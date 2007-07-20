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
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
abstract class jFormsControl {
   public $type = null;
   public $ref='';
   public $datatype='string';
   public $required=false;
   public $readonly=false;
   public $label='';
   public $value='';

   function __construct($ref){
      $this->ref = $ref;
   }

   function isContainer(){
        return false; 
   }
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
class jFormsControlInput extends jFormsControl {
   public $type='input';
   public $defaultValue='';
}


/**
 *
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
abstract class jFormsBaseControlSelect1 extends jFormsControl {
    public $type="select1";
    /**
     * @var jIFormDatasource
     */
    public $datasource;
    public $selectedValues=array();
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
class jFormsControlCheckboxes extends jFormsBaseControlSelect1 {
   public $type="checkboxes";

   function isContainer(){
        return true;
   }
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
class jFormsControlRadiobuttons extends jFormsBaseControlSelect1 {
   public $type="radiobuttons";
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
class jFormsControlListbox extends jFormsBaseControlSelect1 {
   public $type="listbox";
   public $multiple = false;

   function isContainer(){
        return $this->multiple;
   }

}

/**
 *
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
class jFormsControlMenulist extends jFormsBaseControlSelect1 {
   public $type="menulist";
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
class jFormsControlTextarea extends jFormsControl {
   public $type='textarea';
   public $defaultValue='';
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
class jFormsControlSecret extends jFormsControl {
   public $type='secret';
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
class jFormsControlCheckbox extends jFormsControl {
   public $type='checkbox';
   public $defaultValue='';
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
class jFormsControlOutput extends jFormsControl {
   public $type='output';
   public $defaultValue='';
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
class jFormsControlUpload extends jFormsControl {
   public $type='upload';
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
class jFormsControlSubmit extends jFormsControl {
   public $type='submit';
}

?>