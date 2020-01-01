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

class eventResponseToReturn {

    static $responses = array();

}


class eventsTest extends \Jelix\UnitTests\UnitTestCase {

    function setUp() : void {
        jEvent::clearCache();
        self::initJelixConfig();
        jFile::removeDir(jApp::tempPath(), false);
        parent::setUp();
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

    function testResponseItem()
    {
        eventResponseToReturn::$responses = array(
            'testapp' => array('foo' => 'bar'),
            'jelix_tests' => array('foo' => '123'),
        );
        $response = jEvent::notify('TestEventResponse');
        $response = $response->getResponseByKey('foo');
        $this->assertNotNull($response);
        sort($response);
        $this->assertEquals(array('123', 'bar'), $response);
    }

    function testNoResponseItem() {
        eventResponseToReturn::$responses = array();
        $response = jEvent::notify('TestEventResponse');
        $response = $response->getResponseByKey('foo');
        $this->assertNull($response);
    }


    function testBoolItemAllTrue()
    {
        eventResponseToReturn::$responses = array(
            'testapp' => array('foo' => true),
            'jelix_tests' => array('foo' => true),
        );
        $response = jEvent::notify('TestEventResponse');
        $this->assertTrue($response->allResponsesByKeyAreTrue('foo'));
        $this->assertFalse($response->allResponsesByKeyAreFalse('foo'));
    }

    function testBoolItemNotAllTrue() {
        eventResponseToReturn::$responses = array(
            'testapp'=> array('foo'=>false),
            'jelix_tests'=> array('foo'=>true),
        );
        $response = jEvent::notify('TestEventResponse');
        $this->assertFalse($response->allResponsesByKeyAreTrue('foo'));
        $this->assertFalse($response->allResponsesByKeyAreFalse('foo'));
    }

    function testBoolItemAllFalse() {
        eventResponseToReturn::$responses = array(
            'testapp'=> array('foo'=>false),
            'jelix_tests'=> array('foo'=>false),
        );
        $response = jEvent::notify('TestEventResponse');
        $this->assertFalse($response->allResponsesByKeyAreTrue('foo'));
        $this->assertTrue($response->allResponsesByKeyAreFalse('foo'));
    }

    function testBoolItemNoValues() {
        eventResponseToReturn::$responses = array();
        $response = jEvent::notify('TestEventResponse');
        $this->assertNull($response->allResponsesByKeyAreTrue('foo'));
        $this->assertNull($response->allResponsesByKeyAreFalse('foo'));
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
