<?php

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    private $originalPhpunitTestEnv;
    private $originalCwd;
    private $tempDir;

    /**
     * Set up the test environment.
     * Creates a temporary directory and changes the current working directory to it.
     */
    protected function setUp(): void
    {
        $this->originalPhpunitTestEnv = getenv('PHPUNIT_TEST');
        $this->originalCwd            = getcwd();
        $this->tempDir                = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'config_test_' . uniqid();
        mkdir($this->tempDir);
        chdir($this->tempDir);
    }

    /**
     * Clean up the test environment.
     * Restores the original working directory and removes the temporary directory.
     */
    protected function tearDown(): void
    {
        if ($this->originalPhpunitTestEnv !== false) {
            putenv("PHPUNIT_TEST={$this->originalPhpunitTestEnv}");
        } else {
            putenv('PHPUNIT_TEST');
        }
        chdir($this->originalCwd);
        $this->recursiveRemoveDirectory($this->tempDir);
    }

    /**
     * Utility function to recursively remove a directory.
     *
     * @param string $dir The directory to remove.
     */
    private function recursiveRemoveDirectory($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                        $this->recursiveRemoveDirectory($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Test resolve_config_file_path() with the default config file.
     * Checks if the function can find the default config file in the expected location.
     */
    public function test_resolve_config_file_path_default_config_file()
    {
        mkdir($this->tempDir . DIRECTORY_SEPARATOR . 'config');
        $configFile = $this->tempDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'helper.config.php';
        file_put_contents($configFile, '<?php return [];');

        $result = resolve_config_file_path();
        $this->assertEquals($configFile, $result);
    }

    /**
     * Test resolve_config_file_path() with a config file in the current directory.
     * Checks if the function can find a custom config file in the current directory.
     */
    public function test_resolve_config_file_path_in_current_directory()
    {
        $configFile = $this->tempDir . DIRECTORY_SEPARATOR . 'custom_helper.config.php';
        file_put_contents($configFile, '<?php return [];');

        $result = resolve_config_file_path('custom_helper.config.php');
        $this->assertEquals($configFile, $result);
    }

    /**
     * Test resolve_config_file_path() with a config file in the parent directory.
     * Checks if the function can find a config file in the parent directory when called from a subdirectory.
     */
    public function test_resolve_config_file_path_in_parent_directory()
    {
        $configFile = $this->tempDir . DIRECTORY_SEPARATOR . 'parent_helper.config.php';
        file_put_contents($configFile, '<?php return [];');

        mkdir($this->tempDir . DIRECTORY_SEPARATOR . 'subdir');
        chdir($this->tempDir . DIRECTORY_SEPARATOR . 'subdir');

        $result = resolve_config_file_path('parent_helper.config.php');
        $this->assertEquals($configFile, $result);
    }

    /**
     * Test resolve_config_file_path() with a non-existent config file.
     * Checks if the function returns null when the specified config file doesn't exist.
     */
    public function test_resolve_config_file_path_non_existent_file()
    {
        $result = resolve_config_file_path('non_existent_helper.config.php');
        $this->assertNull($result);
    }

    /**
     * Test resolve_config_file_path() with a null config file parameter.
     * Checks if the function returns null when passed a null parameter.
     */
    public function test_resolve_config_file_path_null_parameter()
    {
        $result = resolve_config_file_path(null);
        $this->assertNull($result);
    }

    /**
     * Test resolve_config_file_path() in a simulated vendor installation.
     * Checks if the function can find the config file in the correct location for a Composer-installed package.
     */
    public function test_resolve_config_file_path_vendor_installation()
    {
        // Simulate a vendor installation
        $vendorDir = $this->tempDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR . 'name' . DIRECTORY_SEPARATOR . 'src';
        mkdir($vendorDir, 0777, true);

        $configFile = dirname($vendorDir, 4) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'helper.config.php';
        mkdir(dirname($configFile), 0777, true);
        file_put_contents($configFile, '<?php return [];');

        $result = resolve_config_file_path();
        $this->assertEquals($configFile, $result);
    }

    /**
     * Test load_config() with a valid configuration file.
     * Checks if the function correctly loads and returns the configuration array.
     */
    public function test_load_config_valid_file()
    {
        $config_file = $this->tempDir . DIRECTORY_SEPARATOR . 'valid_helper.config.php';
        file_put_contents($config_file, '<?php return ["key" => "value"];');

        $result = load_config($config_file);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('key', $result);
        $this->assertEquals('value', $result['key']);
    }

    /**
     * Test load_config() with the default configuration file.
     * Checks if the function correctly loads the default config file when no path is provided.
     */
    public function test_load_config_default_file()
    {
        $config_dir = $this->tempDir . DIRECTORY_SEPARATOR . 'config';
        mkdir($config_dir);
        $config_file = $config_dir . DIRECTORY_SEPARATOR . 'helper.config.php';
        file_put_contents($config_file, '<?php return ["default" => "value"];');

        $original_dir = getcwd();
        chdir($this->tempDir);

        $result = load_config();

        chdir($original_dir);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('default', $result);
        $this->assertEquals('value', $result['default']);
    }

    /**
     * Test load_config() with a non-existent file.
     * Checks if the function throws a RuntimeException when the config file doesn't exist.
     */
    public function test_load_config_non_existent_file()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Configuration file not found:');

        load_config($this->tempDir . DIRECTORY_SEPARATOR . 'non_existent.php');
    }

    /**
     * Test load_config() with an invalid configuration format.
     * Checks if the function throws a RuntimeException when the config file doesn't return an array.
     */
    public function test_load_config_invalid_format()
    {
        $config_file = $this->tempDir . DIRECTORY_SEPARATOR . 'invalid_helper.config.php';
        file_put_contents($config_file, '<?php return "not an array";');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid configuration format in file:');

        load_config($config_file);
    }

    public function test_returns_true_when_phpunit_composer_install_is_defined()
    {
        if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
            define('PHPUNIT_COMPOSER_INSTALL', true);
        }
        $this->assertTrue(is_phpunit_environment());
    }

    public function test_returns_true_when_phpunit_bootstrap_is_defined()
    {
        if (!defined('__PHPUNIT_BOOTSTRAP')) {
            define('__PHPUNIT_BOOTSTRAP', true);
        }
        $this->assertTrue(is_phpunit_environment());
    }

    public function test_returns_true_when_phpunit_test_env_is_set()
    {
        putenv('PHPUNIT_TEST=1');
        $this->assertTrue(is_phpunit_environment());
    }

    public function test_returns_false_when_no_phpunit_indicators_are_present()
    {
        // Unset the environment variable
        putenv('PHPUNIT_TEST');
        // Ignore constants
        $this->assertFalse(is_phpunit_environment(false, true));
    }

    public function test_returns_true_when_class_check_is_included()
    {
        $this->assertTrue(is_phpunit_environment(true));
    }

    public function test_returns_false_when_class_check_is_not_included()
    {
        // Unset the environment variable
        putenv('PHPUNIT_TEST');
        // Ignore constants
        $this->assertFalse(is_phpunit_environment(false, true));
    }
}
