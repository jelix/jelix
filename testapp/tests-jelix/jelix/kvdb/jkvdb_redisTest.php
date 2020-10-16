<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @copyright   2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jkvdb.lib.php');
require_once(__DIR__. '/../../../vendor/jelix/php-redis/lib/Redis.php');

/**
* Tests API jKVDb with the redis_php driver
* @package     testapp
* @subpackage  jelix_tests module
*/

class jkvdb_redisTest extends jKVDbTest {

    protected $redis;

    function setUp() : void {
        $this->profile = 'usingredis';
        self::initJelixConfig();

        parent::setUp();
        if (!$this->_kvdbSetUp())
            return;

        $this->redis = new \PhpRedis\Redis(TESTAPP_REDIS_HOST,6379);
        $this->redis->flushall();
    }

    public function tearDown() : void {
        if ($this->redis) {
            //$this->redis->quit();
            $this->redis->disconnect();
        }
    }

    public function testGarbage (){

        $kv = jKVDb::getConnection($this->profile);

        $kv->set('remainingDataKey','remaining data');
        $kv->setWithTtl('garbage1DataKey','data send to the garbage',1);
        $kv->setWithTtl('garbage2DataKey','other data send to the garbage',strtotime("-1 day"));

        sleep(2);

        $this->assertTrue($kv->garbage());

        $this->assertEquals(serialize('remaining data'), $this->redis->get('remainingDataKey'));
        $this->assertNull($this->redis->get('garbage1DataKey'));
        $this->assertNull($this->redis->get('garbage2DataKey'));
    }

    public function testFlush (){

        $kv = jKVDb::getConnection($this->profile);

        $kv->set('flush1DataKey','some data',0);
        $kv->setWithTtl('flush2DataKey','data to remove',strtotime("+1 day"));
        $kv->setWithTtl('flush3DataKey','other data to remove',time()+30);

        $this->assertEquals(serialize('some data'), $this->redis->get('flush1DataKey'));
        $this->assertEquals(serialize('data to remove'), $this->redis->get('flush2DataKey'));
        $this->assertEquals(serialize('other data to remove'), $this->redis->get('flush3DataKey'));
        $this->assertTrue($kv->flush());
        $this->assertNull($this->redis->get('flush1DataKey'));
        $this->assertNull($this->redis->get('flush2DataKey'));
        $this->assertNull($this->redis->get('flush3DataKey'));

    }
    function testHashes() {
        /** @var redis_phpKVDriver $kv */
        $kv = jKVDb::getConnection($this->profile);
        $key = 'redis_phpTest';
        $kv->delete($key);

        $this->assertFalse($kv->hExists($key, 'foo'));
        $this->assertFalse($kv->hExists($key, 'bar'));

        $this->assertEquals(1, $kv->hSet($key, 'foo', 'first'));
        $this->assertEquals(1, $kv->hSet($key, 'bar', 'second'));
        $this->assertFalse($kv->hSetNx($key, 'bar', 'second2'));

        $this->assertEquals('first', $kv->hGet($key, 'foo'));
        $this->assertEquals('second', $kv->hGet($key, 'bar'));

        $this->assertEquals(array('foo'=>'first', 'bar'=>'second'), $kv->hGetAll($key));
        $this->assertEquals(array('foo'=>'first', 'bar'=>'second'), $kv->hMGet($key, array('foo', 'bar')));
        $this->assertEquals(2, $kv->hLen($key));
        $this->assertEquals(array('foo', 'bar'), $kv->hKeys($key));
        $this->assertEquals(array('first', 'second'), $kv->hVals($key));

        $this->assertTrue($kv->hMSet($key, array('foo'=>'first2', 'bar'=>'second2')));
        $this->assertEquals(array('foo'=>'first2', 'bar'=>'second2'), $kv->hMGet($key, array('foo', 'bar')));
    }
}

