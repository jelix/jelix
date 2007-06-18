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

class defaultCtrl extends jController {

   function index() {
      $rep = $this->getResponse('unittest');
      $rep->title = 'test unitaires';
      $rep->body->assign('MAIN','');
      return $rep;
   }

   function testsimpletest() {
      $rep = $this->getResponse('unittest');
      $rep->title = 'test unitaires sur évolutions simpletest';
      $ut = jClasses::create("unittestservice");
      $ut->init($rep);
      $ut->simpletestTest();
      return $rep;
   }
}
?>