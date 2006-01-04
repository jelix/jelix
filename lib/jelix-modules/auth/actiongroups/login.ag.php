<?php
/**
* @package    jelix
* @subpackage utils
* @version    $Id:$
* @author     Croes Gérald,  Bertrand Yan
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Classe orginellement issue du framework Copix 2.3dev20050901. http://www.copix.org (CopixZone)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Bertrand Yan
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/


class AGLogin extends jActionGroup {
    /**
    *
    */
    function doLogin (){
        $conf = $GLOBALS['gJCoord']->getPlugin ('auth')->config;

        if (!($conf['enable_after_login_override'] && $url_return= $this->_get('auth_url_return'))){
            $url_return =  jUrl::get($conf['after_login']);
        }

        if (!jAuth::login($this->_get('login'), $this->_get('password'))){
            sleep (intval($conf['on_error_sleep']));
            $url_return = jUrl::get('auth~loginform',array ('login'=>$this->_get('login'), 'failed'=>1));
        }

        $rep = $this->_getResponse('next');
        $rep->url = $url_return;
        return $rep;
    }

    /**
    *
    */
    function doLogout (){
        jAuth::logout();
        $conf = $GLOBALS['gJCoord']->getPlugin ('auth')->config;

        if (!($conf['enable_after_login_override'] && $url_return= $this->_get('auth_url_return'))){
            $url_return =  jUrl::get($conf['after_logout']);
        }
        $rep = $this->_getResponse('next');
        $rep->url = $url_return;
        return $rep;
    }

    /**
    * Shows the login form
    */
    function getLoginForm() {
        $rep = $this->_getResponse('loginform');

        $rep->title =  jLocale::get ('auth.titlePage.login');
        $rep->body->assignZone ('MAIN', 'auth~loginForm', array ('login'=>$this->_get('login'), 'failed'=>$this->_get('failed')));

        return $rep;
    }
}
?>