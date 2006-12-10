<?php
/**
* @package    jelix
* @subpackage jxauth
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


class loginCtrl extends jController {

    public $pluginParams = array(
      '*'=>array('auth.required'=>false)
    );

    /**
    *
    */
    function in (){
        $conf = $GLOBALS['gJCoord']->getPlugin ('auth')->config;
        $url_return = '/';
        if (!($conf['enable_after_login_override'] && $url_return= $this->param('auth_url_return'))){
            $url_return =  jUrl::get($conf['after_login']);
        }

        if (!jAuth::login($this->param('login'), $this->param('password'))){
            sleep (intval($conf['on_error_sleep']));
            $url_return = jUrl::get('jxauth~login_form',array ('login'=>$this->param('login'), 'failed'=>1));
        }

        $rep = $this->getResponse('redirectUrl');
        $rep->url = $url_return;
        return $rep;
    }

    /**
    *
    */
    function out (){
        jAuth::logout();
        $conf = $GLOBALS['gJCoord']->getPlugin ('auth')->config;

        if (!($conf['enable_after_logout_override'] && $url_return= $this->param('auth_url_return'))){
            $url_return =  jUrl::get($conf['after_logout']);
        }
        $rep = $this->getResponse('redirectUrl');
        $rep->url = $url_return;
        return $rep;
    }

    /**
    * Shows the login form
    */
    function form() {
        $rep = $this->getResponse('html');

        $rep->title =  jLocale::get ('auth.titlePage.login');
        $rep->bodyTpl = 'jxauth~index';
        $rep->body->assignZone ('MAIN', 'jxauth~loginform', array ('login'=>$this->param('login'), 'failed'=>$this->param('failed')));

        return $rep;
    }
}
?>
