<?php

/**
 * Web Scraping and HTTP Request Utility Functions
 *
 * This file provides functions for web scraping and making HTTP requests.
 * It includes utilities for handling proxies, user agents, and various
 * HTTP request scenarios.
 *
 * Key features:
 * - Proxy management for HTTP requests
 * - User-agent handling
 * - Concurrent HTTP requests with cURL
 */

namespace FOfX\Helper;

/**
 * Selects a random proxy from the list of available proxies.
 *
 * @return  ?string              Random proxy IP or null if no proxies are available.
 *
 * @throws  \RuntimeException    If no proxies are available and this is critical for your application.
 * @see     Tracker::getProxyIps()
 */
function get_random_proxy(): ?string
{
    $proxy_array = Tracker::getProxyIps();

    if (empty($proxy_array)) {
        return null;
    }

    return $proxy_array[array_rand($proxy_array)];
}

/**
 * Returns default HTTP headers for the request.
 *
 * @return  array    Default HTTP headers.
 */
function get_default_http_headers(): array
{
    return [
        'Accept-Language: en-US,en;q=0.9',
        'Connection: keep-alive',
        'Cache-Control: no-cache',
        'Pragma: no-cache',
        'User-Agent: ' . get_random_user_agent(),
    ];
}

/**
 * Returns a random user agent string.
 *
 * @return  string    Random user agent string.
 */
function get_random_user_agent(): string
{
    $user_agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
    ];

    return $user_agents[array_rand($user_agents)];
}

/**
 * Fetches the contents of the given URL and returns it as a string.
 * 
 * This function is a basic alternative to file_get_contents().
 * 
 * @param   string                     $url  The URL to fetch contents from.
 * @return  string                           The fetched contents of the URL.
 * @throws  \InvalidArgumentException        if the URL is invalid.
 * @throws  \RuntimeException                if the file could not be read.
 */
function url_get_contents(string $url): string
{
    // Validate URL
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        throw new \InvalidArgumentException('Invalid URL provided.');
    }

    // Custom error handler to convert warnings into exceptions
    set_error_handler(function ($errno, $errstr) use ($url) {
        throw new \RuntimeException("Failed to read the contents of the URL: $url. Error: $errstr");
    });

    try {
        // Try to read the contents of the URL
        $lines_array = file($url);

        // Handle the failure case
        if ($lines_array === false) {
            throw new \RuntimeException("Failed to read the contents of the URL: $url");
        }

        // Combine the lines into a single string
        $content = implode('', $lines_array);

        return $content;
    } finally {
        // Restore the previous error handler
        restore_error_handler();
    }
}

/**
 * Fetches contents from a URL using cURL with various options.
 * 
 * $return_headers option based on comments from Geoffrey and sneaky
 * 
 * @link    https://stackoverflow.com/questions/9183178/can-php-curl-retrieve-response-headers-and-body-in-a-single-request/41135574
 * @param   string                     $url               The URL to fetch contents from.
 * @param   bool                       $use_proxy         Whether to use a random proxy from config.ini.php.
 * @param   array                      $options           Additional cURL options to be set.
 * @param   bool                       $use_http_headers  Whether to set additional HTTP request headers.
 *                                                        Accept-Language, Connection, Cache-Control.
 * @param   bool                       $return_info       Whether to include info from curl_getinfo($ch) in the return value.
 *                                                        As array key 'info'.
 * @param   bool                       $return_headers    Whether to include response headers in the return value.
 *                                                        As array key 'headers'.
 * @return  string|array                                  The fetched contents. Or an array with additional information.
 *
 * @throws  \InvalidArgumentException
 * @throws  \RuntimeException
 */
function curl_get_contents(
    string $url,
    bool $use_proxy = false,
    array $options = [],
    bool $use_http_headers = true,
    bool $return_info = false,
    bool $return_headers = false
): string|array {
    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new \InvalidArgumentException('Invalid URL provided.');
    }

    // Initialize cURL
    $ch = curl_init();
    if ($ch === false) {
        throw new \RuntimeException('Failed to initialize cURL');
    }

    // Set default cURL options
    $default_options = [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HEADER         => $return_headers,
    ];

    // Apply options
    curl_setopt_array($ch, $default_options + $options);

    // Set proxy if requested
    if ($use_proxy) {
        $proxy = get_random_proxy();
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
    }

    // Set default HTTP headers if requested
    if ($use_http_headers) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, get_default_http_headers());
    }

    $headers = [];
    $header_size = 0;

    // Capture response headers if requested
    if ($return_headers) {
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$headers, &$header_size) {
            $len = strlen($header);
            $header_size += $len;
            $header = explode(':', $header, 2);
            if (count($header) > 1) {
                $headers[strtolower(trim($header[0]))][] = trim($header[1]);
            }
            return $len;
        });
    }

    // Execute cURL request
    $response = curl_exec($ch);
    if ($response === false) {
        throw new \RuntimeException(curl_error($ch), curl_errno($ch));
    }

    // Get additional information if requested
    $info = curl_getinfo($ch);
    curl_close($ch);

    // Extract content from response
    if ($return_headers) {
        $contents = substr($response, $header_size);
    } else {
        $contents = $response;
    }

    // Prepare return value
    if ($use_proxy || $return_info || $return_headers) {
        $result = ['contents' => $contents];
        if ($use_proxy && $proxy) {
            $result['proxy'] = $proxy;
        }
        if ($return_info) {
            $result['info'] = $info;
        }
        if ($return_headers) {
            $result['headers'] = $headers;
        }
        return $result;
    }

    return $contents;
}

