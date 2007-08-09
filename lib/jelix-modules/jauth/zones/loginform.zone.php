<?php

/**
* @package    jelix-modules
* @subpackage jauth
* @author     Laurent Jouanneau
* @contributor Antoine Detante
* @copyright  2005-2006 Laurent Jouanneau, 2007 Antoine Detante
* @link       http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

class LoginFormZone extends jZone {
   protected $_tplname='login.form';

    protected function _prepareTpl(){

        $this->_tpl->assign ('login', $this->getParam('login'));
        $this->_tpl->assign ('failed',  $this->getParam('failed'));

        $this->_tpl->assign ('user', jAuth::getUserSession());
        $this->_tpl->assign ('isLogged', jAuth::isConnected());
        $this->_tpl->assign ('showRememberMe', $this->getParam('showRememberMe'));
    }
}
?>
