<?php
/**
* @package     testapp
* @subpackage  testapp module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jstestsCtrl extends jController {

  function jforms() {
      $rep = $this->getResponse('html', true);
      $rep->setXhtmlOutput(false);
      $rep->title = 'Unit tests on jforms';
      $rep->bodyTpl = 'jstest_jforms';
      $rep->addCssLink($GLOBALS['gJConfig']->urlengine['basePath'].'qunit/testsuite.css');
      //$rep->addJsLink('http://code.jquery.com/jquery-latest.js');
      $rep->addJsLink($GLOBALS['gJConfig']->urlengine['jelixWWWPath'].'jquery/jquery.js');
      //$rep->addJsLink($GLOBALS['gJConfig']->urlengine['basePath'].'qunit/testrunner.js');
      $rep->addHeadContent('
                           
                           <script>

  jQuery(document).ready(function(){try{
    
test("a basic test example", function() {
  ok( true, "this test is fine" );
  var value = "hello";
  equals( "hello", value, "We expect value to be hello" );
});

module("Module A");

test("first test within module", function() {
  ok( true, "all pass" );
});

test("second test within module", function() {
  ok( true, "all pass" );
});

module("Module B");

test("some other test", function() {
  expect(1);
  ok( true, "well" );
});

  }catch(e){ alert(e);}});

</script>

                           
                           ');
      
      
      
      return $rep;
  }
}

?>