/**
 * Executes multiple cURL requests concurrently.
 *
 * @link     https://www.phpied.com/simultaneuos-http-requests-in-php-with-curl/
 * @param    array                      $urls         Array of URLs or data to fetch via cURL.
 * @param    bool                       $use_proxy    Whether to use a random proxy.
 * @param    bool                       $use_headers  Whether to include headers in the cURL requests.
 * @param    array                      $options      Additional cURL options.
 *
 * @return   array                                    An array of responses, each containing the URL, content, and optional info.
 *
 * @example  GET request:
 *           $urls = [
 *               'http://localhost/helper/public/request-vars.php?string=hello&num=1',
 *               'http://localhost/helper/public/request-vars.php?string=testing&num=2',
 *               'http://localhost/helper/public/request-vars.php?string=test123&num=3'
 *           ];
 *           $results = curl_multi_get_contents($urls);
 * 
 * @example  POST request:
 *           $data = [
 *               [
 *                   'url'  => 'http://localhost/helper/public/request-vars.php?string=hello',
 *                   'post' => ['postvar1' => 'Demo', 'postvar2' => 'Roses are red, Violets are blue.']
 *               ],
 *               [
 *                   'url'  => 'http://localhost/helper/public/request-vars.php?string=testing',
 *                   'post' => ['postvar1' => 'Demo', 'postvar2' => 'Sugar is sweet, And so are you.']
 *               ]
 *           ];
 *           $results = curl_multi_get_contents($data);
 *
 * @throws   \InvalidArgumentException                If any URL is invalid.
 * @throws   \RuntimeException                        If the cURL request fails.
 * @see      Tracker::getProxyIps() For proxy settings.
 */
function curl_multi_get_contents(array $urls, bool $use_proxy = false, bool $use_headers = true, array $options = []): array
{
    $used_proxies = [];
    $curl_handles = [];
    $result = [];

    $mh = curl_multi_init();

    // Validate and set up each cURL handle
    foreach ($urls as $id => $url_data) {
        $curl_handles[$id] = curl_init();

        // If the URL data is just a string, treat it as a URL (GET request)
        if (is_string($url_data)) {
            $url = $url_data;
            $post_data = [];  // No POST data for a GET request
        } elseif (is_array($url_data) && isset($url_data['url'])) {
            // Otherwise, it's a POST request (or other type) with a URL and possibly POST data
            $url = $url_data['url'];
            $post_data = $url_data['post'] ?? [];
        } else {
            throw new \InvalidArgumentException("Invalid URL data provided.");
        }

        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid URL provided: $url");
        }

        // Configure the cURL handle
        configure_curl_handle($curl_handles[$id], $url, $use_headers, $use_proxy, $used_proxies, $options, $id, $post_data);

        curl_multi_add_handle($mh, $curl_handles[$id]);
    }

    // Execute all cURL requests concurrently
    execute_multi_curl($mh, $result, $curl_handles, $urls, $use_proxy, $used_proxies);

    curl_multi_close($mh);

    return $result;
}

/**
 * Configures a cURL handle with the necessary options.
 *
 * @param  \CurlHandle  $ch             The cURL handle.
 * @param  string       $url            The URL to fetch.
 * @param  bool         $use_headers    Whether to include headers in the request.
 * @param  bool         $use_proxy      Whether to use a proxy.
 * @param  array        &$used_proxies  Reference to store used proxies.
 * @param  array        $options        Additional cURL options.
 * @param  int          $id             Index of the current cURL request.
 * @param  array        $post_data      Additional POST data (if applicable).
 */
function configure_curl_handle(\CurlHandle $ch, string $url, bool $use_headers, bool $use_proxy, array &$used_proxies, array $options, int $id, array $post_data = [])
{
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, get_random_user_agent());

    // SSL options: Disabled by default
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // Apply proxy if needed
    if ($use_proxy) {
        $proxy = get_random_proxy();
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
            $used_proxies[$id] = $proxy;
        } else {
            // Ensure null is stored if no proxy is used
            $used_proxies[$id] = null;
        }
    }

    // Apply HTTP headers if requested
    if ($use_headers) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, get_default_http_headers());
    }

    // Handle POST requests
    if (!empty($post_data)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }

    // Apply additional options
    if (!empty($options)) {
        curl_setopt_array($ch, $options[$id] ?? []);
    }
}

/**
 * Executes the multi-cURL requests and processes the responses.
 *
 * @param  \CurlMultiHandle  $mh            The multi-cURL handle.
 * @param  array     &$result       Reference to store the results.
 * @param  array     $curl_handles  Array of cURL handles.
 * @param  array     $urls          Array of URLs.
 * @param  bool      $use_proxy     Whether to include proxy in the response.
 * @param  array     $used_proxies  List of used proxies.
 */
function execute_multi_curl(\CurlMultiHandle $mh, array &$result, array $curl_handles, array $urls, bool $use_proxy, array $used_proxies)
{
    $running = null;
    $info_array = [];

    do {
        $status = curl_multi_exec($mh, $running);
        if ($running) {
            curl_multi_select($mh);
        }
        $info = curl_multi_info_read($mh);
        if ($info !== false) {
            $info_array[] = curl_getinfo($info['handle']);
        }
    } while ($running > 0);

    // Retrieve the content and remove handles
    foreach ($curl_handles as $id => $ch) {
        $result[$id] = [
            'url'      => $urls[$id],
            'contents' => curl_multi_getcontent($ch),
            'info'     => $info_array[$id] ?? null,
        ];
        if ($use_proxy) {
            $result[$id]['proxy'] = $used_proxies[$id] ?? null;
        }
        curl_multi_remove_handle($mh, $ch);
    }
}
