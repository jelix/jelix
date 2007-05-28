<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006-2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class dbCtrl extends jController {

   function index() {
      $rep = $this->getResponse('unittest');
      $rep->title = 'test unitaires sur jDb';
      $ut = jClasses::create("unittestservice");
      $ut->init($rep);
      $ut->jdbTest();
      return $rep;
   }

}
?>