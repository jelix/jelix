<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006-2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class testeventsListener extends jEventListener{

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

}
?>