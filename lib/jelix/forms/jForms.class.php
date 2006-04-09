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
require_once(JELIX_LIB_FORMS_PATH.'jFormsBase.class.php');
require_once(JELIX_LIB_FORMS_PATH.'jFormsControl.class.php');
require_once(JELIX_LIB_UTILS_PATH.'jDatatype.class.php');
/**
 * Classe abstraite pour gérer un formulaire
 */
class jForms {

   private function __construct(){ }


   public static function create($formSel , $id=null)
   {
      $form = self::_getInstance($formSel,$id, true);
      return $form;
   }

   static public function get($formSel,$idName=''){
      global $gJCoord;
      if(empty($idName)){
         $id = 0;
      }else{
         $id = $gJCoord->request->getParam($idName);
      }
      $form = self::_getInstance($formSel,$id);
      return $form;
   }

   static public function fill($formSel,$idName=''){
      $form = self::get($formSel,$idName);
      $form->initFromRequest();
      return $form;
   }


   static protected function _getInstance($formSel, $id, $reset=false){
      $sel = new jSelectorForm($formSel);
      jIncluder::inc($sel);
      $c = $sel->getClass();

      if($id == null) $id=0;
      if(!isset($_SESSION['JFORMS'][$formSel][$id]) || $reset){
          $_SESSION['JFORMS'][$formSel][$id]= new jFormsDataContainer($formSel, $id);
      }

      $form = new $c($_SESSION['JFORMS'][$formSel][$id],$reset);

      return $form;
   }

   static public function destroy($formSel,$idName=''){
      global $gJCoord;
      if(empty($idName)){
         $id = 0;
      }else{
         $id = $gJCoord->request->getParam($idName,0);
      }

      if(isset($_SESSION['JFORMS'][$formSel][$id])){
          unset($_SESSION['JFORMS'][$formSel][$id]);
      }
   }

}

?>