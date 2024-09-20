<?php

namespace AFDRedis\Tests;

use PHPUnit\Framework\TestCase;
use AFDRedis\AFDRedis;

class AFDRedisTest extends TestCase
{
    
    protected $AFDRedis;
    
    public function setUp(): void
    {
        $this->AFDRedis = new AFDRedis($this->db, $this->config);
    }
    
    public function tearDown(): void
    {
        $this->AFDRedis = null;
    }
    
    /**
     * @covers \AFDRedis\AFDRedis::setExpiryTime
     * @covers \AFDRedis\AFDRedis::getExpiryTime
     */
    public function testSetExpiryTime()
    {
        $expiryTime = $this->AFDRedis->getExpiryTime();
        $this->assertIsInt($expiryTime);
        $this->assertObjectHasAttribute('expiryTime', $this->AFDRedis->setExpiryTime(100));
        $this->assertEquals(100, $this->AFDRedis->getExpiryTime());
    }
    
    /**
     * @covers \AFDRedis\AFDRedis::addServer
     */
    public function testAddRedisServer()
    {
        $this->assertObjectEquals($this->AFDRedis, $this->AFDRedis->addServer('localhost'));
    }
    
    /**
     * @covers \AFDRedis\AFDRedis::addServer
     * @covers \AFDRedis\AFDRedis::save
     * @covers \AFDRedis\AFDRedis::replace
     * @covers \AFDRedis\AFDRedis::fetch
     * @covers \AFDRedis\AFDRedis::delete
     * @covers \AFDRedis\AFDRedis::deleteAll
     * @covers \AFDRedis\AFDRedis::getData
     */
    public function testRedisCaching()
    {
        $this->markTestIncomplete();
    }
}
