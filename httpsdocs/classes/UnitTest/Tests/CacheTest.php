<?php
namespace UnitTest;

use CB\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->cacheVarName = 'testingCacheVar';
        $this->prevValue = Cache::get($this->cacheVarName);
        Cache::remove($this->cacheVarName);
    }

    public function testCache()
    {
        $this->assertTrue(
            !Cache::exist($this->cacheVarName),
            "Not set variable exists"
        );

        Cache::set($this->cacheVarName, 'a.b.c');

        $this->assertTrue(
            Cache::exist($this->cacheVarName),
            "Set variable doesnt exist"
        );

        $this->assertTrue(
            (Cache::get($this->cacheVarName, 'qwe') == 'a.b.c'),
            "Cache value doesnt match"
        );

        Cache::remove($this->cacheVarName);

        $this->assertTrue(
            !Cache::exist($this->cacheVarName),
            "Removed value exists"
        );

    }

    public function tearDown()
    {
        if (!is_null($this->prevValue)) {
            Cache::set($this->cacheVarName, $this->prevValue);
        } else {
            Cache::remove($this->cacheVarName);
        }
    }
}
