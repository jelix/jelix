<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Brice Tencé
* @copyright   2012 Brice Tencé
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class UTjzonecache extends jUnitTestCase {

    function testCache() {

        $zoneSel = 'test_cache';

        //remove cache for a fresh startup:
        jZone::clearAll( $zoneSel );

        $GLOBALS['zoneTestCache'] = 0;

        $tpl = new jTpl();
        $expectedOutput = $tpl->fetch('test_zone_cache');

        $firstCachedShot = jZone::get($zoneSel);
        $this->assertEqualOrDiff($GLOBALS['zoneTestCache'], 1, 'First cached zone get, %s');
        $this->assertEqualOrDiff($firstCachedShot, $expectedOutput, 'First cached zone get, %s');
        $secondCachedShot = jZone::get($zoneSel);
        $this->assertEqualOrDiff($GLOBALS['zoneTestCache'], 1, 'Second cached zone get, %s');
        $this->assertEqualOrDiff($secondCachedShot, $expectedOutput, 'Second cached zone get, %s');

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
        $this->assertEqualOrDiff($GLOBALS['zoneTestCache'], 1, 'First canceled cached zone get, %s');
        $this->assertEqualOrDiff($firstCachedShot, $expectedOutput, 'First canceled cached zone get, %s');
        $secondCachedShot = jZone::get($zoneSel, array('cancelCache'=>true));
        $this->assertEqualOrDiff($GLOBALS['zoneTestCache'], 2, 'Second canceled cached zone get, %s');
        $this->assertEqualOrDiff($secondCachedShot, $expectedOutput, 'Second canceled cached zone get, %s');

        unset($GLOBALS['zoneTestCache']);
    }

    function testCacheMetaParam() {

        $zoneSel = 'test_cache_meta';

        //remove cache for a fresh startup:
        jZone::clearAll( $zoneSel );

        $resp = jApp::coord()->response;
        $titleBefore = $resp->title;

        $actionTitle = 'title from action';
        jZone::get($zoneSel, array('zoneTitle'=>$actionTitle));
        $this->assertEqualOrDiff($resp->title, $actionTitle, 'First cached zone get with param - meta, %s');
        $resp->title = $titleBefore;
        jZone::get($zoneSel, array('zoneTitle'=>'title from action'));
        $this->assertEqualOrDiff($resp->title, $actionTitle, 'Second cached zone get with param - meta, %s');
        $resp->title = $titleBefore;

        jZone::get($zoneSel);
        $this->assertEqualOrDiff($resp->title, 'title from zone', 'First cached zone get - meta, %s');
        $resp->title = $titleBefore;
        jZone::get($zoneSel);
        $this->assertEqualOrDiff($resp->title, 'title from zone', 'Second cached zone get - meta, %s');
        $resp->title = $titleBefore;
    }

    function testCacheMetaDeep() {

        $zoneSel = 'test_cache_meta_deep';

        //remove cache for a fresh startup:
        jZone::clearAll( $zoneSel );

        $resp = jApp::coord()->response;
        $titleBefore = $resp->title;

        jZone::get($zoneSel);
        $this->assertEqualOrDiff($resp->title, 'deep zone title', 'First cached zone get - "deep" meta, %s');
        $resp->title = $titleBefore;
        jZone::get($zoneSel);
        $this->assertEqualOrDiff($resp->title, 'deep zone title', 'Second cached zone get - "deep" meta, %s');
        $resp->title = $titleBefore;
    }

}

