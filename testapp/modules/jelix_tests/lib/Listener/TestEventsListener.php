<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2023 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace  JelixTests\Tests\Listener;

class TestEventsListener extends \jEventListener{

   /**
   *
   */
   function onTestEventWithParams ($event) {
        $event->Add(array('params'=>$event->getParam('hello')));

   }

   /**
   *
   */
   function onTestEvent ($event) {
        $event->Add(array('module'=>'jelix_tests','ok'=>true));
   }

   function onTestEventResponse($event) {
       if (isset(\eventResponseToReturn::$responses['jelix_tests'])) {
           $event->add(\eventResponseToReturn::$responses['jelix_tests']);
       }
   }

}
