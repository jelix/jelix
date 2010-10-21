<?php
/**
* @package    jelix
* @subpackage core
* @author     Laurent Jouanneau
* @contributor Yannick Le Guédart
* @copyright  2005-2010 Laurent Jouanneau, 2010 Yannick Le Guédart
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
                throw new jException('jelix~errors.ad.response.type.unknown',array($gJCoord->action->resource,$type,$gJCoord->action->getPath()));
            }
            $respclass = $gJConfig->_coreResponses[$type];
            $path = $gJConfig->_coreResponses[$type.'.path'];
        }else{
            if(!isset($gJConfig->responses[$type])){
                throw new jException('jelix~errors.ad.response.type.unknown',array($gJCoord->action->resource,$type,$gJCoord->action->getPath()));
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
    
    /**
     * return the ip address of the user
     * @return string the ip
     */
    function getIP() {
        if (isset ($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']){
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else if (isset ($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']){
            return  $_SERVER['HTTP_CLIENT_IP'];
        }else{
            return $_SERVER['REMOTE_ADDR'];
        }
    }
    
    /**
     * return the protocol
     * @return string  http or https
     * @since 1.2
     */
   function getProtocol() {
      static $proto = null;
      if ($proto === null)
         $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off' ? 'https://':'http://');
      return $proto;
   }

   /**
    * call it when you want to read the content of the body of a request
    * when the method is not GET or POST
    * @return mixed    array of parameters or a single string when the content-type is unknown
    * @since 1.2
    */
   public function readHttpBody() {
      $input = file_get_contents("php://input");
      $values = array();

      if (strpos($_SERVER["CONTENT_TYPE"], "application/x-www-url-encoded") == 0) {
         parse_str($input, $values);
         return $values;
      }
      else if (strpos($_SERVER["CONTENT_TYPE"], "multipart/form-data") == 0) {

         if (!preg_match("/boundary=([a-zA-Z0-9]+)/", $_SERVER["CONTENT_TYPE"], $m))
            return $input;

         $parts = explode('--'.$m[1], $input);
         foreach($parts as $part) {
            if (trim($part) == '' || $part == '--')
               continue;
            list($header, $value) = explode("\r\n\r\n", $part);
            if (preg_match('/content\-disposition\:(?: *)form\-data\;(?: *)name="([^"]+)"(\;(?: *)filename="([^"]+)")?/i', $header, $m)) {
               if (isset($m[2]) && $m[3] != '')
                  $return[$m[1]] = array( $m[3], $value);
               else
                  $return[$m[1]] = $value;
            }
         }
         if (count($values))
            return $values;
         else
            return $input;
      }
      else {
         return $input;
      }
   }

   private $_headers = null;

   private function _generateHeaders() {
      if (is_null($this->_headers)) {
         if (function_exists('apache_response_headers')) {
            $this->_headers = apache_request_headers();
         }
         else {
            $this->_headers = array();

            foreach($_SERVER as $key => $value) {
               if (substr($key,0,5) == "HTTP_") {
                  $key = str_replace(" ", "-",
                          ucwords(strtolower(str_replace('_', ' ', substr($key,5)))));
                  $this->_headers[$key] = $value;
               }
            }
         }
      }
   }

   public function header($name) {
      $this->_generateHeaders();
      if (isset($this->_headers[$name])) {
         return $this->_headers[$name];
      }
      return null;
   }

   public function headers() {
      $this->_generateHeaders();
      return $this->_headers;
   }


}

