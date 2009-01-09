<?php
/**
* @package   admin
* @subpackage jauthdb_admin
* @author    Laurent Jouanneau
* @copyright 2009 Laurent Jouanneau
* @link      http://jelix.org
* @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public Licence
*/

class passwordCtrl extends jController {
   
    protected function _checkRights() {
        $id = $this->param('id');
        
        if ($id !== null && ($id == jAuth::getUserSession()->login || jAcl2::check('auth.user.change.password')))
            return null;
        
        $rep = $this->getResponse('html');
        $tpl = new jTpl();
        $rep->body->assign('MAIN', $tpl->fetch('jelix~403.html'));
        $rep->setHttpStatus('403', 'Forbidden');
        return $rep;
    }


    function index(){
        $id = $this->param('id');
        if($id === null){
            $rep = $this->getResponse('redirect');
            $rep->action = 'master_admin~default:index';
            return $rep;
        }
        if($rep = $this->_checkRights()){
            return $rep;
        }
        $rep = $this->getResponse('html');

        $tpl = new jTpl();
        $tpl->assign('id', $id);
        $tpl->assign('randomPwd', jAuth::getRandomPassword());
        $rep->body->assign('MAIN', $tpl->fetch('password_change'));
        return $rep;
    }

    /**
     * 
     */
    function update(){
        if($rep = $this->_checkRights()){
            return $rep;
        }

        $id = $this->param('id');
        $pwd = $this->param('pwd');
        $pwdconf = $this->param('pwd_confirm');
        $rep = $this->getResponse('redirect');

        /*if (jAuth::verifyPassword(jAuth::getUserSession()->login, $pwd) == false) {
            jMessage::add(jLocale::get('crud.message.delete.invalid.pwd'), 'error');
            $rep->action = 'default:confirmdelete';
            $rep->params['id'] = $id;
            return $rep;
        }*/
        
        if (trim($pwd) == '' || $pwd != $pwdconf) {
            jMessage::add(jLocale::get('crud.message.bad.password'), 'error');
            $rep->action = 'password:index';
            $rep->params['id'] = $id;
            return $rep;
        }
        
        if(jAuth::changePassword($id, $pwd)) {
            jMessage::add(jLocale::get('crud.message.change.password.ok', $id), 'notice');
            $rep->action = 'default:view';
            $rep->params['id'] = $id;
            return $rep;
        }
        else{
            jMessage::add(jLocale::get('crud.message.change.password.notok'), 'error');
            $rep->action = 'password:index';
            $rep->params['id'] = $id;
        }
        return $rep;
    }

}

