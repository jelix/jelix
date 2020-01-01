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

/**
* Tests API jCache
* @package     testapp
* @subpackage  jelix_tests module
*/

class jCache_DbTest extends jCacheAPITest {

    public function setUp () : void {
        $this->profile = 'usingdb';
        parent::setUp();
        $this->emptyTable('jlx_cache');
    }

    public function testGet (){
        parent::testGet();

        $this->insertRecordsIntoTable('jlx_cache', array('cache_key','cache_data','cache_date'),array(array('cache_key'=>'phpIncompleteClassKey','cache_data'=>'O:9:"dummyData":2:{s:5:"label";s:23:"test unserializing data";s:11:"description";s:26:"for expecting an exception";}','cache_date'=>null)));
        $data = jCache::get('phpIncompleteClassKey',$this->profile);
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            $this->assertTrue(is_object($data));
        }
        else {
            $this->assertFalse(is_object($data));
        }
    }

    public function testGarbage (){
        parent::testGarbage();
        $this->assertFalse(jCache::garbage());

        $this->assertTableContainsRecords('jlx_cache',array(
            array('cache_key'=>'remainingDataKey','cache_data'=>serialize('remaining data'),'cache_date'=>null)
        ));
    }

    public function testFlush (){
        parent::testFlush();

        $this->assertTableHasNRecords('jlx_cache', 3);
        $this->assertTrue(jCache::flush($this->profile));
        $this->assertTableIsEmpty('jlx_cache');

    }

}

?>
