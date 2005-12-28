<?php
/**
* @package     myapp
* @subpackage  myappmodule
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class AGMain extends jActionGroup {

  function getDefault(){

      if($this->_get('output') == 'text'){
         $rep = $this->_getResponse('hellotext');
         $rep->content = 'Hello World !';
      }else{

         $rep = $this->_getResponse('hello');
         $rep->title = 'Hello From Jelix !';
         $rep->bodyTpl = 'myapp~hello';
         $rep->body->assign('person', $this->_get('person','You'));

      }
      return $rep;
   }



   function getTestDao(){
    if( $id=$this->_get('newid')){
        $dao = jDAO::create('config');
        $rec = jDAO::createRecord('config');

        $rec->ckey = $id;
        $rec->cvalue=$this->_get('newvalue','');
        $dao->insert($rec);
    }

    $rep = $this->_getResponse('dao');
    $rep->title = 'This is a DAO Test';
    $rep->bodyTpl = 'myapp~main';
    $rep->body->assign('person','Laurent');
    $rep->body->assignZone('MAIN', 'test');

      return $rep;
   }


}

?>