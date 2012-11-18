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

require_once(__DIR__.'/jcache.lib.php');

/**
* Tests API jCache
* @package     testapp
* @subpackage  jelix_tests module
*/

class UTjCacheFile extends UTjCacheAPI {

    protected $profile = 'usingfile';

    public function setUp (){
        if (file_exists(jApp::tempPath().'cache'))
            jFile::removeDir(jApp::tempPath().'cache/',false);
    }

    public function testSet (){
        parent::testSet();
        $this->assertTrue(file_exists(jApp::tempPath().'cache'));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache___noExpireKey.cache'));
        $this->assertFalse(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache___expiredKey.cache'));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache___ttlInDateKey.cache'));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache___ttlInSecondesKey.cache'));
    }


    public function testGarbage (){
        parent::testGarbage();
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache___remainingDataKey.cache'));
        $this->assertFalse(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache___garbage1DataKey.cache'));
        $this->assertFalse(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache___garbage1DataKey.cache'));
    }

    public function testFlush (){
        parent::testFlush();

        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache___flush1DataKey.cache'));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache___flush2DataKey.cache'));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache___flush3DataKey.cache'));
        $this->assertTrue(jCache::flush($this->profile));
        $this->assertFalse(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache___flush1DataKey.cache'));
        $this->assertFalse(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache___flush2DataKey.cache'));
        $this->assertFalse(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache___flush3DataKey.cache'));
    }

}
