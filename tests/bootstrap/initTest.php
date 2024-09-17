<?php

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;
use FOfX\Helper\Tracker;

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
        $this->tempConfigFile = $this->originalCwd . DIRECTORY_SEPARATOR . 'temp_config_' . uniqid() . '.php';
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
        $configContent = "<?php\nreturn ['helper' => ['autoStartTracker' => " . ($autoStartTracker ? 'true' : 'false') . "]];";
        file_put_contents($this->tempConfigFile, $configContent);
    }

    public function test_initialize_without_config()
    {
        $this->assertFalse(Tracker::isInitialized());

        initialize_tracker();

        $this->assertTrue(Tracker::isInitialized());
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
