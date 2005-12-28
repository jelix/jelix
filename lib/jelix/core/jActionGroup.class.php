<?php
/**
* @package    jelix
* @subpackage core
* @version    $Id:$
* @author     Laurent Jouanneau
* @contributor
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Classe orginellement issue du framework Copix 2.3dev20050901. http://www.copix.org (CopixActionGroup)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

class jActionGroup {

    private $request;
    private $action;

    /**
    *
    * @param
    */
    function __construct ($action, $request){
        $this->request = $request;
        $this->action = $action;
    }

    /**
    * CopixZone::process alias
    * @param string $name identifier module|zoneName
    * @param array $params associative array, parameters
    */
    protected function _processZone($name, $params=array ()){
        return jZone::processZone ($name, $params);
    }

    /**
    * Gets the value of a request variable. If not defined, gets its default value.
    * @param string $varName the name of the request variable
    * @param mixed $varDefaultValue the default value of the request variable
    * @return mixed the request variable value
    */
    protected function _get ($varName, $varDefaultValue=null, $useDefaultIfEmpty=false){
       return $this->request->getParam($varName, $varDefaultValue, $useDefaultIfEmpty);
    }

    protected function _getResponse($name){
        return $this->action->getResponse($name);
    }


}
?>