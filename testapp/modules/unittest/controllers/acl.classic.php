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

class aclCtrl extends jController {

   function index() {
      $rep = $this->getResponse('unittest');
      $rep->title = 'test unitaires sur jAcl';
      $ut = jClasses::create("unittestservice");
      $ut->init($rep);
      $ut->jaclTest();
      return $rep;
   }

   function manager() {
      $rep = $this->getResponse('unittest');
      $rep->title = 'test unitaires sur jAclManager';
      $ut = jClasses::create("unittestservice");
      $ut->init($rep);
      $ut->jaclmanagerTest();
      return $rep;
   }


   function usergroup() {
      $rep = $this->getResponse('unittest');
      $rep->title = 'test unitaires sur jAclUserGroup';
      $ut = jClasses::create("unittestservice");
      $ut->init($rep);
      $ut->jaclusergroupTest();
      return $rep;
   }



}
?>