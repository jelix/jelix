<?php
/**
* @package     jelix
* @subpackage  utils
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
require_once (LIB_PATH.'json/JSON.php');

/**
 * objet permettant d'encoder/décoder des request/responses Json-RPC
 * pour les specs, voir http://json-rpc.org/index.xhtml
 */
class jJsonRpc {

    private function __construct(){}

    /**
     *
     * @param
     * @return
     */
    public static function decodeRequest($content){
        // {method:.. , params:.. , id:.. }
        $json = new JSON(JSON_LOOSE_TYPE);

        $obj = $json->decode($content);
        /*
        $obj->method
        $obj->params
        $obj->id*/
        return $obj; //array($methodname, $params);
    }

    /**
     *
     * @param
     * @return
     */
    public static function encodeRequest($methodname, $params, $id=1){
        $json = new JSON();
        $request = '{method:"'.$methodname.'",params:'.$json->encode($params).',id:'.$json->encode($id).'}';
        return $request;
    }

    /**
     *
     * @param
     * @return
     */
    public static function decodeResponse($content){
        // {result:.. , error:.. , id:.. }
        $json = new JSON(JSON_LOOSE_TYPE);

        $response = $json->decode($content);
        return $response;
    }

    /**
     *
     * @param
     * @return
     */
    public static function encodeResponse($params, $id=1){
        $json = new JSON();
        $request = '{result:'.$json->encode($params).',error:null,id:'.$json->encode($id).'}';
        return $request;
    }

    /**
     *
     * @param
     * @return
     */
    public static function encodeFaultResponse($code, $message, $id=1){
        $json = new JSON();
        $request = '{result:null,error:{code: '.$json->encode($code).', string:'.$json->encode($message).' },id:'.$json->encode($id).'}';
        return $request;
    }
}

?>