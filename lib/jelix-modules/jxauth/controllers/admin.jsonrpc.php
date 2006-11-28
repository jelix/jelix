<?php
/**
* @package     jelix-modules
* @subpackage  jxauth
* @version     $Id$
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
    function saveuser() {
        $user = jAuth::getUser($this->param('login'));
        if($user){
            $user->email = $this->param('email');
            jAuth::updateUser($user);
            if($this->param('pwd') != '')
                jAuth::changePassword($this->param('login'), $this->param('pwd'));
        }
        return $this->getResponse('jsonrpc');
    }

    function createUser(){
        $user = jAuth::createUser($this->param('login'),$this->param('pwd'));
        if($user){
            $user->email = $this->param('email');
            jAuth::saveNewUser($user);
        }
        return $this->getResponse('jsonrpc');
    }

}
?>
