<?php

use Testapp\Tests\EventForTest;

/**
 * @package     testapp
 * @subpackage  jelix_tests module
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2006-2024 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class eventResponseToReturn
{

    static $responses = array();
}


class eventsTest extends \Jelix\UnitTests\UnitTestCase
{

    function setUp(): void
    {
        self::initJelixConfig();
        jFile::removeDir(jApp::tempPath(), false);

        $warmup = new \Jelix\Event\EventWarmup(\Jelix\Core\App::app());
        $warmup->launch(\Jelix\Core\App::getEnabledModulesPaths(), 0);
        \Jelix\Core\App::reloadServices();
        parent::setUp();
    }

    function testBasics()
    {
        $response = jEvent::notify('TestEvent');
        $response = serialize($response->getResponse());
        $expected = serialize([
            array('module' => 'jelix_tests', 'ok' => true),
            array('module' => 'jelix_tests2', 'ok' => true)
        ]);

        $this->assertEquals($expected, $response, 'simple event');

        $expected = array('hello' => 'world');
        $response = jEvent::notify('TestEventWithParams', $expected);
        $response = $response->getResponse();
        $this->assertEquals('world', $response[0]['params'], 'event with parameters');
        $this->assertEquals('world', $response[1]['params2'], 'event with parameters');
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

    function testNoResponseItem()
    {
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

    function testBoolItemNotAllTrue()
    {
        eventResponseToReturn::$responses = array(
            'testapp' => array('foo' => false),
            'jelix_tests' => array('foo' => true),
        );
        $response = jEvent::notify('TestEventResponse');
        $this->assertFalse($response->allResponsesByKeyAreTrue('foo'));
        $this->assertFalse($response->allResponsesByKeyAreFalse('foo'));
    }

    function testBoolItemAllFalse()
    {
        eventResponseToReturn::$responses = array(
            'testapp' => array('foo' => false),
            'jelix_tests' => array('foo' => false),
        );
        $response = jEvent::notify('TestEventResponse');
        $this->assertFalse($response->allResponsesByKeyAreTrue('foo'));
        $this->assertTrue($response->allResponsesByKeyAreFalse('foo'));
    }

    function testBoolItemNoValues()
    {
        eventResponseToReturn::$responses = array();
        $response = jEvent::notify('TestEventResponse');
        $this->assertNull($response->allResponsesByKeyAreTrue('foo'));
        $this->assertNull($response->allResponsesByKeyAreFalse('foo'));
    }

    function testDisabledListener()
    {
        jApp::config()->disabledListeners['TestEvent'] = array('\JelixTests\Tests\Listener\TestEventsListener');

        $response = jEvent::notify('TestEvent');
        $response = serialize($response->getResponse());
        $expected = serialize([
            array('module' => 'jelix_tests2', 'ok' => true)
        ]);

        $this->assertEquals($expected, $response);
    }

    function testSingleDisabledListener()
    {
        jApp::config()->disabledListeners['TestEvent'] = '\JelixTests\Tests\Listener\TestEventsListener';

        $response = jEvent::notify('TestEvent');
        $response = $response->getResponse();
        $expected = [
            array('module' => 'jelix_tests2', 'ok' => true)
        ];

        $this->assertEquals($expected, $response);
    }

    function testEventObject()
    {
        $event = new EventForTest();
        jEvent::notify($event);
        $this->assertEquals('onTestEventObject called', $event->getDummyValue());
        $this->assertEquals('TestAttrEventsListener called', $event->getDummy2Value());
    }

    function testEventHavingNoListener()
    {
        $response = jEvent::notify('eventhavingnolistener');
        $this->assertEquals(array(), $response->getResponse());
    }

}
