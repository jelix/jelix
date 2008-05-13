<?php
/**
* @package    jelix
* @subpackage core
* @author     Laurent Jouanneau
* @contributor
* @copyright  2005-2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


/**
 * base class for object which retrieve all parameters of an http request. The
 * process depends on the type of request (ex: xmlrpc..)
 *
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
     * the path of the entry point in the url
     * if the url is /foo/index.php/bar, its value is /foo/
     * @var string
     */
    public $urlScriptPath;

    /**
     * the name of the entry point
     * if the url is /foo/index.php/bar, its value is index.php
     * @var string
     */
    public $urlScriptName;

    /**
     * the path to the entry point in the url
     * if the url is /foo/index.php/bar, its value is /foo/index.php
     * @var string
     */
    public $urlScript;

    /**
     * the pathinfo part of the url
     * if the url is /foo/index.php/bar, its value is /bar
     * @var string
     */
    public $urlPathInfo;


    /**
     * @var string
     * @deprecated see $urlScriptPath
     */
    public $url_script_path;

    /**
     * @var string
     * @deprecated see $urlScriptName
     */
    public $url_script_name;

    /**
     * @var string
     * @deprecated see $urlPathInfo
     */
    public $url_path_info;



    function __construct(){  }

    /**
     * initialize the request : analyse of http request etc..
     */
    public function init(){
        $this->_initUrlData();
        $this->_initParams();
    }

    /**
     * analyse the http request and sets the params property
     */
    abstract protected function _initParams();

    /**
     * init the url* properties
     */
    protected function _initUrlData(){
        global $gJConfig;

        if (isset($_SERVER[$gJConfig->urlengine['scriptNameServerVariable']]))
            $this->urlScript = $_SERVER[$gJConfig->urlengine['scriptNameServerVariable']];
        else
            $this->urlScript = $_SERVER['SCRIPT_NAME'];

        $lastslash = strrpos ($this->urlScript, '/');
        $this->url_script_path = $this->urlScriptPath = substr ($this->urlScript, 0, $lastslash ).'/';

        if($gJConfig->urlengine['basePath'] == ''){ // for beginners or simple site, we "guess" the base path
            $gJConfig->urlengine['basePath'] = $this->urlScriptPath;
            if($gJConfig->urlengine['jelixWWWPath']{0} != '/')
                $gJConfig->urlengine['jelixWWWPath'] = $this->urlScriptPath.$gJConfig->urlengine['jelixWWWPath'];
        }else if(strpos($this->urlScriptPath,$gJConfig->urlengine['basePath']) !== 0){
            throw new Exception('Jelix Error: basePath ('.$gJConfig->urlengine['basePath'].') in config file doesn\'t correspond to current base path. You should setup it to '.$this->urlScriptPath);
        }

        $this->url_script_name = $this->urlScriptName = substr ($this->urlScript, $lastslash+1);

        $piiqp = $gJConfig->urlengine['pathInfoInQueryParameter'];
        if ($piiqp) {
            if (isset($_GET[$piiqp])) {
                $pathinfo = $_GET[$piiqp];
                unset($_GET[$piiqp]);
            } else
                $pathinfo = '';
        } else if(isset($_SERVER['PATH_INFO'])){
            $pathinfo = $_SERVER['PATH_INFO'];
        } else if(isset($_SERVER['ORIG_PATH_INFO'])){
            $pathinfo = $_SERVER['ORIG_PATH_INFO'];
        } else
            $pathinfo = '';

        if($pathinfo == $this->urlScript) {
            //when php is used as cgi and if there isn't pathinfo in the url
            $pathinfo = '';
        }

        if ($gJConfig->isWindows && $pathinfo && strpos ($pathinfo, $this->urlScript) !== false){
            //under IIS, we may get  /subdir/index.php/mypath/myaction as PATH_INFO, so we fix it
            $pathinfo = substr ($pathinfo, strlen ($this->urlScript));
        }

        $this->url_path_info = $this->urlPathInfo = $pathinfo;
    }

    /**
    * Gets the value of a request parameter. If not defined, gets its default value.
    * @param string  $name           the name of the request parameter
    * @param mixed   $defaultValue   the default returned value if the parameter doesn't exists
    * @param boolean $useDefaultIfEmpty true: says to return the default value if the parameter value is ""
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
            $path = $gJConfig->_coreResponses[$type.'.path'];
        }else{
            if(!isset($gJConfig->responses[$type])){
                throw new jException('jelix~errors.ad.response.type.unknow',array($gJCoord->action->resource,$type,$gJCoord->action->getPath()));
            }
            $respclass = $gJConfig->responses[$type];
            $path = $gJConfig->responses[$type.'.path'];
        }

        if(!$this->isAllowedResponse($respclass)){
            throw new jException('jelix~errors.ad.response.type.notallowed',array($gJCoord->action->resource,$type,$gJCoord->action->getPath()));
        }

        require_once ($path);

        $response = new $respclass();
        $gJCoord->response= $response;

        return $response;
    }
}

