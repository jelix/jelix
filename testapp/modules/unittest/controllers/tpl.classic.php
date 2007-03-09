<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class tplCtrl extends jController {

   function parseExpression() {
      $rep = $this->getResponse('unittest');
      $rep->title = 'test unitaires sur le compilateur jtpl';
      $ut = jClasses::create("unittestservice");
      $ut->init($rep);
      $ut->jtplExprTest();
      return $rep;
   }
}
?>