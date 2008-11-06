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

        $this->urlScript = $gJConfig->urlengine['urlScript'];
        $this->urlScriptPath = $gJConfig->urlengine['urlScriptPath'];
        $this->urlScriptName = $gJConfig->urlengine['urlScriptName'];

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

        if ($gJConfig->isWindows && $pathinfo && strpos($pathinfo, $this->urlScript) !== false){
            //under IIS, we may get  /subdir/index.php/mypath/myaction as PATH_INFO, so we fix it
            $pathinfo = substr ($pathinfo, strlen ($this->urlScript));
        }

        $this->urlPathInfo = $pathinfo;
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
     * @param string $respclass the name of a response class
     */
    public function isAllowedResponse($respclass){
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

        if(!class_exists($respclass,false))
            require($path);

        $response = new $respclass();
        $gJCoord->response = $response;

        return $response;
    }
}

