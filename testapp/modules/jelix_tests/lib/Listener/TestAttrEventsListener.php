<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2024 Laurent Jouanneau
* @link        https://www.jelix.org
* @licence     MIT
*/
namespace  JelixTests\Tests\Listener;

use Jelix\Event\Attribute\ListenEvent;
use Jelix\Event\Attribute\ListenEventClass;
use Testapp\Tests\EventForTest;

class TestAttrEventsListener
{

    #[ListenEvent(eventName:'TestEventWithParams')]
    function eventWithParams ($event) {
        $event->Add(array('params2'=>$event->getParam('hello')));

    }

    #[ListenEvent('TestEvent')]
    function onTestEvent ($event) {
        $event->Add(array('module'=>'jelix_tests2','ok'=>true));
    }


    #[ListenEventClass]
    function testEventObject(EventForTest $event)
    {
        $event->setDummy2Value('TestAttrEventsListener called');
    }
}
