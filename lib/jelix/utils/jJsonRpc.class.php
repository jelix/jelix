<?php
/**
* @package     jelix
* @subpackage  utils
* @author      Laurent Jouanneau
* @contributor
* @copyright   2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

#ifndef ENABLE_PHP_JSON
/**
 *
 */
require_once (LIB_PATH.'json/JSON.php');
#endif
/**
 * object which encode and decode a jsonrpc request and response
 * @package    jelix
 * @subpackage utils
 * @link http://json-rpc.org/index.xhtml
 */
class jJsonRpc {

    private function __construct(){}

    /**
     * decode a request of json xmlrpc
     * @param string $content
     * @return mixed
     */
    public static function decodeRequest($content){
        // {method:.. , params:.. , id:.. }
#ifdef ENABLE_PHP_JSON
        $obj = json_decode($content);
#else
        $json = new JSON(JSON_LOOSE_TYPE);
        $obj = $json->decode($content);
        /*
        $obj->method
        $obj->params
        $obj->id*/
#endif
        return $obj;
    }

    /**
     * create a request content for a jsonrpc call
     * @param string $methodname method of the jsonrcp web service
     * @param array $params parameters for the methods
     * @return string jsonrcp request content
     */
    public static function encodeRequest($methodname, $params, $id=1){

#ifdef ENABLE_PHP_JSON
        return '{"method":"'.$methodname.'","params":'.json_encode($params).',"id":'.json_encode($id).'}';
#else
        $json = new JSON();
        return '{"method":"'.$methodname.'","params":'.$json->encode($params).',"id":'.$json->encode($id).'}';
#endif

    }

    /**
     * decode a jsonrpc response
     * @param string $content
     * @return mixed decoded content
     */
    public static function decodeResponse($content){
        // {result:.. , error:.. , id:.. }
#ifdef ENABLE_PHP_JSON
        return json_decode($content);
#else
        $json = new JSON(JSON_LOOSE_TYPE);
        return $json->decode($content);
#endif

    }

    /**
     * encode a jsonrpc response
     * @param array $params  returned value
     * @return string encoded response
     */
    public static function encodeResponse($params, $id=1){
#ifdef ENABLE_PHP_JSON
        return '{"result":'.json_encode($params).',"error":null,"id":'.json_encode($id).'}';
#else
        $json = new JSON();
        return '{"result":'.$json->encode($params).',"error":null,"id":'.$json->encode($id).'}';
#endif
    }

    /**
     * encode a jsonrpc error response
     * @param int $code code error
     * @param string $message error message
     * @return string encoded response
     */
    public static function encodeFaultResponse($code, $message, $id=1){
#ifdef ENABLE_PHP_JSON
        return '{"result":null,"error":{"code": '.json_encode($code).', "string":'.json_encode($message).' },"id":'.json_encode($id).'}';
#else
        $json = new JSON();
        return '{"result":null,"error":{"code": '.$json->encode($code).', "string":'.$json->encode($message).' },"id":'.$json->encode($id).'}';
#endif
    }
}

?>