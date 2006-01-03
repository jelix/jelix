<?php
/**
* @package    jelix
* @subpackage core
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*
* Some parts of this file are took from Copix Framework v2.3dev20050901, CopixCoordinator.class.php,
* copyrighted by CopixTeam and released under GNU Lesser General Public Licence
* author : Gerald Croes, Laurent Jouanneau
* http://www.copix.org
*/



abstract class jRequest {

   /**
    * liste des paramètres en entrée
    * @var array
    */
    public $params;

    public $type;

    public $allowedResponse = array();

    public $defaultResponseType = '';

    /* ========================================= paramètres de l'url courante */
    public $url_script_path;
    public $url_script_name;
    public $url_path_info;

    public $url;

    function __construct(){
        $this->_initUrlDatas();
        $this->_initParams();
    }

    abstract protected function _initParams();

    protected function _initUrlDatas(){
        global $gJConfig;

        $lastslash = strrpos ($_SERVER['SCRIPT_NAME'], '/');
        $this->url_script_path = substr ($_SERVER['SCRIPT_NAME'], 0,$lastslash ).'/';//following is subdir/
        $this->url_script_name = substr ($_SERVER['SCRIPT_NAME'], $lastslash+1);//following is index.php

        if(isset($_SERVER['PATH_INFO'])){
            $pathinfo = $_SERVER['PATH_INFO'];
            if (strpos ($_SERVER['PATH_INFO'], $_SERVER['SCRIPT_NAME']) !== false){
                //under IIS, we may get as PATH_INFO /subdir/index.php/mypath/myaction (which is incorrect)
                $pathinfo = substr ($_SERVER['PATH_INFO'], strlen ($_SERVER['SCRIPT_NAME']));
            }
        }else{
            if($gJConfig->urlengine['use_IIS'] && isset ($_GET[$gJConfig->urlengine['url_IIS_path_key']])){
                $pathinfo = $_GET[$gJConfig->urlengine['url_IIS_path_key']];
                $pathinfo = $gJConfig->urlengine['IIS_stripslashes_path_key'] === true ? stripslashes($pathinfo) : $pathinfo;
            }else{
                //if($_SERVER['PHP_SELF']!= $_SERVER['SCRIPT_NAME']){
                //   $pathinfo = substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME'])-1);
                //}else
                   $pathinfo='';

            }
        }
        $this->url_path_info = $pathinfo;
    }

    public function getParam($name, $defaultValue=null, $useDefaultIfEmpty=false){

        if(isset($this->params[$name])){
            if($useDefaultIfEmpty && trim($this->params[$name]) == ''){
                return $defaultValue;
            }else{
                return $this->params[$name];
            }
        }else{
            return $defaultValue;
        }
    }
    public function allowedResponses(){ return false;}

}


?>