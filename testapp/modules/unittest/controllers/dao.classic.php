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

class CTDao extends jController {

   function parser() {
      $rep = $this->getResponse('unittest');
      $rep->title = 'test unitaires sur parser jDao';
      $ut = jClasses::create("unittestservice");
      $ut->init($rep);
      $ut->daoParserTest();
      return $rep;
   }

   function parser2() {
      $rep = $this->getResponse('unittest');
      $rep->title = 'test unitaires sur parser jDao (2)';
      $ut = jClasses::create("unittestservice");
      $ut->init($rep);
      $ut->daoParser2Test();
      return $rep;
   }

   function conditions() {
      $rep = $this->getResponse('unittest');
      $rep->title = 'test unitaires sur jDaoConditions';
      $ut = jClasses::create("unittestservice");
      $ut->init($rep);
      $ut->daoConditionsTest();
      return $rep;
   }

}
?>