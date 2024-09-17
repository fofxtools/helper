<?php

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @group scraping
 */
class ScrapingTest extends TestCase
{
    /**
     * Check if the web server is running before running the whole test suite.
     */
    public static function setUpBeforeClass(): void
    {
        self::check_web_server_status();
    }

    /**
     * Check if the web server is running by attempting to access localhost.
     */
    private static function check_web_server_status()
    {
        $url = 'http://localhost';

        // Use cURL to check if the server is running.
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Timeout after 2 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_exec($ch);

        // If the connection fails, skip all tests.
        if (curl_errno($ch)) {
            self::markTestSkipped('The web server is not running. Skipping tests.');
        }

        curl_close($ch);
    }

    /**
     * Tests that get_random_proxy() returns either string or null
     */
    public function test_get_random_proxy_returns_string_or_null()
    {
        // Call get_random_proxy
        $proxy = get_random_proxy();

        $this->assertTrue(
            is_null($proxy) || is_string($proxy),
            "Expected value to be either null or string, but got " . gettype($proxy)
        );
    }

    /**
     * Test get_default_http_headers
     */
    public function test_get_default_http_headers()
    {
        $headers = get_default_http_headers();

        $this->assertIsArray($headers);
        $this->assertCount(5, $headers);
        $this->assertContains('Accept-Language: en-US,en;q=0.9', $headers);
        $this->assertContains('Connection: keep-alive', $headers);
        $this->assertContains('Cache-Control: no-cache', $headers);
        $this->assertContains('Pragma: no-cache', $headers);
        $this->assertStringStartsWith('User-Agent: ', $headers[4]);
    }

    /**
     * Test get_random_user_agent
     */
    public function test_get_random_user_agent()
    {
        $user_agent = get_random_user_agent();

        $expected_user_agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
        ];

