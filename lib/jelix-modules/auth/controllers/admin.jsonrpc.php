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
    function saveuser() {
        $user = jAuth::getUser($this->param('login'));
        if($user){
            $user->email = $this->param('email');
            jAuth::updateUser($user);
        }
        return $this->getResponse('jsonrpc');
    }

    function newpwd() {
        jAuth::changePassword($this->param('login'), $this->param('pwd'));

        $rep = $this->getResponse('jsonrpc');
        return $rep;
    }

}
?>
