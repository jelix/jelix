<?php
/**
* @package     jelix
* @subpackage  core
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


require_once (JELIX_LIB_UTILS_PATH    . 'jJsonRpc.class.php');



/**
* Analyseur pour les requêtes JSON-RPC
* @package  jelix
* @subpackage core
*/
class jJsonRpcRequest extends jRequest {

    public $type = 'jsonrpc';

    public $defaultResponseType = 'jsonrpc';

    /**
     * initialisation du tableau de parametres vars
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

        // Définition de l'action a executer et des paramètres
        $this->params['module'] = $module;
        $this->params['action'] = $action;
        $this->params['params'] = $requestobj['params'];
        $this->params['id']  = $requestobj['id'];
        $this->url  = new jUrl($_SERVER['SCRIPT_NAME']);
    }

    public function allowedResponses(){ return array('jResponseJsonrpc');}

}
?>