<?php
/**
* @package     jelix
* @subpackage  core_request
* @author      Laurent Jouanneau
* @contributor
* @copyright   2005-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * the jJsonRpcRequest require jJsonRpc class
 */
require(JELIX_LIB_UTILS_PATH.'jJsonRpc.class.php');

/**
* handle a JSON-rpc call. The response has to be a json rpc response.
* @package  jelix
* @subpackage core_request
*/
class jJsonRpcRequest extends jRequest {

    public $type = 'jsonrpc';

    public $defaultResponseType = 'jsonrpc';

    public $jsonRequestId=null;

    /**
     * analyse the http request and set the params property
     */
    protected function _initParams(){
        global $HTTP_RAW_POST_DATA;
        if(isset($HTTP_RAW_POST_DATA)){
            $request = $HTTP_RAW_POST_DATA;
        }else{
            $request = file('php://input');
            $request = implode("\n",$request);
        }

        // Décodage de la requete
        $requestobj = jJsonRpc::decodeRequest($request);
        if($requestobj['method']){
            list($module, $action) = explode('~',$requestobj['method']);
        }else{
            $module='';
            $action='';
        }
        if(isset( $requestobj['id']))
            $this->jsonRequestId = $requestobj['id'];

        if(is_array($requestobj['params']))
            $this->params = $requestobj['params'];

        $this->params['params'] = $requestobj['params'];

        // Définition de l'action a executer et des paramètres
        $this->params['module'] = $module;
        $this->params['action'] = $action;
    }

    public function allowedResponses(){ return array('jResponseJsonrpc');}

}
?>