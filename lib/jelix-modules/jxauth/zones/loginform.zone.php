<?php

/**
* @package    jelix-modules
* @subpackage jxauth
* @version    $Id:$
* @author     Croes Gérald, Bertrand Yan
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link       http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Classe orginellement issue du framework Copix 2.3dev20050901. http://www.copix.org (ZoneLoginForm)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Bertrand Yan
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

class LoginFormZone extends jZone {
   protected $_tplname='login.form';


    protected function _prepareTpl(){

        $this->_tpl->assign ('login', $this->getParam('login'));
        $this->_tpl->assign ('failed',  $this->getParam('failed'));

        $this->_tpl->assign ('user', jAuth::getUserSession());
        $this->_tpl->assign ('isLogged', jAuth::isConnected());
        $this->_tpl->assign ('showLostPassword', false);
        $this->_tpl->assign ('showRememberMe', false);
    }
}
?>
