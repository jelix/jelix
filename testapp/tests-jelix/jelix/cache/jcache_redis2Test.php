<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Tahina Ramaroson
* @contributor Sylvain de Vathaire
* @contributor Laurent Jouanneau
* @copyright   NEOV 2009, 2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jcache.lib.php');
require_once(__DIR__. '/../../../vendor/jelix/php-redis/lib/Redis.php');

/**
* Tests API jCache
* @package     testapp
* @subpackage  jelix_tests module
*/

class jCache_Redis2Test extends jCacheAPITest {

    protected $redis;

    function setUp () {
        $this->profile = 'usingredis2';
        parent::setUp();
        $this->redis = new \PhpRedis\Redis(TESTAPP_REDIS_HOST,6379);
        $this->redis->select_db(1);
        $this->redis->flushall();
    }

    public function tearDown() {
        if ($this->redis) {
            //$this->redis->quit();
            $this->redis->disconnect();
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
