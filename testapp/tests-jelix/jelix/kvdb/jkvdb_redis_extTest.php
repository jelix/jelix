<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @copyright   2010-2017 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jkvdb.lib.php');

/**
* Tests API jKVDb with the redis_ext driver
* @package     testapp
* @subpackage  jelix_tests module
*/

class jkvdb_redis_extTest extends jKVDbTest {

    protected $redis;

    function setUp () {
        if (!extension_loaded('redis'))
            $this->markTestSkipped('jkvdb_redis_extTest cannot be run because redis extension is not installed');

        $this->profile = 'usingredis_ext';
        self::initJelixConfig();

        parent::setUp();
        if (!$this->_kvdbSetUp())
            return;

        $this->redis = new jRedis();
        $this->redis->connect(TESTAPP_REDIS_HOST,6379);
        $this->redis->flushAll();
    }

    public function tearDown() {
        if ($this->redis) {
            $this->redis->close();
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
        $this->assertFalse($this->redis->get('garbage1DataKey'));
        $this->assertFalse($this->redis->get('garbage2DataKey'));
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
        $this->assertFalse($this->redis->get('flush1DataKey'));
        $this->assertFalse($this->redis->get('flush2DataKey'));
        $this->assertFalse($this->redis->get('flush3DataKey'));

    }

    function testDeletePrefix() {
        // let's fill the database values
        $this->redis->set('foo:bar', "yes");
        $this->redis->set('hello', "world");

        for($i=0; $i < 5500; $i++) {
            $this->redis->set('user:lorem:ipsum:machin:bidule:'.$i.'aaaaaa/bbbbbbb/ccccccc/dddddd', "name".$i);
        }

        $keys = $this->redis->keys('*');
        $this->assertEquals(5502, count($keys));
        sleep(1);

        // now let's delete them
        $this->redis->flushByPrefix("user:lorem:ipsum:machin:bidule:", 500);

        // let's verify that there is only two keys
        $keys = $this->redis->keys('*');
        $this->assertEquals(2, count($keys));
        sort($keys);
        $this->assertEquals(array('foo:bar', 'hello'), $keys);
    }


    function testHashes() {
        /** @var redis_extKVDriver $kv */
        $kv = jKVDb::getConnection($this->profile);
        $key = 'redis_extTest';
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
