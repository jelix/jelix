<?php
/**
* @package     jelix
* @subpackage  forms
* @version     $Id:$
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/



/**
 * Classe abstraite pour gérer un formulaire
 */
abstract class jFormsBase {

   protected $_controls = array();
   protected $_container=null;
   protected $_readOnly = false;
   protected $_errors;

   public function __construct(&$container, $reset = false){
      $this->_container = & $container;
   }

   public function initFromRequest(){
      $req = $GLOBALS['gJCoord']->request;
      foreach($this->_controls as $name=>$ctrl){
         $value = $req->getParam($name);
         if($value !== null)
            $this->_container->datas[$name]= $value;
      }
   }


   /**
   * @param $control jFormsControl
   */
   protected function addControl($control){
      $this->_controls [$control->ref] = $control;
      if(!isset($this->_container->datas[$control->ref])){
         $this->_container->datas[$control->ref] = $control->value;
      }
   }

   public function check(){
      $this->_errors = array();
      foreach($this->_controls as $name=>$ctrl){
          $value=$this->_container->datas[$name];
          if($value === null && $ctrl->required){
            $this->_errors[$name]=2;
          }elseif($ctrl->datatype->check($value)){
            $this->_errors[$name]=1;
          }
      }
      return count($this->errors) == 0;
   }

   abstract public function save();

   public function setReadOnly($r = true){  $this->_readOnly = $r;  }

   public function getErrors(){  return $this->_errors;  }

   public function getDatas(){ return $this->_container->datas; }
   public function getContainer(){ return $this->_container; }
   public function id(){ return $this->_container->id; }

}

/**
 * Classe de gestion de formulaire basé sur un DAO
 */
class jFormsDAO extends jFormsBase {


   public function __construct(){

   }

   public function save(){

   }

   public function init(){


   }

}

?>