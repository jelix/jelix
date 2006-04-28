<?php
/**
* @package    jelix
* @subpackage utils
* @version    $Id:$
* @author     Laurent Jouanneau
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class CTLogin extends jController {

    public $pluginParams = array(
      '*'=>array('auth.required'=>false)
    );

    /**
    *
    */
    function in (){
        $conf = $GLOBALS['gJCoord']->getPlugin('auth')->config;

        if (!jAuth::login($this->param('login'), $this->param('password'))){
            sleep (intval($conf['on_error_sleep']));
            $result='OK';
        }else{
            $result='BAD';
        }

        $rep = $this->getResponse('jsonrpc');
        $rep->content = $result;
        return $rep;
    }

    /**
    *
    */
    function out (){
        jAuth::logout();

        $rep = $this->getResponse('jsonrpc');
        $rep->content = 'OK';
        return $rep;
    }
}
?>
