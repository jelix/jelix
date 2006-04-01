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


   public static function create($formSel , $idName=null)
   {
      $form = self::_getInstance($formSel,$idName, true);
      return $form;
   }

   static public function get($formSel,$idName){
      $form = self::_getInstance($formSel,$idName);
      return $form;
   }

   static public function fill($formSel,$idName){
      $form = self::_getInstance($formSel,$idName);
      $form->initFromRequest();
      return $form;
   }


   static protected function _getInstance($formSel, $idName, $reset=false){
      global $gJCoord;

      $sel = new jSelectorForm($formSel);
      jIncluder::inc($sel);
      $c = $sel->getClass();


      if(empty($idName)){
         $id = 0;
      }else{
         $id = $gJCoord->request->getParam($idName);
      }
      $form = new $c($formSel,$id,$reset);

      return $form;
   }


}

?>