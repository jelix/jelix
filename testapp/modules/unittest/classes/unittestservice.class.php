<?php
/**
* @package     testapp
* @subpackage  unittest module
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

   function urlsCreateTest(){
      $test = jClasses::create("utcreateurls");
      $test->run(new jHtmlRespReporter($this->_rep));
   }
   function urlsParseTest(){
      $test = jClasses::create("utparseurls");
      $test->run(new jHtmlRespReporter($this->_rep));
   }

   function selectorActTest(){
      $test = jClasses::create("utselectoract");
      $test->run(new jHtmlRespReporter($this->_rep));
   }
   function selectorModTest(){
      $test = jClasses::create("utselectormod");
      $test->run(new jHtmlRespReporter($this->_rep));
   }

   function daoConditionsTest(){
      $test = jClasses::create("utdao_conditions");
      $test->run(new jHtmlRespReporter($this->_rep));
   }


   function daoParserTest(){
      $test = jClasses::create("utdao_parser");
      $test->run(new jHtmlRespReporter($this->_rep));
   }

   function daoParser2Test(){
      $test = jClasses::create("utdao_parser2");
      $test->run(new jHtmlRespReporter($this->_rep));
   }

   function daoTest(){
      $test = jClasses::create("utdao");
      $rep = new jHtmlRespReporter($this->_rep);
      $test->run($rep);
      $test = jClasses::create("utdaopdo");
      $test->run($rep);
      $rep->makeDry(false);
/*      $test = jClasses::create("utdao_conditions");
      $test->run($rep);
      $test = jClasses::create("utdao_parser");
      $test->run($rep);
      $test = jClasses::create("utdao_parser2");
      $test->run($rep);*/
   }

   function simpleTestTest(){
      $test = jClasses::create("utsimpletest");
      $test->run(new jHtmlRespReporter($this->_rep));
   }

   function filterTest(){
      $test = jClasses::create("utfilter");
      $test->run(new jHtmlRespReporter($this->_rep));
   }

   function localepropTest(){
      $test = jClasses::create("utjlocale");
      $test->run(new jHtmlRespReporter($this->_rep));
   }

   function jtplExprTest(){
      $test = jClasses::create("utjtplexpr");
      $test->run(new jHtmlRespReporter($this->_rep));
   }


   function jaclTest(){
      $test = jClasses::create("utjacl");
      $test->run(new jHtmlRespReporter($this->_rep));
   }

   function jaclmanagerTest(){
      $test = jClasses::create("utjaclmanager");
      $test->run(new jHtmlRespReporter($this->_rep));
   }

   function jaclusergroupTest(){
      $test = jClasses::create("utjaclusergroup");
      $test->run(new jHtmlRespReporter($this->_rep));
   }

   function jdbTest(){
      $rep =new jHtmlRespReporter($this->_rep);
      $test = jClasses::create("utjdb_profile");
      $test->run($rep);
      $test = jClasses::create("utjdb");
      $test->run($rep);
      $test = jClasses::create("utjdb_pdo");
      $test->run($rep);
   }
}
?>