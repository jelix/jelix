<?php
/**
* @package     jelix-modules
* @subpackage  jauth
* @author      Laurent Jouanneau
* @contributor Antoine Detante
* @copyright   2005-2007 Laurent Jouanneau, 2007 Antoine Detante
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/


class loginCtrl extends jController {

    public $pluginParams = array(
      '*'=>array('auth.required'=>false)
    );

    /**
    *
    */
    function in (){
        $conf = $GLOBALS['gJCoord']->getPlugin('auth')->config;
        $url_return = '/';

        if ($conf['after_login'] == '')
            throw new jException ('jauth~autherror.no.auth_login');

        if ($conf['after_logout'] == '')
            throw new jException ('jauth~autherror.no.auth_logout');

        if (!($conf['enable_after_login_override'] && $url_return= $this->param('auth_url_return'))){
            $url_return =  jUrl::get($conf['after_login']);
        }

        if (!jAuth::login($this->param('login'), $this->param('password'), $this->param('rememberMe'))){
            sleep (intval($conf['on_error_sleep']));
            $url_return = jUrl::get($conf['after_logout'],array ('login'=>$this->param('login'), 'failed'=>1));
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

        if ($conf['after_logout'] == '')
            throw new jException ('jauth~autherror.no.auth_logout');

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
        $rep->bodyTpl = 'jauth~index';
        $rep->body->assignZone ('MAIN', 'jauth~loginform', array ('login'=>$this->param('login'), 'failed'=>$this->param('failed'), 'showRememberMe'=>jAuth::isPersistant()));
        return $rep;
    }
}
?>
