<?php
/**
* @package    jelix-modules
* @subpackage jxauth
* @author     Laurent Jouanneau
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class loginCtrl extends jController {

    public $pluginParams = array(
      '*'=>array('auth.required'=>false)
    );

    /**
    *
    */
    function in (){

        $l = $this->param('login');
        $p = $this->param('password');

        if (!$l || !$p || !jAuth::login($l, $p)){
            $conf = $GLOBALS['gJCoord']->getPlugin('auth')->config;
            sleep (intval($conf['on_error_sleep']));
            $result='BAD';
        }else{
            $result='OK';
        }

        $rep = $this->getResponse('jsonrpc');
        $rep->response = $result;
        return $rep;
    }

    /**
    *
    */
    function out (){
        jAuth::logout();

        $rep = $this->getResponse('jsonrpc');
        $rep->response = 'LOGOUT';
        return $rep;
    }
}
?>
