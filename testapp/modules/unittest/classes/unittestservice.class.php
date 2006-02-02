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

require_once(LIB_PATH.'/simpletest/unit_tester.php');
require_once(dirname(__FILE__).'/jhtmlrespreporter.class.php');



class UnitTestService {
   protected $_rep;
   function init($rep){
      $this->_rep = $rep;
   }

   function eventsTest(){

      $test = jClasses::create("utevents");
      $test->run(new jHtmlRespReporter($this->_rep));
   }
      /*$test = &new GroupTest('All tests');
      $test->addTestFile('log_test.php');
      $test->run(new HtmlReporter());
      */

   function urlsTest(){
      $test = jClasses::create("utcreateurls");
      $test->run(new jHtmlRespReporter($this->_rep));
   }
}
?>