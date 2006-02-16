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

define('JFORMS_ID_PARAM','__forms_id__');


/**
 * Classe abstraite pour gérer un formulaire
 */
abstract class jForms {

   //------------- méthodes statiques

   static public create($formSel){
      $sel = new jSelectorForms($formSel);
      jIncluder::inc($sel);
      $c = $sel->getClass();
      return new $c;
   }

   static public get($formSel){
      $form = self::create($formSel);
      $form->initFromRequest($formSel);
   }

   static public destroy($formSel){
      global $gJCoord;
      $req = $gJCoord->request;
      $id = $gJCoord->request->getParam(JFORMS_ID_PARAM);
      if($id !== null &&  isset($_SESSION['JFORMS'][$id])){
          unset($_SESSION['JFORMS'][$id];
      }
   }

   //---------------- membres non statiques

   protected $_controls = array();
   protected $_container=null;
   protected $_readOnly = false;
   protected $_errors;

   public function __construct(){

   }

   protected function initFromRequest($formSel){
      global $gJCoord;
      $req = $gJCoord->request;
      $this->_container = $this->getDataContainer($req->getParam(JFORMS_ID_PARAM),$formSel);
      foreach($this->_controls as $name=>$ctrl){
         $value = $req->getParam($name);
         if($value !== null)
            $this->_container->set($name, $value);
      }
   }

   protected function getDataContainer($id==null, $formSel){
      if($id === null || ! isset($_SESSION['JFORMS'][$id])){
          $id=md5(uniqid(rand(), true));
          $_SESSION['JFORMS'][$id]= new jFormsDataContainer($id, $formSel);
      }
      return $_SESSION['JFORMS'][$id];
   }

   /**
   * @param $control jFormsControl
   */
   protected function addControl($control){
      $this->_controls [$control->name] = $control;
   }

   public function check(){
      $this->_errors = array();
      foreach($this->_controls as $name=>$ctrl){
          $value=$this->_container->get($name);
          if($value === null && $ctrl->required){
            $this->_errors[$name]=2;
          }elseif($ctrl->datatype->check($value)){
            $this->_errors[$name]=1;
          }
      }
      return count($this->errors) == 0:
   }

   abstract public function save();

   public function setReadOnly($r = true){  $this->_readOnly = $r;  }

   public function getErrors(){  return $this->_errors;  }

   public function getDatas(){ return $this->_container->getDatas(); }

}

/**
 * Classe de gestion de formulaire basé sur un DAO
 */
class jFormsDAO extends jForms {


   public function __construct(){

   }

   public function save(){

   }

   public function init(){


   }

}

?>