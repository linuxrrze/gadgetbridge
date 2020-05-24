<?php

namespace OCA\GadgetBridge\Tests;

use OCA\GadgetBridge\Database;
use OCA\GadgetBridge\InvalidDatabaseException;

/**
 * DatabaseTest
 * @group Activities
 * 
 */
class DatabaseTest extends \Test\TestCase
{
    private $database;
    protected function setUp(): void
    {
        parent::setUp();
        $this->database = new Database('tests/_data/Gadgetbridge');
    }
    

    /** @test 
     * 
     */    
    public function an_invalid_file_throws_an_exception()
    {
        $this->expectException(InvalidDatabaseException::class);
        $database = new Database('tests/_data/invalidfile.txt');        
    }
    
    /** @test */
    public function a_valid_database_can_parse_for_devices()
    {

        $devices = $this->database->getDevices();
        $this->assertCount(1, $devices);
        $this->assertEquals('Mi Band 3', $devices[0]->data->name);
    }

    /** @test */
    public function a_valid_device_returns_samples()
    {
        $device = $this->database->getDevices()[0];

        $samples = $device->getSamples();
        $this->assertArrayHasKey("STEPS", $samples);
        $this->assertArrayHasKey("TIMESTAMPS", $samples);
        $this->assertArrayHasKey("KINDS", $samples);
        $this->assertArrayHasKey("ACTIVITY_COLORS", $samples);
        $this->assertArrayHasKey("HEART_RATES", $samples);
    }
    

}
