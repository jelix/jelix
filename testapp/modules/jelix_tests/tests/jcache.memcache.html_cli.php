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

class UTjCacheMemcache extends UTjCacheAPI {

    protected $profile = 'usingmemcache';

    protected $mmport = 'localhost';
    protected $mmhost = 11211;

    protected $wrongversion = false;

    function getTests() {
        $r = parent::getTests();
        if (count($r)) {
            if (version_compare(phpversion('memcache'), '3.0.1') > 0) {
                $this->wrongversion = true;
                return array('tfail');
            }
        }
        return $r;
    }

    public function tfail() {
        $this->fail('UTjCacheMemcache cannot be run because version of memcache is wrong (should be <= 3.0.1)');
    }

    public function setUp () {
        if ($this->wrongversion)
            return;
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
