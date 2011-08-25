<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2011 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTjimagemodifier extends UnitTestCase {

    protected $origFile;

    function testStart() {
        $this->origFile = jApp::wwwPath('imagemodifier/logo_test.png');
        if (!is_writable(jApp::wwwPath('cache/images/')))
            $this->fail("cache/images/ is not writable");

    }

    function setUp() {
    }

    function testGet() {

        $cacheName = 'cache/images/'.md5('imagemodifier/logo_test.pngwidth50height30').'.png';
        $cacheFile = jApp::wwwPath($cacheName);
        if (file_exists($cacheFile))
            unlink($cacheFile);

        $attributes = jImageModifier::get('imagemodifier/logo_test.png', array('width'=>50, 'height'=>30));
        $this->assertEqual($GLOBALS['gJCoord']->request->getServerURI().$GLOBALS['gJConfig']->urlengine['basePath'].$cacheName, $attributes['src']);
        $this->assertTrue(file_exists($cacheFile));


        $image = imagecreatefrompng($cacheFile);
        $this->assertEqual(50, imagesx($image));
        $this->assertEqual(30, imagesy($image));
        @imagedestroy($image);
        if (file_exists($cacheFile))
            unlink($cacheFile);
    }

    function testGetOmo() {
        $cacheName = 'cache/images/'.md5('imagemodifier/logo_test.pngmaxwidth50maxheight30omo1').'.png';
        $cacheFile = jApp::wwwPath($cacheName);
        if (file_exists($cacheFile))
            unlink($cacheFile);

        $attributes = jImageModifier::get('imagemodifier/logo_test.png', array('maxwidth'=>50, 'maxheight'=>30, 'omo'=>true));
        $this->assertTrue(file_exists($cacheFile));
        $this->assertEqual($GLOBALS['gJCoord']->request->getServerURI().$GLOBALS['gJConfig']->urlengine['basePath'].$cacheName, $attributes['src']);
        //$this->assertEqual('50', $attributes['width']);
        //$this->assertEqual('16', $attributes['height']);

        $image = imagecreatefrompng($cacheFile);
        $this->assertEqual(50, imagesx($image));
        $this->assertEqual(16, imagesy($image));
        @imagedestroy($image);
        if (file_exists($cacheFile))
            unlink($cacheFile);
    }

    function testGetOmo2() {
        $cacheName = 'cache/images/'.md5('imagemodifier/logo_test.pngwidth50height30omo1').'.png';
        $cacheFile = jApp::wwwPath($cacheName);
        if (file_exists($cacheFile))
            unlink($cacheFile);

        $attributes = jImageModifier::get('imagemodifier/logo_test.png', array('width'=>50, 'height'=>30, 'omo'=>true));
        $this->assertEqual($GLOBALS['gJCoord']->request->getServerURI().$GLOBALS['gJConfig']->urlengine['basePath'].$cacheName, $attributes['src']);
        $this->assertEqual('50', $attributes['width']);
        $this->assertEqual('30', $attributes['height']);
        $this->assertTrue(file_exists($cacheFile));

        $image = imagecreatefrompng($cacheFile);
        $this->assertEqual(50, imagesx($image));
        $this->assertEqual(30, imagesy($image));
        @imagedestroy($image);
        if (file_exists($cacheFile))
            unlink($cacheFile);
    }




}
