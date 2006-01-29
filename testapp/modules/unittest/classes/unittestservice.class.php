<?php

require_once(LIB_PATH.'/simpletest/unit_tester.php');
require_once(LIB_PATH.'/simpletest/reporter.php');

class jHtmlRepReporter extends SimpleReporter {
   protected $_response;
   function __construct($response) {
      $this->SimpleReporter();
      $this->_response = $response;
   }

   function paintHeader($test_name) {
      $this->_response->title=$test_name;
      $this->_response->body->append('MAIN','<h2>'.$test_name.'</h2>');
   }

   function paintFooter($test_name) {
      $colour = ($this->getFailCount() + $this->getExceptionCount() > 0 ? "resultfail" : "resultsuccess");
      $str = "<div class=\"$colour\">";
      $str.= $this->getTestCaseProgress() . "/" . $this->getTestCaseCount();
      $str.= " test cases complete:\n";
      $str.= "<strong>" . $this->getPassCount() . "</strong> passes, ";
      $str.= "<strong>" . $this->getFailCount() . "</strong> fails and ";
      $str.= "<strong>" . $this->getExceptionCount() . "</strong> exceptions.";
      $str.= "</div>\n";
      $this->_response->body->append('MAIN',$str);
   }

   function paintFail($message) {
      parent::paintFail($message);

      $str = "<span class=\"fail\">Fail</span>: ";
      $breadcrumb = $this->getTestList();
      array_shift($breadcrumb);
      $str.= implode(" -&gt; ", $breadcrumb);
      $str.= " -&gt; " . $this->_htmlEntities($message) . "<br />\n";
      $this->_response->body->append('MAIN',$str);
   }

   function paintException($message) {
      parent::paintException($message);
      $str=  "<span class=\"fail\">Exception</span>: ";
      $breadcrumb = $this->getTestList();
      array_shift($breadcrumb);
      $str.=  implode(" -&gt; ", $breadcrumb);
      $str.=  " -&gt; <strong>" . $this->_htmlEntities($message) . "</strong><br />\n";
      $this->_response->body->append('MAIN',$str);
   }

   function paintMessage($message) {
      $this->_response->body->append('MAIN','<p>'.$message.'</p>');
   }

   function paintFormattedMessage($message) {
      $this->_response->body->append('MAIN','<pre>' . $this->_htmlEntities($message) . '</pre>');
   }

   function _htmlEntities($message) {
      return htmlentities($message, ENT_COMPAT, $this->_character_set);
   }
}


class UnitTestService {
   protected $_rep;
   function init($rep){
      $this->_rep = $rep;
   }

   function eventsTest(){

      $test = jClasses::create("utevents");
      $test->run(new jHtmlRepReporter($this->_rep));
   }
      /*$test = &new GroupTest('All tests');
      $test->addTestFile('log_test.php');
      $test->run(new HtmlReporter());
      */
}
?>