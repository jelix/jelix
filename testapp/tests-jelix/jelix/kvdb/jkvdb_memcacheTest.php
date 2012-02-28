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

/**
* Tests API jKVDb
* @package     testapp
* @subpackage  jelix_tests module
*/

class UTjKVDbMemcache extends UTjKVDb {

    protected $profile = 'usingmemcache';

    function skip() {
        parent::skip();
        $this->skipIf(!extension_loaded('memcache'), 'UTjKVDbMemcache cannot be run because memcache is not installed');
    }

    public function setUp (){
        $this->mmc = memcache_connect('localhost',11211);
        memcache_flush($this->mmc);
    }

    public function tearDown() {
        memcache_close($this->mmc);
        $this->mmc = null;
    }
    
    public function testGarbage () {

        $kv = jKVDb::getConnection($this->profile);

        $kv->set('remainingDataKey','remaining data');
        $kv->setWithTtl('garbage1DataKey','data send to the garbage',1);
        $kv->setWithTtl('garbage2DataKey','other data send to the garbage',strtotime("-1 day"));

        sleep(2);

        $this->assertTrue($kv->garbage());

        $this->assertTrue(memcache_get($this->mmc,'remainingDataKey')=='remaining data');
        $this->assertFalse(memcache_get($this->mmc,'garbage1DataKey'));
        $this->assertFalse(memcache_get($this->mmc,'garbage2DataKey'));
    }

    public function testFlush (){

        $kv = jKVDb::getConnection($this->profile);

        $kv->set('flush1DataKey','some data',0);
        $kv->setWithTtl('flush2DataKey','data to remove',strtotime("+1 day"));
        $kv->setWithTtl('flush3DataKey','other data to remove',time()+30);

        $this->assertTrue(memcache_get($this->mmc,'flush1DataKey'));
        $this->assertTrue(memcache_get($this->mmc,'flush2DataKey'));
        $this->assertTrue(memcache_get($this->mmc,'flush3DataKey'));
        $this->assertTrue($kv->flush());
        $this->assertFalse(memcache_get($this->mmc,'flush1DataKey'));
        $this->assertFalse(memcache_get($this->mmc,'flush2DataKey'));
        $this->assertFalse(memcache_get($this->mmc,'flush3DataKey'));
    }
    
    
}

?>