<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class eventsTest extends PHPUnit_Framework_TestCase {

    function setUp() {
        jEvent::clearCache();
        jelix_init_test_env();
        jFile::removeDir(jApp::tempPath(), false);
    }

    function testBasics() {
      $response = jEvent::notify('TestEvent');
      $response = $response->getResponse ();
      $response = serialize($response[0]);
      $temoin = serialize(array('module'=>'jelix_tests','ok'=>true));

      $this->assertEquals($temoin, $response, 'simple event');

      $temoin = array('hello'=>'world');
      $response = jEvent::notify('TestEventWithParams',$temoin );
      $response = $response->getResponse ();
      $this->assertEquals('world', $response[0]['params'], 'event with parameters');
    }

    function testDisabledListener() {
        jApp::config()->disabledListeners['TestEvent'] = array('jelix_tests~testevents');

        $response = jEvent::notify('TestEvent');
        $response = $response->getResponse ();
        $this->assertEquals(array(), $response);
    }

    function testDisabledListener2() {
        jApp::config()->disabledListeners['TestEvent'] = 'jelix_tests~testevents';

        $response = jEvent::notify('TestEvent');
        $response = $response->getResponse ();
        $this->assertEquals(array(), $response);
    }
}
