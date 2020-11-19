<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Tahina Ramaroson
* @contributor Sylvain de Vathaire
* @contributor Laurent Jouanneau
* @copyright   NEOV 2009, 2012-2017 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jcache.lib.php');

/**
* Tests API jCache
* @package     testapp
* @subpackage  jelix_tests module
*/

class jCache_Redis_ext2Test extends jCacheAPITest {

    protected $redis;

    function setUp ()
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('jCache_Redis_ext2Test cannot be run because redis extension is not installed');
            return;
        }
        $this->profile = 'usingredis_ext2';
        parent::setUp();

        $this->redis = new jRedis();
        $this->redis->connect(TESTAPP_REDIS_HOST,6379);
        $this->redis->select(1);
        $this->redis->flushAll();
    }

    public function tearDown() {
        if ($this->redis) {
            $this->redis->close();
        }
    }

/*
    public function testGarbage (){
        parent::testGarbage();
        $mmc = memcache_connect($this->mmhost, $this->mmport);
        $this->assertTrue(memcache_get($mmc,'remainingDataKey')=='remaining data');
        $this->assertFalse(memcache_get($mmc,'garbage1DataKey'));
        $this->assertFalse(memcache_get($mmc,'garbage2DataKey'));
    }

    public function testFlush (){
        parent::testFlush();

        $mmc=memcache_connect($this->mmhost, $this->mmport);
        $this->assertEquals('some data', memcache_get($mmc,'flush1DataKey'));
        $this->assertEquals('data to remove', memcache_get($mmc,'flush2DataKey'));
        $this->assertEquals('other data to remove', memcache_get($mmc,'flush3DataKey'));
        $this->assertTrue(jCache::flush($this->profile));
        $this->assertFalse(memcache_get($mmc,'flush1DataKey'));
        $this->assertFalse(memcache_get($mmc,'flush2DataKey'));
        $this->assertFalse(memcache_get($mmc,'flush3DataKey'));
    }
*/
}
