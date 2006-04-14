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


   public static function create($formSel , $userId=null)
   {
      $sel = new jSelectorForm($formSel);
      jIncluder::inc($sel);
      $c = $sel->getClass();

      if($userId == null)
         $userId=0;
      $internalId = md5($userId);

      if(!isset($_SESSION['JFORMS'][$formSel][$internalId])){
          $_SESSION['JFORMS'][$formSel][$internalId]= new jFormsDataContainer($formSel, $internalId, $userId);
      }
      $form = new $c($_SESSION['JFORMS'][$formSel][$internalId],true);
      return $form;
   }

   static public function get($formSel,$internalIdName=''){
      global $gJCoord;
      if(empty($internalIdName)){
         $internalId = 0;
      }else{
         $internalId = $gJCoord->request->getParam($internalIdName);
         if($internalId == null) $internalId=0;
      }

      if(!isset($_SESSION['JFORMS'][$formSel][$internalId])){
          return null;
      }

      $sel = new jSelectorForm($formSel);
      jIncluder::inc($sel);
      $c = $sel->getClass();
      $form = new $c($_SESSION['JFORMS'][$formSel][$internalId],false);

      return $form;
   }

   static public function fill($formSel,$internalIdName=''){
      $form = self::get($formSel,$internalIdName);
      $form->initFromRequest();
      return $form;
   }

   static public function destroy($formSel,$internalIdName=''){
      global $gJCoord;
      if(empty($internalIdName)){
         $internalId = 0;
      }else{
         $internalId = $gJCoord->request->getParam($internalIdName);
         if($internalId == null) $internalId=0;
      }

      if(isset($_SESSION['JFORMS'][$formSel][$internalId])){
          unset($_SESSION['JFORMS'][$formSel][$internalId]);
      }
   }

}

?>