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


require_once (JELIX_LIB_UTILS_PATH    . 'jXmlRpc.class.php');


/**
* analyseur  pour les requêtes XmlRpc
* @package  jelix
* @subpackage core
*/
class jXmlRpcRequest extends jRequest {

    public $type = 'xmlrpc';

    public $defaultResponseType = 'xmlrpc';

    /**
     * initialisation du tableau de parametres vars
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
            list($nom,$vars) = CopixXmlRpc::decodeRequest($requestXml);
            list($module, $action) = explode('.',$nom);
            // Définition de l'action a executer et des paramètres
            $this->params['module'] = $module;
            $this->params['action'] = $action;
            $this->params['params'] = $vars;
            $this->url  = new jUrl($_SERVER['SCRIPT_NAME']);
    }

    public function allowedResponses(){ return array('jResponseXmlRpc');}

}
?>