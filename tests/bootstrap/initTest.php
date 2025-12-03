<?php

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;

class InitTest extends TestCase
{
    private string $tempConfigFile;
    private string $tempConfigRelativePath;
    private $originalCwd;

    protected function setUp(): void
    {
        $this->originalCwd = getcwd();

        // Reset the Singleton before each test
        Tracker::reset();

        // Create a temporary config file in the project root
        $this->tempConfigFile         = $this->originalCwd . DIRECTORY_SEPARATOR . 'temp_config_' . uniqid() . '.php';
        $this->tempConfigRelativePath = basename($this->tempConfigFile);
    }

    protected function tearDown(): void
    {
        // Reset the Singleton after each test
        Tracker::reset();

        // Delete the temporary config file
        if (file_exists($this->tempConfigFile)) {
            unlink($this->tempConfigFile);
        }

        // Restore the original working directory
        chdir($this->originalCwd);
    }

    private function createMockConfig(bool $autoStartTracker): void
    {
        $configContent = "<?php\nreturn ['tracker' => ['autoStartTracker' => " . ($autoStartTracker ? 'true' : 'false') . ']];';
        file_put_contents($this->tempConfigFile, $configContent);
    }

    public function test_initialize_with_missing_config_file()
    {
        $this->assertFalse(Tracker::isInitialized());

        // Pass a non-existent config file path
        initialize_tracker('nonexistent_' . uniqid() . '.php');

        // Should not initialize when config file doesn't exist
        $this->assertFalse(Tracker::isInitialized());
    }

    public function test_initialize_with_autoStartTracker_true()
    {
        $this->createMockConfig(true);

        $this->assertFalse(Tracker::isInitialized());

        initialize_tracker($this->tempConfigRelativePath);

        $this->assertTrue(Tracker::isInitialized());
    }

    public function test_initialize_with_autoStartTracker_false()
    {
        $this->createMockConfig(false);

        $this->assertFalse(Tracker::isInitialized());

        initialize_tracker($this->tempConfigRelativePath);

        $this->assertFalse(Tracker::isInitialized());
    }
}
