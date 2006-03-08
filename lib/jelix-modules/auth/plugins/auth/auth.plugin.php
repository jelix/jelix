<?php
/**
* @package    jelix
* @subpackage auth
* @version    $Id:$
* @author     Croes Gérald
* @contributor  Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Une partie du code est issue d'une version experimentale de la classe
* PluginAuth issue du framework Copix 2.3dev20050901.
* et est sous Copyright 2001-2005 CopixTeam (licence LGPL) http://www.copix.org
* Auteur initial : Croes Gérald
* Contributeur de la version experimentale : Laurent Jouanneau
* Adaptée pour Jelix par Laurent Jouanneau
*/

class AuthPlugin implements jPlugin {
    public $config;

    function __construct($conf){
        $this->config = $conf;
    }

    /**
     * @param    array  $params   plugin parameters for the current action
     * @return null or jSelectorAct  if action should change
     */
    public function beforeProcess ($params){
        $notLogged = false;
        $badip = false;
        $selector = null;

        //Do we check the ip ?
        if ($this->config['secure_with_ip']){
            if (! isset ($_SESSION['JELIX_AUTH_SECURE_WITH_IP'])){
                $_SESSION['JELIX_AUTH_SECURE_WITH_IP'] = $this->_getIpForSecure ();
            }else{
                if (($_SESSION['JELIX_AUTH_SECURE_WITH_IP'] != $this->_getIpForSecure ())){
                    session_destroy ();
                    $selector = new jSelectorAct($this->config['bad_ip_action']);
                    $notLogged = true;
                    $badip = true;
                }
            }
        }

        //Creating the user's object if needed
        if (! isset ($_SESSION['JELIX_USER'])){
            $notLogged = true;
            $_SESSION['JELIX_USER'] = new jUser();
        }else{
            $notLogged = ! jAuth::isConnected();
        }
        if(!$notLogged && $this->config['timeout']){
            if(!isset($_SESSION['JELIX_AUTH_LASTTIME'])
            || (mktime() - $_SESSION['JELIX_AUTH_LASTTIME'] )> ($this->config['timeout'] *60)){
                $notLogged = true;
                jAuth::logout();
            }else{
                $_SESSION['JELIX_AUTH_LASTTIME'] =mktime();
            }
        }

        $needAuth = isset($action->pluginParams['auth.required']) ? ($action->pluginParams['auth.required']==true):$this->config['auth_required'];
        $authok = false;

        if($needAuth){
            if($notLogged){
                if($this->config['on_error'] == 1 || !$GLOBALS['gJCoord']->request->isAllowedResponse('jResponseRedirect')){
                    trigger_error(jLocale::get($this->config['error_message']), E_USER_ERROR);
                    exit;
                }else{
                    if(!$badip){
                        $selector= new jSelectorAct($this->config['on_error_action']);
                    }
                }
            }else{
                $authok= true;
            }
        }else{
          $authok= true;
        }

        return $selector;
    }


    public function beforeOutput(){}

    public function afterProcess (){}

    /**
    * Getting IP adress of the user
    * @return string
    * @access private
    */
    private function _getIpForSecure (){
        //this method is heavily based on the article found on
        // phpbuilder.com, and from the comments on the official phpdoc.
        if (isset ($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']){
            $IP_ADDR = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else if (isset ($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']){
            $IP_ADDR =  $_SERVER['HTTP_CLIENT_IP'];
        }else{
            $IP_ADDR = $_SERVER['REMOTE_ADDR'];
        }

        // get server ip and resolved it
        $FIRE_IP_ADDR = $_SERVER['REMOTE_ADDR'];
        $ip_resolved = gethostbyaddr($FIRE_IP_ADDR);
        // builds server ip infos string
        $FIRE_IP_LITT = ($FIRE_IP_ADDR != $ip_resolved && $ip_resolved) ? $FIRE_IP_ADDR." - ". $ip_resolved : $FIRE_IP_ADDR;
        // builds client ip full infos string
        $toReturn = ($IP_ADDR != $FIRE_IP_ADDR) ? "$IP_ADDR | $FIRE_IP_LITT" : $FIRE_IP_LITT;
        return $toReturn;
    }
}
?>