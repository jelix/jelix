<?php
/**
* @package    testapp
* @subpackage unittest
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


require_once(LIB_PATH.'/simpletest/reporter.php');

class jHtmlRespReporter extends SimpleReporter {
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

   /*function paintPass($message) {
      parent::paintPass($message);

      $str = "<span class=\"pass\">Pass</span>: ";
      $breadcrumb = $this->getTestList();
      array_shift($breadcrumb);
      $str.= implode(" -&gt; ", $breadcrumb);
      $str.= " -&gt; " . $this->_htmlEntities($message) . "<br />\n";
      $this->_response->body->append('MAIN',$str);
   }*/

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
      global $gJConfig;
      return htmlentities($message, ENT_COMPAT, $gJConfig->defaultCharset);
   }
}

?>