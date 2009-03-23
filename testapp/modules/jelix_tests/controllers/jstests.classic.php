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
      $rep->addJsLink($GLOBALS['gJConfig']->urlengine['jelixWWWPath'].'jquery/jquery.js');
      $rep->addJsLink($GLOBALS['gJConfig']->urlengine['jelixWWWPath'].'jquery/include/jquery.include.js');
      $rep->addJsLink($GLOBALS['gJConfig']->urlengine['jelixWWWPath'].'js/jforms_jquery.js');
      $rep->addJsLink($GLOBALS['gJConfig']->urlengine['jelixWWWPath'].'wymeditor/jquery.wymeditor.js');
      $rep->addJsLink($GLOBALS['gJConfig']->urlengine['jelixWWWPath'].'wymeditor/config/default.js');
      $rep->addJsLink($GLOBALS['gJConfig']->urlengine['jelixWWWPath'].'js/jforms/datepickers/default/init.js');

      $rep->addJsLink($GLOBALS['gJConfig']->urlengine['basePath'].'qunit/testrunner.js');
      
      return $rep;
  }
  
  function jsonrpc() {
      $rep = $this->getResponse('html', true);
      $rep->setXhtmlOutput(false);
      $rep->title = 'Unit tests for jsonrpc';
      $rep->bodyTpl = 'jstest_jsonrpc';
      $rep->addCssLink($GLOBALS['gJConfig']->urlengine['basePath'].'qunit/testsuite.css');
      $rep->addJsLink($GLOBALS['gJConfig']->urlengine['jelixWWWPath'].'jquery/jquery.js');
      $rep->addJsLink($GLOBALS['gJConfig']->urlengine['basePath'].'qunit/testrunner.js');
      $rep->addJsLink($GLOBALS['gJConfig']->urlengine['jelixWWWPath'].'js/json.js');
      return $rep;
  }
  
  
}

?>