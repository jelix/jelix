<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 *
 */
define('JFORMS_ID_PARAM','__forms_id__');
define('JFORMS_DEFAULT_ID',0);

require_once(JELIX_LIB_FORMS_PATH.'jFormsBase.class.php');
require_once(JELIX_LIB_FORMS_PATH.'jFormsControl.class.php');
require_once(JELIX_LIB_FORMS_PATH.'jFormsDatasource.class.php');
require_once(JELIX_LIB_UTILS_PATH.'jDatatype.class.php');

/**
 * static class to manage and call a form
 *
 * A form is identified by a selector, and each instance of a form have a unique id (formId).
 * This id can be the id of a record for example. If it is not given, the id is set to 0.
 * @package     jelix
 * @subpackage  forms
 */
class jForms {

    private function __construct(){ }

    /**
     * Create a new form with empty datas
     *
     * Call it to create a new form, before to display it.
     * Datas of the form are stored in the php session in a jFormsDataContainer object.
     * If a form with same id exists, datas are erased.
     *
     * @param string $formSel the selector of the xml jform file
     * @param string $formId  the id of the new instance (an id of a record for example)
     * @return jFormBase the object representing the form
     */
    public static function create($formSel , $formId=JFORMS_DEFAULT_ID){
        $sel = new jSelectorForm($formSel);
        jIncluder::inc($sel);
        $c = $sel->getClass();
        if($formId === null) $formId=JFORMS_DEFAULT_ID;
        if(!isset($_SESSION['JFORMS'][$formSel][$formId])){
            $_SESSION['JFORMS'][$formSel][$formId]= new jFormsDataContainer($formSel, $formId);
        }
        $form = new $c($sel->toString(), $_SESSION['JFORMS'][$formSel][$formId],true);
        return $form;
    }

    /**
     * get an existing instance of a form
     *
     * In your controller, call it before to re-display a form with existing datas.
     *
     * @param string $formSel the selector of the xml jform file
     * @param string $formId  the id of the form (if you use multiple instance of a form)
     * @return jFormBase the object representing the form. Return null if there isn't an existing form
     */
    static public function get($formSel,$formId=JFORMS_DEFAULT_ID){
        global $gJCoord;
        if($formId === null) $formId=JFORMS_DEFAULT_ID;

        if(!isset($_SESSION['JFORMS'][$formSel][$formId])){
            return null;
        }

        $sel = new jSelectorForm($formSel);
        jIncluder::inc($sel);
        $c = $sel->getClass();
        $form = new $c($sel->toString(), $_SESSION['JFORMS'][$formSel][$formId],false);

        return $form;
    }

    /**
     * get an existing instance of a form, and fill it with datas provided by the request
     *
     * use it in the action called to submit a webform.
     *
     * @param string $formSel the selector of the xml jform file
     * @param string $formId  the id of the form (if you use multiple instance of a form)
     * @return jFormBase the object representing the form. Return null if there isn't an existing form
     */
    static public function fill($formSel,$formId=JFORMS_DEFAULT_ID){
        $form = self::get($formSel,$formId);
        if($form)
            $form->initFromRequest();
        return $form;
    }

    /**
     * destroy a form in the session
     *
     * use it after saving datas of a form, and if you don't want to re-display the form.
     *
     * @param string $formSel the selector of the xml jform file
     * @param string $formId  the id of the form (if you use multiple instance of a form)
     */
   static public function destroy($formSel,$formId=JFORMS_DEFAULT_ID){
      global $gJCoord;
      if($formId === null) $formId=JFORMS_DEFAULT_ID;
      if(isset($_SESSION['JFORMS'][$formSel][$formId])){
          unset($_SESSION['JFORMS'][$formSel][$formId]);
      }
   }
}

?>
