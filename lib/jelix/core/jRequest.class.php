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
*/


/**
 * base class for object which retrieve all parameters of an http request. The
 * process depends on the type of request (ex: xmlrpc..)
 *
 * @copyright line codes which set the url_* properties are took from Copix Framework v2.3dev20050901, CopixCoordinator.class.php,
 * copyrighted by CopixTeam and released under GNU Lesser General Public Licence
 * author : Gerald Croes, Laurent Jouanneau
 * http://www.copix.org
 * @package  jelix
 * @subpackage core
 */
abstract class jRequest {

   /**
    * request parameters
    * could set from $_GET, $_POST, or from data processing of $HTTP_RAW_POST_DATA
    * @var array
    */
    public $params;

    /**
     * the request type code
     * @var string
     */
    public $type;

    /**
     * the type of the default response
     * @var string
     */
    public $defaultResponseType = '';

    /**
     * the path to the entry point in the url
     * @var string
     */
    public $url_script_path;

    /**
     * the name of the entry point
     * @var string
     */
    public $url_script_name;

    /**
     * the pathinfo part of the url
     * @var string
     */
    public $url_path_info;

    function __construct(){  }

    /**
     * initialize the request : analyse of http request etc..
     */
    public function init(){
        $this->_initUrlDatas();
        $this->_initParams();
    }

    /**
     * analyse the http request and set the params property
     */
    abstract protected function _initParams();

    /**
     * inits the url_* properties
     */
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
            if($gJConfig->urlengine['useIIS'] && isset ($_GET[$gJConfig->urlengine['IISPathKey']])){
                $pathinfo = $_GET[$gJConfig->urlengine['IISPathKey']];
                $pathinfo = $gJConfig->urlengine['IISStripslashesPathKey'] === true ? stripslashes($pathinfo) : $pathinfo;
            }else{
                //if($_SERVER['PHP_SELF']!= $_SERVER['SCRIPT_NAME']){
                //   $pathinfo = substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME'])-1);
                //}else
                   $pathinfo='';

            }
        }
        $this->url_path_info = $pathinfo;
    }

    /**
    * Gets the value of a request parameter. If not defined, gets its default value.
    * @param string  $name           the name of the request parameter
    * @param mixed   $defaultValue   the default returned value if the parameter doesn't exists
    * @param boolean $useDefaultIfEmpty true: says to return the default value the value is ""
    * @return mixed the request parameter value
    */
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

    /**
     * return a list of class name of allowed response corresponding to the request
     * @return array the list, or false which means everything
     * @see jRequest::getResponse()
     */
    public function allowedResponses(){ return false;}

    /**
     * @param string $respclass the name of a response class
     */
    public function isAllowedResponse($respclass){
        if($ar=$this->allowedResponses()){
            return in_array($respclass, $ar);
        }else
            return true;
    }

    /**
     * get a response object.
     * @param string $name the name of the response type (ex: "html")
     * @param boolean $useOriginal true:don't use the response object redefined by the application
     * @return jResponse the response object
     */
    public function getResponse($type='', $useOriginal = false){
        global $gJCoord, $gJConfig;
        if($type == ''){
            $type = $this->defaultResponseType;
        }

        if($useOriginal){
            if(!isset($gJConfig->_coreResponses[$type])){
               throw new jException('jelix~errors.ad.response.type.unknow',array($gJCoord->action->resource,$type,$gJCoord->action->getPath()));
            }
            $respclass = $gJConfig->_coreResponses[$type];
        }else{
            if(!isset($gJConfig->responses[$type])){
               throw new jException('jelix~errors.ad.response.type.unknow',array($gJCoord->action->resource,$type,$gJCoord->action->getPath()));
            }
            $respclass = $gJConfig->responses[$type];
        }
        if(file_exists($path=JELIX_LIB_RESPONSE_PATH.$respclass.'.class.php')){
           require_once ($path);
        }elseif(file_exists($path=JELIX_APP_PATH.'responses/'.$respclass.'.class.php')){
           require_once ($path);
        }else{
           throw new jException('jelix~errors.ad.response.not.loaded',array($gJCoord->action->resource,$type,$gJCoord->action->getPath()));
        }

        if(!$this->isAllowedResponse($respclass)){
           throw new jException('jelix~errors.ad.response.type.notallowed',array($gJCoord->action->resource,$type,$gJCoord->action->getPath()));
        }

        $response = new $respclass();
        $gJCoord->response= $response;

        return $response;
    }
}


?>