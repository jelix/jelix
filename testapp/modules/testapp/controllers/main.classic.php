<?php
/**
* @package     testapp
* @subpackage  testapp module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class mainCtrl extends jController {

  function index(){

      $rep = $this->getResponse('html');
      $rep->title = 'Homepage of TestApp';
      $rep->body->assign('page_title','Test App');
      $rep->body->assign('MAIN','<p>Welcome on this application to test Jelix</p>');
      return $rep;
   }

   function hello(){

      if($this->param('output') == 'text'){
         $rep = $this->getResponse('text', true);
         $rep->content = 'Hello World !';
      }else{
         $rep = $this->getResponse('html',true);
         $rep->title = 'Hello From Jelix !';
         $rep->bodyTpl = 'testapp~hello';
         $rep->body->assign('person', $this->param('person','You'));
         $rep->body->assign('value','name');
      }

      return $rep;
   }

   function hello2(){

      $rep = $this->getResponse('html',true);
      $rep->title = 'Hello 2 From Jelix !';
      $rep->bodyTpl = 'testapp~hello2';

      return $rep;
   }

   function hello3(){

      $rep = $this->getResponse('html',true);
      $rep->title = 'Hello 3 From Jelix !';
      $rep->bodyTpl = 'testapp~hello3';

      return $rep;
   }

   function testdao(){

    if ($id = $this->param('newid')) {
        $dao = jDao::get('config');
        $rec = $dao->createRecord();

        $rec->ckey = $id;
        $rec->cvalue=$this->param('newvalue','');
        $dao->insert($rec);
    }

    $rep = $this->getResponse('html');
    $rep->title = 'This is a DAO Test';
    $rep->bodyTpl = 'testapp~main';
    $rep->body->assign('person','Laurent');
    $rep->body->assignZone('MAIN', 'test');

      return $rep;
   }

    function resetdao(){
        $db = jDb::getConnection();
        $db->exec('delete from myconfig');
        
        $rep = $this->getResponse('html');
        $rep->title = 'Empty table';
        $rep->bodyTpl = 'testapp~main';
        $rep->body->assign('MAIN', 'reset done');
    
        return $rep;
    }
   
    function generateerror() {
        $rep = $this->getResponse('html');
        throw new Exception("here is an error");
    }

    function generatewarning(){

      $rep = $this->getResponse('html');
      $rep->title = 'This is a test for the debug bar';
      $tpl = new jTpl();

      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("This is a simple notice", E_USER_NOTICE);
      trigger_error("an other notice!", E_USER_NOTICE);
      trigger_error("which notice!", E_USER_NOTICE);
      trigger_error("notice the return", E_USER_NOTICE);
      trigger_error("damned, a notice!", E_USER_NOTICE);
      trigger_error("This is a simple warning", E_USER_WARNING);

      $rep->body->assign('MAIN', $tpl->fetch('loremipsum'));

      return $rep;
    }


    function testminify() {
        $config = jApp::config();
        $config->jResponseHtml['plugins'] = 'minify';
        $config->jResponseHtml['minifyCSS'] = true;
        $config->jResponseHtml['minifyJS'] = true;

        $resp = $this->getResponse('html', true);
        $resp->bodyTpl = 'testapp~testminify';
        $resp->addJSLink (jApp::urlBasePath().'testminify/js/s1.js');
        $resp->addJSLink (jApp::urlBasePath().'testminify/js/s2.js');
        $resp->addCSSLink(jApp::urlBasePath().'testminify/css/style1.css');
        $resp->addCSSLink(jApp::urlBasePath().'testminify/css/style2.css');
        return $resp;
    }

  function sitemap() {
     $resp = $this->getResponse('sitemap');
     $resp->importFromUrlsXml();
     return $resp;
  }
  
  function installchecker() {
      $rep = $this->getResponse('html');
      $rep->body->assignZone('MAIN', 'jelix~check_install');
      return $rep;
  }
}
