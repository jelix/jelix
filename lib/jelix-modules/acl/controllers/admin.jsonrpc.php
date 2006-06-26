<?php
/**
* @package     jelix-modules
* @subpackage  users
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class CTadmin extends jController {
    /**
    *
    */
    function saveright() {
        $rep = $this->getResponse('jsonrpc');
        if(!jAclManager::setRight($this->param('groupid'), $this->param('subject'),
                            $this->param('rightvalue') , $this->param('ressource'))){
            $rep->response='BAD';
        }else
            $rep->response='OK';
        return $rep;
    }

    function newgrp(){
        $rep = $this->getResponse('jsonrpc');
        $name=$this->param('groupname');
        if($name != ''){
            $id = jAclUserGroup::createGroup($name);
            $rep->response = array('id'=>$id);
        }else{
            $rep->response = 'NONAME';
        }
        return $rep;
    }

    function renamegrp(){
        $rep = $this->getResponse('jsonrpc');
        $id=$this->param('groupid');
        $name=$this->param('newname');
        if($name != '' && $id !=''){
            $id = jAclUserGroup::updateGroup($id,$name);
            $rep->response = 'OK';
        }else{
            $rep->response = 'NONAMEID';
        }
        return $rep;
    }


    function deletegrp(){
        $rep = $this->getResponse('jsonrpc');
        $id=$this->param('groupid');
        if($id !=''){
            $id = jAclUserGroup::removeGroup($id);
            $rep->response = 'OK';
        }else{
            $rep->response = 'NOID';
        }
        return $rep;
    }

}
?>
