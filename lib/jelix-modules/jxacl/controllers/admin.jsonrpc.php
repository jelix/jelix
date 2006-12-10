<?php
/**
* @package     jelix-modules
* @subpackage  jxacl
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class adminCtrl extends jController {
    /**
    *
    */
    function saveright() {
        $rep = $this->getResponse('jsonrpc');
        $idgroup = $this->param('groupid');
        $rights = $this->param('rightvalues');
        foreach($rights as $r){
            if($r['newvalue'] == 'true'){
                jAclManager::addRight($idgroup, $r['subject'], $r['right'], $r['res']);
            }else{
                jAclManager::removeRight($idgroup, $r['subject'], $r['right'], $r['res']);
            }
        }
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

    function addusertogrp(){
        $rep = $this->getResponse('jsonrpc');
        $id=$this->param('groupid');
        $login=$this->param('user');
        $user= jAuth::getUser($login);
        if($user === null){
            $rep->response = 'UNKNOW_LOGIN';
            return $rep;
        }
        if($id ==''){
           $rep->response = 'NOID';
           return $rep;
        }
        try {
            $id = jAclUserGroup::addUserToGroup($login, $id);
            $rep->response = 'OK';
        }catch(Exception $e){
            $rep->response = 'BADLOGIN';
        }
        return $rep;
    }

    function removeuserfromgrp(){
        $rep = $this->getResponse('jsonrpc');
        $id=$this->param('groupid');
        $login=$this->param('userdel');
        if($id ==''){
            $rep->response = 'NOID';
            return $rep;
        }
        try {
            $id = jAclUserGroup::removeUserFromGroup($login, $id);
            $rep->response = 'OK';
        }catch(Exception $e){
            $rep->response = 'BADLOGIN';
        }
        return $rep;
    }
}
?>
