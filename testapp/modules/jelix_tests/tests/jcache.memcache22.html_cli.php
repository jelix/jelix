<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Tahina Ramaroson
* @contributor Sylvain de Vathaire
* @contributor Laurent Jouanneau
* @copyright   NEOV 2009
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(dirname(__FILE__).'/jcache.lib.php');

/**
* Tests API jCache
* @package     testapp
* @subpackage  jelix_tests module
*/

class UTjCacheMemcache22 extends UTjCacheAPI {

    protected $profile = 'usingmemcache';

    protected $mmhost = 'localhost';
    protected $mmport = 11211;

    function skip() {
        parent::skip();
        $this->skipIf(!extension_loaded('memcache'), 'UTjCacheMemcache22  cannot be run because memcache is not installed');
        $this->skipIf(version_compare(phpversion('memcache'), '3.0.1') > 0, 'UTjCacheMemcache22 cannot be run because version of memcache is wrong (should be <= 3.0.1)');
    }

    public function setUp () {
        if (isset($this->conf['servers']))
            list($this->mmhost, $this->mmport) = explode(":",$this->conf['servers']);
        $mmc = memcache_connect($this->mmhost, $this->mmport);
        memcache_flush($mmc);
    }

    public function testGet (){
        parent::testGet();
        //Memcache manages serialization and unserialization process internally. It throws an exception in case of errors
        $this->pass();
    }

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
        $this->assertTrue(memcache_get($mmc,'flush1DataKey'));
        $this->assertTrue(memcache_get($mmc,'flush2DataKey'));
        $this->assertTrue(memcache_get($mmc,'flush3DataKey'));
        $this->assertTrue(jCache::flush($this->profile));
        $this->assertFalse(memcache_get($mmc,'flush1DataKey'));
        $this->assertFalse(memcache_get($mmc,'flush2DataKey'));
        $this->assertFalse(memcache_get($mmc,'flush3DataKey'));
    }

}
