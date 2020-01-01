<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Brice Tencé
* @contributor Laurent Jouanneau
* @copyright   2012 Brice Tencé, 2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jzone_cacheTest extends \Jelix\UnitTests\UnitTestCase {

    function setUp() : void  {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        jApp::coord()->response = new jResponseHtml();
    }
    function tearDown() : void  {
        jApp::popCurrentModule();
    }

    function testCache() {

        $zoneSel = 'test_cache';

        //remove cache for a fresh startup:
        jZone::clearAll( $zoneSel );

        $GLOBALS['zoneTestCache'] = 0;

        $tpl = new jTpl();
        $expectedOutput = $tpl->fetch('test_zone_cache');

        $firstCachedShot = jZone::get($zoneSel);
        $this->assertEquals(1, $GLOBALS['zoneTestCache'], 'First cached zone get, %s');
        $this->assertEquals($expectedOutput, $firstCachedShot, 'First cached zone get, %s');
        $secondCachedShot = jZone::get($zoneSel);
        $this->assertEquals(1, $GLOBALS['zoneTestCache'], 'Second cached zone get, %s');
        $this->assertEquals($expectedOutput, $secondCachedShot, 'Second cached zone get, %s');

        unset($GLOBALS['zoneTestCache']);
    }

    function testCancelCache() {

        $zoneSel = 'test_cache';

        //remove cache for a fresh startup:
        jZone::clearAll( $zoneSel );

        $GLOBALS['zoneTestCache'] = 0;

        $tpl = new jTpl();
        $expectedOutput = $tpl->fetch('test_zone_cache');

        $firstCachedShot = jZone::get($zoneSel, array('cancelCache'=>true));
        $this->assertEquals( 1, $GLOBALS['zoneTestCache'],'First canceled cached zone get, %s');
        $this->assertEquals($expectedOutput, $firstCachedShot, 'First canceled cached zone get, %s');
        $secondCachedShot = jZone::get($zoneSel, array('cancelCache'=>true));
        $this->assertEquals( 2, $GLOBALS['zoneTestCache'],'Second canceled cached zone get, %s');
        $this->assertEquals($expectedOutput, $secondCachedShot, 'Second canceled cached zone get, %s');

        unset($GLOBALS['zoneTestCache']);
    }

    function testCacheMetaParam() {

        $zoneSel = 'test_cache_meta';

        //remove cache for a fresh startup:
        jZone::clearAll( $zoneSel );

        $resp = jApp::coord()->response;
        $titleBefore = $resp->title;
        $bodyTagAttrBefore = $resp->bodyTagAttributes;
        $resp->bodyTagAttributes = array();

        $actionTitle = 'That\'s a title from action';
        $bodyAttrs = array('class' => 'special');
        jZone::get($zoneSel, array('zoneTitle'=>$actionTitle));
        $this->assertEquals($actionTitle, $resp->title, 'First cached zone get with param - meta, %s');
        $this->assertEquals($bodyAttrs, $resp->bodyTagAttributes, 'First cached zone get with param - meta, %s');
        $resp->title = $titleBefore;
        $resp->bodyTagAttributes = $bodyTagAttrBefore;
        jZone::get($zoneSel, array('zoneTitle'=>$actionTitle));
        $this->assertEquals($actionTitle, $resp->title, 'Second cached zone get with param - meta, %s');
        $this->assertEquals($bodyAttrs, $resp->bodyTagAttributes, 'First cached zone get with param - meta, %s');
        $resp->title = $titleBefore;
        $resp->bodyTagAttributes = $bodyTagAttrBefore;

        jZone::get($zoneSel);
        $expectedOutput = 'That\'s a title from zone';
        $this->assertEquals($expectedOutput, $resp->title, 'First cached zone get - meta, %s');
        $this->assertEquals($bodyAttrs, $resp->bodyTagAttributes, 'First cached zone get with param - meta, %s');
        $resp->title = $titleBefore;
        $resp->bodyTagAttributes = $bodyTagAttrBefore;
        jZone::get($zoneSel);
        $this->assertEquals($expectedOutput, $resp->title, 'Second cached zone get - meta, %s');
        $this->assertEquals($bodyAttrs, $resp->bodyTagAttributes, 'First cached zone get with param - meta, %s');
        $resp->title = $titleBefore;
        $resp->bodyTagAttributes = $bodyTagAttrBefore;
    }

    function testCacheMetaDeep() {

        $zoneSel = 'test_cache_meta_deep';

        //remove cache for a fresh startup:
        jZone::clearAll( $zoneSel );

        $resp = jApp::coord()->response;
        $titleBefore = $resp->title;

        jZone::get($zoneSel);
        $expectedOutput = 'That\'s a deep zone title with backslash \\';
        $this->assertEquals($expectedOutput, $resp->title, 'First cached zone get - "deep" meta, %s');
        $resp->title = $titleBefore;
        jZone::get($zoneSel);
        $this->assertEquals($expectedOutput, $resp->title, 'Second cached zone get - "deep" meta, %s');
        $resp->title = $titleBefore;
    }

}

