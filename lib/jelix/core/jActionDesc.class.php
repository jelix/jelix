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

interface jIActionDesc {

    public function perform();
    public function getResponse($name='default');
    public function getResponses();
    public function getPluginParams();
    public function getName();
}



class jActionDesc implements jIActionDesc {
    public $name;
    public $actionsGroupPath;
    public $actionsGroupClass;
    public $method;
    public $pluginParams = array();
    public $responses = array();

    function __construct($name, $pathAg, $ag, $meth){
        $this->name = $name;
        $this->actionsGroupPath = $pathAg;
        $this->actionsGroupClass = $ag;
        $this->method = $meth;
    }

    public function perform (){
        global $gJCoord;
        if(!file_exists($this->actionsGroupPath)){
            trigger_error(jLocale::get('jelix~errors.ad.actiongroup.file.unknow',array($this->name,$this->actionsGroupPath)),E_USER_ERROR);
            return;
        }
        require($this->actionsGroupPath);
        if(!class_exists($this->actionsGroupClass,false)){
            trigger_error(jLocale::get('jelix~errors.ad.actiongroup.class.unknow',array($this->name,$this->actionsGroupClass, $this->actionsGroupPath)),E_USER_ERROR);
            return;
        }
        $class = $this->actionsGroupClass;
        $method = $this->method;


        $ag = new $class($this,$gJCoord->request);

        if(!method_exists($ag,$method)){
            trigger_error(jLocale::get('jelix~errors.ad.actiongroup.method.unknow',array($this->name,$method, $this->actionsGroupClass, $this->actionsGroupPath)),E_USER_ERROR);
            return;
        }
        return $ag->$method();
    }

    public function getResponse($name='default'){
        global $gJCoord, $gJConfig;

        if(!isset($this->responses[$name])){
            trigger_error(jLocale::get('jelix~errors.ad.response.unknow',array($this->name,$name,$this->actionsGroupPath)),E_USER_ERROR);
            return null;
        }
        list($type, $attr) = $this->responses[$name];

        if(!isset($gJConfig->responses[$type])){
            trigger_error(jLocale::get('jelix~errors.ad.response.type.unknow',array($this->name,$type,$name,$this->actionsGroupPath)),E_USER_ERROR);
            return null;
        }
        $respclass = $gJConfig->responses[$type];
        if(file_exists($path=JELIX_LIB_RESPONSE_PATH.$respclass.'.class.php')){
           require_once ($path);
        }elseif(file_exists($path=JELIX_APP_PATH.'responses/'.$respclass.'.class.php')){
           require_once ($path);
        }else{
           trigger_error(jLocale::get('jelix~errors.ad.reponse.not.loaded',array($this->name,$type,$name,$this->actionsGroupPath)),E_USER_ERROR);
           return null;
        }

        if($ar = $gJCoord->request->allowedResponses()){
           if(!in_array($respclass, $ar)){
              trigger_error(jLocale::get('jelix~errors.ad.reponse.type.notallowed',array($this->name,$type,$name,$this->actionsGroupPath)),E_USER_ERROR);
              return null;
           }
        }

        $response = new $respclass($attr);
        $gJCoord->response= $response;

        return $response;
    }

    public function getResponses(){
        return $this->responses;
    }
    public function getPluginParams(){
        return $this->pluginParams;
    }
    public function getName(){
        return $this->name;
    }

}
?>