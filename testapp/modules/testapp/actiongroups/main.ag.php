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

class AGMain extends jActionGroup {

  function getDefault(){

      if($this->param('output') == 'text'){
         $rep = $this->getResponse('hellotext');
         $rep->content = 'Hello World !';
      }else{

         $rep = $this->getResponse('hello');
         $rep->title = 'Hello From Jelix !';
         $rep->bodyTpl = 'testapp~hello';
         $rep->body->assign('person', $this->param('person','You'));
         $rep->body->assign('value','name');
      }

      return $rep;
   }



   function getTestDao(){
    if( $id=$this->param('newid')){
        $dao = jDAO::get('config');
        $rec = jDAO::createRecord('config');

        $rec->ckey = $id;
        $rec->cvalue=$this->param('newvalue','');
        $dao->insert($rec);
    }

    $rep = $this->getResponse('dao');
    $rep->title = 'This is a DAO Test';
    $rep->bodyTpl = 'testapp~main';
    $rep->body->assign('person','Laurent');
    $rep->body->assignZone('MAIN', 'test');

      return $rep;
   }


}

?>