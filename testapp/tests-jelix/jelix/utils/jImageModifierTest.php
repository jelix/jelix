<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2011-2012 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jImageModifierTest extends \Jelix\UnitTests\UnitTestCase {

    protected $origFile;

    protected function setUp() : void {
        if (!is_writable(jApp::wwwPath('cache/images/'))) {
            $this->markTestSkipped(
              'cache/images/ is not writable'
            );
        }
        parent::setUp();
        $this->origFile = jApp::wwwPath('imagemodifier/logo_test.png');
        self::initClassicRequest(TESTAPP_URL.'index.php');
    }

    function testGet() {

        $cacheName = 'cache/images/imagemodifier/logo_test.png.cache/'.md5('imagemodifier/logo_test.pngwidth50height30').'.png';
        $cacheFile = jApp::wwwPath($cacheName);
        if (file_exists($cacheFile))
            unlink($cacheFile);

        $attributes = jImageModifier::get('imagemodifier/logo_test.png', array('width'=>50, 'height'=>30));
        $this->assertEquals($attributes['src'], jApp::coord()->request->getServerURI().jApp::urlBasePath().$cacheName);
        $this->assertTrue(file_exists($cacheFile));

        $image = imagecreatefrompng($cacheFile);
        $this->assertEquals(50, imagesx($image));
        $this->assertEquals(30, imagesy($image));
        @imagedestroy($image);
        if (file_exists($cacheFile))
            unlink($cacheFile);
    }

    function testGetOmo() {
        $cacheName = 'cache/images/imagemodifier/logo_test.png.cache/'.md5('imagemodifier/logo_test.pngmaxwidth50maxheight30omo1').'.png';
        $cacheFile = jApp::wwwPath($cacheName);
        if (file_exists($cacheFile))
            unlink($cacheFile);

        $attributes = jImageModifier::get('imagemodifier/logo_test.png', array('maxwidth'=>50, 'maxheight'=>30, 'omo'=>true));
        $this->assertTrue(file_exists($cacheFile));
        $this->assertEquals($attributes['src'], jApp::coord()->request->getServerURI().jApp::urlBasePath().$cacheName);
        //$this->assertEquals('50', $attributes['width']);
        //$this->assertEquals('16', $attributes['height']);

        $image = imagecreatefrompng($cacheFile);
        $this->assertEquals(50, imagesx($image));
        $this->assertEquals(16, imagesy($image));
        @imagedestroy($image);
        if (file_exists($cacheFile))
            unlink($cacheFile);
    }

    function testGetOmo2() {
        $cacheName = 'cache/images/imagemodifier/logo_test.png.cache/'.md5('imagemodifier/logo_test.pngwidth50height30omo1').'.png';
        $cacheFile = jApp::wwwPath($cacheName);
        if (file_exists($cacheFile))
            unlink($cacheFile);

        $attributes = jImageModifier::get('imagemodifier/logo_test.png', array('width'=>50, 'height'=>30, 'omo'=>true));
        $this->assertEquals($attributes['src'], jApp::coord()->request->getServerURI().jApp::urlBasePath().$cacheName);
        $this->assertEquals('50', $attributes['width']);
        $this->assertEquals('30', $attributes['height']);
        $this->assertTrue(file_exists($cacheFile));

        $image = imagecreatefrompng($cacheFile);
        $this->assertEquals(50, imagesx($image));
        $this->assertEquals(30, imagesy($image));
        @imagedestroy($image);
        if (file_exists($cacheFile))
            unlink($cacheFile);
    }
}
