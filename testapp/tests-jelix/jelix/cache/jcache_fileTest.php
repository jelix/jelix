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

class jCache_FileTest extends jCacheAPITest {

    public function setUp () : void {
        $this->profile = 'usingfile';
        if (file_exists(jApp::tempPath().'cache'))
            jFile::removeDir(jApp::tempPath().'cache/',false);
    }

    public function testSet (){
        $this->assertTrue(jCache::set('hello',"lorem ipsum",null, $this->profile));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/hello.cache'));
        $this->assertTrue(jCache::set('hello/foo/bar',"lorem ipsum1",null, $this->profile));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/hello/foo/bar.cache'));
        $this->assertTrue(jCache::set('hello:foo/bar-baz.yo',"lorem ipsum3",null, $this->profile));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/hello:foo/bar-baz.yo.cache'));
        $this->assertTrue(jCache::set('/hello/foo/bar',"lorem ipsum2",null, $this->profile));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/jelix_cache/hello/foo/bar.cache'));
        $this->assertEquals("lorem ipsum", jCache::get('hello', $this->profile));
        $this->assertEquals("lorem ipsum1", jCache::get('hello/foo/bar', $this->profile));
        $this->assertEquals("lorem ipsum2", jCache::get('/hello/foo/bar', $this->profile));
        $this->assertEquals("lorem ipsum3", jCache::get('hello:foo/bar-baz.yo', $this->profile));
    }

    public function testSetMultipleDirectories (){   
        $this->assertTrue(jCache::set('hello',"lorem ipsum",null, 'usingfile2'));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile2/jelix_cache/5/5d/hello.cache'));
        $this->assertTrue(jCache::set('hello/foo/bar',"lorem ipsum1",null, 'usingfile2'));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile2/jelix_cache/7/74/hello/foo/bar.cache'));
        $this->assertTrue(jCache::set('/hello/foo/bar',"lorem ipsum2",null, 'usingfile2'));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile2/jelix_cache/f/f3/jelix_cache/hello/foo/bar.cache'));
        $this->assertTrue(jCache::set('hello:foo/bar-baz.yo',"lorem ipsum3",null, 'usingfile2'));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile2/jelix_cache/d/d5/hello:foo/bar-baz.yo.cache'));
        $this->assertEquals("lorem ipsum", jCache::get('hello', 'usingfile2'));
        $this->assertEquals("lorem ipsum1", jCache::get('hello/foo/bar', 'usingfile2'));
        $this->assertEquals("lorem ipsum2", jCache::get('/hello/foo/bar', 'usingfile2'));
        $this->assertEquals("lorem ipsum3", jCache::get('hello:foo/bar-baz.yo', 'usingfile2'));
    }

    public function testSetWithTtl (){
        parent::testSetWithTtl();
        $this->assertTrue(file_exists(jApp::tempPath().'cache'));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/noExpireKey.cache'));
        $this->assertFalse(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/expiredKey.cache'));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/ttlInDateKey.cache'));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/ttlInSecondesKey.cache'));
    }

    public function testGarbage (){
        parent::testGarbage();
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/remainingDataKey.cache'));
        $this->assertFalse(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/garbage1DataKey.cache'));
        $this->assertFalse(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/garbage1DataKey.cache'));
    }

    public function testFlush (){
        parent::testFlush();

        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/flush1DataKey.cache'));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/flush2DataKey.cache'));
        $this->assertTrue(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/flush3DataKey.cache'));
        $this->assertTrue(jCache::flush($this->profile));
        $this->assertFalse(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/flush1DataKey.cache'));
        $this->assertFalse(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/flush2DataKey.cache'));
        $this->assertFalse(file_exists(jApp::tempPath().'cache/usingfile/jelix_cache/flush3DataKey.cache'));
    }

}
