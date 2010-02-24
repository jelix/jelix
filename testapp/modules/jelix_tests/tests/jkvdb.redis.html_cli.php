<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @copyright   2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(dirname(__FILE__).'/jkvdb.lib.php');
require_once(LIB_PATH . 'php5redis/Php5Redis.php');
/**
* Tests API jKVDb
* @package     testapp
* @subpackage  jelix_tests module
*/

class UTjKVDbRedis extends UTjKVDb {

    protected $profile = 'usingredis';

    protected $redis;

    public function setUp (){
        $this->redis = new Php5Redis('localhost',6379);
        $this->redis->flushall();
    }

    public function tearDown() {
        //$this->redis->quit();
        $this->redis->disconnect();
    }
    public function testGarbage (){

        $kv = jKVDb::getConnection($this->profile);

        $kv->set('remainingDataKey','remaining data');
        $kv->setWithTtl('garbage1DataKey','data send to the garbage',1);
        $kv->setWithTtl('garbage2DataKey','other data send to the garbage',strtotime("-1 day"));

        sleep(2);

        $this->assertTrue($kv->garbage());

        $this->assertEqual($this->redis->get('remainingDataKey'),serialize('remaining data'));
        $this->assertNull($this->redis->get('garbage1DataKey'));
        $this->assertNull($this->redis->get('garbage2DataKey'));
    }

    public function testFlush (){

        $kv = jKVDb::getConnection($this->profile);

        $kv->set('flush1DataKey','some data',0);
        $kv->setWithTtl('flush2DataKey','data to remove',strtotime("+1 day"));
        $kv->setWithTtl('flush3DataKey','other data to remove',time()+30);

        $this->assertEqual($this->redis->get('flush1DataKey'),serialize('some data'));
        $this->assertEqual($this->redis->get('flush2DataKey'),serialize('data to remove'));
        $this->assertEqual($this->redis->get('flush3DataKey'),serialize('other data to remove'));
        $this->assertTrue($kv->flush());
        $this->assertNull($this->redis->get('flush1DataKey'));
        $this->assertNull($this->redis->get('flush2DataKey'));
        $this->assertNull($this->redis->get('flush3DataKey'));

    }
}

?>