        $this->assertContains($user_agent, $expected_user_agents);
    }

    /**
     * Test that get_random_user_agent returns different values
     */
    public function test_get_random_user_agent_randomness()
    {
        $user_agents = [];
        for ($i = 0; $i < 100; $i++) {
            $user_agents[] = get_random_user_agent();
        }

        $unique_user_agents = array_unique($user_agents);
        $this->assertGreaterThan(1, count($unique_user_agents));
    }

    /**
     * Test successful retrieval of content from a valid URL.
     */
    public function test_url_get_contents_success()
    {
        $url = 'http://localhost/helper/public/request-vars.php?string=hello&num=1';
        $content = url_get_contents($url);

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('hello', $content);
    }

    /**
     * Test handling of an invalid URL.
     */
    public function test_url_get_contents_invalid_url()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL provided.');

        url_get_contents('not a valid url');
    }

    /**
     * Test handling of a non-existent domain.
     */
    public function test_url_get_contents_non_existent_domain()
    {
        $this->expectException(\RuntimeException::class);

        url_get_contents('http://this-domain-does-not-exist.com');
    }

    /**
     * Test handling of a valid URL with non-existent path.
     */
    public function test_url_get_contents_non_existent_path()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to read the contents of the URL: http://localhost/non-existent-path-123');

        url_get_contents('http://localhost/non-existent-path-123');
    }

    /**
     * Test handling of a URL with special characters.
     */
    public function test_url_get_contents_url_with_special_chars()
    {
        $url = 'http://localhost/helper/public/request-vars.php?param=' . urlencode('value with spaces');
        $content = url_get_contents($url);

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('value with spaces', $content);
    }

    /**
     * Test handling of a URL with query parameters.
     */
    public function test_url_get_contents_url_with_query_params()
    {
        $url = 'http://localhost/helper/public/request-vars.php?foo=bar&baz=qux';
        $content = url_get_contents($url);

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('foo', $content);
        $this->assertStringContainsString('bar', $content);
    }

    /**
     * Test handling of a URL that redirects.
     */
    public function test_url_get_contents_redirect()
    {
        $url = 'http://localhost/helper/public/request-vars.php?redirect=true';
        $content = url_get_contents($url);

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('redirect', $content);
    }

    /**
     * Tests fetching content from a valid URL without proxy or additional options.
     */
    public function test_curl_get_contents_with_valid_url()
    {
        $url = 'http://localhost/helper/public/request-vars.php?string=hello&num=1';
        $result = curl_get_contents($url);

        $this->assertIsString($result);
        $this->assertStringContainsString('hello', $result);
    }

    /**
     * Tests fetching content using a proxy.
     */
    public function test_curl_get_contents_with_proxy()
    {
        $url = 'http://localhost/helper/public/request-vars.php?string=hello&num=1';
        $config = ['use_proxy' => true];

        $result = curl_get_contents($url, $config['use_proxy']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('contents', $result);
        $this->assertStringContainsString('hello', $result['contents']);
    }

    /**
     * Tests fetching content with the return_headers option set to TRUE.
     */
    public function test_curl_get_contents_with_headers()
    {
        $url = 'http://localhost/helper/public/request-vars.php?string=hello&num=1';
        $config = ['return_headers' => true];

        $result = curl_get_contents($url, false, [], true, false, $config['return_headers']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('headers', $result);
        $this->assertArrayHasKey('contents', $result);
    }

    /**
     * Tests fetching content with the return_info option set to TRUE.
     */
    public function test_curl_get_contents_with_info()
    {
        $url = 'http://localhost/helper/public/request-vars.php?string=hello&num=1';
        $config = ['return_info' => true];

        $result = curl_get_contents($url, false, [], true, $config['return_info']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('info', $result);
        $this->assertArrayHasKey('contents', $result);
    }

    /**
     * Tests fetching content from an invalid URL.
     */
    public function test_curl_get_contents_with_invalid_url()
    {
        $url = 'invalid-url';

        $this->expectException(\InvalidArgumentException::class);

        curl_get_contents($url);
    }

    /**
     * Tests fetching content from an empty URL.
     */
    public function test_curl_get_contents_with_empty_url()
    {
        $url = '';

        $this->expectException(\InvalidArgumentException::class);

        curl_get_contents($url);
    }


    /**
     * Tests error handling when cURL execution fails.
     */
    public function test_curl_get_contents_with_curl_failure()
    {
        $url = 'https://invalid-domain.com';

        $this->expectException(\RuntimeException::class);

        curl_get_contents($url);
    }

    /**
     * Tests curl_multi_get_contents with valid GET URLs.
     */
    public function test_curl_multi_get_contents_with_valid_get_urls()
    {
        // Set up an array of valid GET request URLs.
        $urls = [
            'http://localhost/helper/public/request-vars.php?string=hello&num=1',
            'http://localhost/helper/public/request-vars.php?string=testing&num=2',
            'http://localhost/helper/public/request-vars.php?string=test123&num=3',
        ];

        // Call the curl_multi_get_contents function with the valid GET URLs.
        $results = curl_multi_get_contents($urls);

        // Assert that the results are an array.
        $this->assertIsArray($results);

        // Assert that each result contains 'contents' and 'url'.
        foreach ($results as $result) {
            $this->assertArrayHasKey('contents', $result);
            $this->assertArrayHasKey('url', $result);

            // Assert that the 'contents' key is a string.
            $this->assertIsString($result['contents']);
        }
    }

    /**
     * Tests curl_multi_get_contents with POST data.
     */
    public function test_curl_multi_get_contents_with_post_data()
    {
        // Set up an array of POST requests with URLs and POST data.
        $data = [
            [
                'url'  => 'http://localhost/helper/public/request-vars.php?string=hello',
                'post' => [
                    'postvar1' => 'Demo',
                    'postvar2' => 'Roses are red, Violets are blue.',
                ]
            ],
            [
                'url'  => 'http://localhost/helper/public/request-vars.php?string=testing',
                'post' => [
                    'postvar1' => 'Demo',
                    'postvar2' => 'Sugar is sweet, And so are you.',
                ]
            ]
        ];

        // Call the curl_multi_get_contents function with POST data.
        $results = curl_multi_get_contents($data);

        // Assert that the results are an array.
        $this->assertIsArray($results);

        // Assert that each result contains 'contents' and 'url'.
        foreach ($results as $result) {
            $this->assertArrayHasKey('contents', $result);
            $this->assertArrayHasKey('url', $result);

            // Assert that the 'contents' key is a string.
            $this->assertIsString($result['contents']);
        }
    }

    /**
     * Tests curl_multi_get_contents with an invalid URL.
     */
    public function test_curl_multi_get_contents_with_invalid_url()
    {
        // Set up an array with an invalid URL.
        $urls = [
            'invalid-url',
            'http://localhost/helper/public/request-vars.php?string=testing&num=2'
        ];

        // Expect an InvalidArgumentException to be thrown due to the invalid URL.
        $this->expectException(\InvalidArgumentException::class);

        // Call the curl_multi_get_contents function with the invalid URL.
        curl_multi_get_contents($urls);
    }

    /**
     * Tests fetching content using a proxy.
     */
    public function test_curl_multi_get_contents_with_proxy()
    {
        $urls = [
            'http://localhost/helper/public/request-vars.php?string=hello&num=1',
            'http://localhost/helper/public/request-vars.php?string=testing&num=2',
        ];

        $result = curl_multi_get_contents($urls, true);

        // Assert that the result contains the proxy field, even if it's null
        foreach ($result as $res) {
            $this->assertArrayHasKey('proxy', $res);
            // If proxy is used, ensure it's not null
            if ($res['proxy'] !== null) {
                $this->assertNotEmpty($res['proxy']);
            }
        }
    }

    /**
     * Tests curl_multi_get_contents with additional cURL options.
     */
    public function test_curl_multi_get_contents_with_additional_options()
    {
        // Set up an array of valid GET request URLs.
        $urls = [
            'http://localhost/helper/public/request-vars.php?string=hello&num=1',
            'http://localhost/helper/public/request-vars.php?string=testing&num=2',
        ];

        // Set up additional cURL options.
        $options = [
            [
                CURLOPT_TIMEOUT => 10,
            ],
            [
                CURLOPT_TIMEOUT => 5,
            ]
        ];

        // Call the curl_multi_get_contents function with additional cURL options.
        $results = curl_multi_get_contents($urls, false, true, $options);

        // Assert that the results are an array.
        $this->assertIsArray($results);

        // Assert that each result contains 'contents' and 'url'.
        foreach ($results as $result) {
            $this->assertArrayHasKey('contents', $result);
            $this->assertArrayHasKey('url', $result);

            // Assert that the 'contents' key is a string.
            $this->assertIsString($result['contents']);
        }
    }
}
