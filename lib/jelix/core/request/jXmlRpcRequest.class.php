<?php
/**
* @package     jelix
* @subpackage  core_request
* @author      Laurent Jouanneau
* @contributor
* @copyright   2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


/**
*
*/
require_once (JELIX_LIB_UTILS_PATH    . 'jXmlRpc.class.php');


/**
* handle XML-rpc call. The response has to be a xml-rpc response.
* @package  jelix
* @subpackage core_request
*/
class jXmlRpcRequest extends jRequest {

    public $type = 'xmlrpc';

    public $defaultResponseType = 'xmlrpc';

    /**
     * analyse the http request and set the params property
     */
    protected function _initParams(){
            global $HTTP_RAW_POST_DATA;
            if(isset($HTTP_RAW_POST_DATA)){
                $requestXml = $HTTP_RAW_POST_DATA;
            }else{
                $requestXml = file('php://input');
                $requestXml = implode("\n",$requestXml);
            }

            // Décodage de la requete
            list($nom,$vars) = jXmlRpc::decodeRequest($requestXml);
            list($module, $action) = explode('.',$nom);

            if(is_array($vars)){
                $this->params = $vars;
            }else
                $this->params['params'] = $vars;

            // Définition de l'action a executer et des paramètres
            $this->params['module'] = $module;
            $this->params['action'] = $action;
    }

    public function allowedResponses(){ return array('jResponseXmlrpc');}

}
?>