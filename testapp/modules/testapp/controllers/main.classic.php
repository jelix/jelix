<?php
/**
* @package     testapp
* @subpackage  testapp module
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class CTMain extends jController {

  function index(){

      $rep = $this->getResponse('html');
      $rep->title = 'Accueil de TestApp';
      $rep->body->assign('page_title','Test App');
      $rep->body->assign('MAIN','<p>Bienvenue sur cette application de test</p>');
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

   function testdao(){
    $dao = jDao::get('testnews');

    if( $id=$this->param('newid')){
        $dao = jDao::get('config');
        $rec = jDao::createRecord('config');

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


}

?>