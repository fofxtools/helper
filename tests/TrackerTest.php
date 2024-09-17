<?php

namespace FOfX\Helper;

use PHPUnit\Framework\TestCase;

class TrackerTest extends TestCase
{
    /**
     * @var Tracker
     */
    private $tracker;
    private $originalServerRemoteAddr;

    /**
     * Set up the test environment before each test
     * 
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Store original values
        $this->originalServerRemoteAddr = get_remote_addr();

        // Reset the Tracker instance before each test
        $reflection = new \ReflectionClass(Tracker::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        $this->tracker = Tracker::getInstance();
    }

    /**
     * Clean up the test environment after each test
     * 
     * @return void
     */
    protected function tearDown(): void
    {
        // Restore original REMOTE_ADDR
        if ($this->originalServerRemoteAddr !== null) {
            $_SERVER['REMOTE_ADDR'] = $this->originalServerRemoteAddr;
        } else {
            unset($_SERVER['REMOTE_ADDR']);
        }
    }

    /**
     * Test getInstance method
     */
    public function testGetInstance(): void
    {
        $instance1 = Tracker::getInstance();
        $instance2 = Tracker::getInstance();

        $this->assertSame($instance1, $instance2);
        $this->assertInstanceOf(Tracker::class, $instance1);
    }

    /**
     * Test scriptTimer method
     */
    public function testScriptTimer(): void
    {
        Tracker::scriptTimer('TestSection', 'start');

        // Sleep for a short time to ensure elapsed time
        usleep(10000);

        Tracker::scriptTimer('TestSection', 'end');

        $reflection = new \ReflectionClass(Tracker::class);
        $timerArray = $reflection->getProperty('timerArray');
        $timerArray->setAccessible(true);
        $timer = $timerArray->getValue($this->tracker);

        $this->assertArrayHasKey('TestSection', $timer);
        $this->assertArrayHasKey('Start', $timer['TestSection']);
        $this->assertArrayHasKey('End', $timer['TestSection']);
        $this->assertArrayHasKey('Elapsed', $timer['TestSection']);
        $this->assertGreaterThan(0, $timer['TestSection']['Elapsed']);
    }

    /**
     * Test scriptTimerElapsed method
     */
    public function testScriptTimerElapsed(): void
    {
        Tracker::scriptTimer('TestSection', 'start');

        // Sleep for a short time to ensure elapsed time
        usleep(10000);

        $elapsed = Tracker::scriptTimerElapsed('TestSection');

        $this->assertGreaterThan(0, $elapsed);
    }

    /**
     * Test createDataArray and printData methods
     */
    public function testCreateDataArrayAndPrintData(): void
    {
        Tracker::scriptTimer('TestSection', 'start');
        Tracker::scriptTimer('TestSection', 'end');

        // Capture the output of printData
        ob_start();
        Tracker::printData();
        $output = ob_get_clean();

        $this->assertStringContainsString('TestSection', $output);
        $this->assertStringContainsString('timer', $output);
        $this->assertStringContainsString('Start', $output);
        $this->assertStringContainsString('End', $output);
        $this->assertStringContainsString('Elapsed', $output);
    }

    /**
     * Test trackerEnd method
     */
    public function testTrackerEnd(): void
    {
        // Capture the output of trackerEnd
        ob_start();
        Tracker::trackerEnd();
        $output = ob_get_clean();

        $this->assertStringContainsString('Main', $output);
        $this->assertStringContainsString('Start', $output);
        $this->assertStringContainsString('End', $output);
        $this->assertStringContainsString('Elapsed', $output);
    }

    /**
     * Test bandwidthEnd method
     */
    public function testBandwidthEnd(): void
    {
        // Capture the output of bandwidthEnd
        ob_start();
        Tracker::bandwidthEnd();
        $output = ob_get_clean();

        $this->assertStringContainsString('Main', $output);
        $this->assertStringContainsString('Start', $output);
        $this->assertStringContainsString('End', $output);
        $this->assertStringContainsString('Net', $output);
    }

    /**
     * Test throwTracker method
     */
    public function testThrowTracker(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test exception');

        Tracker::throwTracker('Test exception');
    }

    /**
     * Test inCheckIPs with a single IP address
     * 
     * @return void
     */
    public function testSingleIPInCheckIPs(): void
    {
        // Set up test checkIPs
        $this->setTrackerCheckIPs(['192.168.1.1', '10.0.0.1']);

        // Test IP in the list
        $this->assertTrue(Tracker::inCheckIPs('192.168.1.1'));
        // Test IP not in the list
        $this->assertFalse(Tracker::inCheckIPs('172.16.0.1'));
    }

    /**
     * Test inCheckIPs with multiple IP addresses
     * 
     * @return void
     */
    public function testMultipleIPsInCheckIPs(): void
    {
        // Set up test checkIPs
        $this->setTrackerCheckIPs(['192.168.1.1', '10.0.0.1']);

        // Test when one IP is in the list
        $this->assertTrue(Tracker::inCheckIPs(['192.168.1.1', '172.16.0.1']));
        // Test when no IPs are in the list
        $this->assertFalse(Tracker::inCheckIPs(['172.16.0.1', '172.16.0.2']));
    }

    /**
     * Test inCheckIPs with null input (should use REMOTE_ADDR)
     * 
     * @return void
     */
    public function testNullIPUsesRemoteAddr(): void
    {
        // Set up test REMOTE_ADDR and checkIPs
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $this->setTrackerCheckIPs(['192.168.1.1', '10.0.0.1']);

        // Test with null input
        $this->assertTrue(Tracker::inCheckIPs());
    }

    /**
     * Test inCheckIPs with localhost inclusion
     * 
     * @return void
     */
    public function testIncludeLocalhost(): void
    {
        // Set up test checkIPs
        $this->setTrackerCheckIPs(['192.168.1.1', '10.0.0.1']);

        // Test with localhost included
        $this->assertTrue(Tracker::inCheckIPs('127.0.0.1', true));
        // Test with localhost excluded
        $this->assertFalse(Tracker::inCheckIPs('127.0.0.1', false));
    }

    /**
     * Test inCheckIPs with localhost exclusion
     * 
     * @return void
     */
    public function testExcludeLocalhost(): void
    {
        // Set up test checkIPs
        $this->setTrackerCheckIPs(['192.168.1.1', '10.0.0.1']);

        // Test with localhost excluded
        $this->assertFalse(Tracker::inCheckIPs(['172.16.0.1', '127.0.0.1'], false));
        // Test that a valid IP still returns true when localhost is excluded
        $this->assertTrue(Tracker::inCheckIPs(['172.16.0.1', '192.168.1.1'], false));
    }

    /**
     * Helper method to set Tracker's checkIPs
     * 
     * @param  array  $ips
     * @return void
     */
    private function setTrackerCheckIPs(array $ips): void
    {
        $reflection = new \ReflectionClass(Tracker::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $tracker = $instance->getValue();

        $checkIPs = $reflection->getProperty('checkIPs');
        $checkIPs->setAccessible(true);
        $checkIPs->setValue($tracker, $ips);
    }
}
