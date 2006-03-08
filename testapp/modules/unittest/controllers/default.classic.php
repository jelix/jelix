<?php
/**
* @package     testapp
* @subpackage  unittest module
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class CTDefault extends jController {

   function index() {
      $rep = $this->getResponse('unittest');
      $rep->title = 'test unitaires';
      $rep->body->assign('MAIN','');
      return $rep;
   }

   /*
   *
   */
   function testevent (){
      $rep = $this->getResponse('unittest');
      $rep->title = 'test unitaires sur jEvent';

      $ut = jClasses::create("unittestservice");
      $ut->init($rep);
      $ut->eventsTest();

      return $rep;
   }

   function testurlcreate(){
      $rep = $this->getResponse('unittest');
      $rep->title = 'test unitaires sur la creation d\'url avec jUrl';

      $ut = jClasses::create("unittestservice");
      $ut->init($rep);
      $ut->urlsCreateTest();

      return $rep;
   }
   function testurlparse(){
      $rep = $this->getResponse('unittest');
      $rep->title = 'test unitaires sur le parsing d\'url avec jUrl';

      $ut = jClasses::create("unittestservice");
      $ut->init($rep);
      $ut->urlsParseTest();

      return $rep;
   }
}
?>