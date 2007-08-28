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
define('JFORM_ERRDATA_INVALID',1);
define('JFORM_ERRDATA_REQUIRED',2);

/**
 * base class for all jforms control
 * @package     jelix
 * @subpackage  forms
 */
abstract class jFormsControl {
    public $type = null;
    public $ref='';
    public $datatype;
    public $required = false;
    public $readonly = false;
    public $label='';
    public $value='';
    public $hasHelp = false;
    public $hint='';
    public $alertInvalid='';
    public $alertRequired='';

    function __construct($ref){
        $this->ref = $ref;
        $this->datatype = new jDatatypeString();
    }

    function isContainer(){
        return false; 
    }

    function check($value, $form){
        if($value == '') {
            if($this->required)
                return JFORM_ERRDATA_REQUIRED;
        }elseif(!$this->datatype->check($value)){
            return JFORM_ERRDATA_INVALID;
        }
        return null;
    }

    function getDisplayValue($value){
        return $value;
    }
}

/**
 * bas class for controls which uses a datasource to fill their contents.
 * @package     jelix
 * @subpackage  forms
 */
abstract class jFormsControlDatasource extends jFormsControl {

    public $type="datasource";

    /**
     * @var jIFormDatasource
     */
    public $datasource;
    public $selectedValues=array();

    function getDisplayValue($value){
        if(is_array($value)){
            $labels = array();
            foreach($value as $val){
                $labels[$val]=$this->datasource->getLabel($val);
            }
            return $labels;
        }else{
            return $this->datasource->getLabel($value);
        }
    }
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlInput extends jFormsControl {
    public $type='input';
    public $defaultValue='';
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlCheckboxes extends jFormsControlDatasource {
    public $type="checkboxes";

    function isContainer(){
        return true;
    }

    function check($value, $form){
        if(is_array($value)){
            if(count($value) == 0 && $this->required){
                return JFORM_ERRDATA_REQUIRED;
            }else{
                foreach($value as $v){
                    if(!$this->datatype->check($v)){
                        return JFORM_ERRDATA_INVALID;
                    }
                }
            }
        }else{
            if($value == ''){
                if($this->required)
                    return JFORM_ERRDATA_REQUIRED;
            }else{
                return JFORM_ERRDATA_INVALID;
            }
        }
        return null;
    }
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlRadiobuttons extends jFormsControlDatasource {
    public $type="radiobuttons";
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlListbox extends jFormsControlDatasource {
    public $type="listbox";
    public $multiple = false;
    public $size = 4;

    function isContainer(){
        return $this->multiple;
    }

    function check($value, $form){
        if(is_array($value)){
            if(!$this->multiple){
                return JFORM_ERRDATA_INVALID;
            }
            if(count($value) == 0 && $this->required){
                return JFORM_ERRDATA_REQUIRED;
            }else{
                foreach($value as $v){
                    if(!$this->datatype->check($v)){
                        return JFORM_ERRDATA_INVALID;
                    }
                }
            }
        }else{
            if($value == '' && $this->required){
                return JFORM_ERRDATA_REQUIRED;
            }elseif(!$this->datatype->check($value)){
                return JFORM_ERRDATA_INVALID;
            }
        }
        return null;
    }
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlMenulist extends jFormsControlDatasource {
    public $type="menulist";
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlTextarea extends jFormsControl {
    public $type='textarea';
    public $defaultValue='';
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlSecret extends jFormsControl {
    public $type='secret';
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlSecretConfirm extends jFormsControl {
    public $type='secretconfirm';
    public $primarySecret='';
    function check($value, $form){
        if($value != $form->getData($this->primarySecret))
            return JFORM_ERRDATA_INVALID;
        return null;
    }
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlCheckbox extends jFormsControl {
    public $type='checkbox';
    public $defaultValue='0';
    public $valueOnCheck='1';
    public $valueOnUncheck='0';

    function check($value, $form){
        if($value != $this->valueOnCheck && $value != $this->valueOnUncheck)
            return JFORM_ERRDATA_INVALID;
        return null;
    }
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlOutput extends jFormsControl {
    public $type='output';
    public $defaultValue='';

    public function check($value, $form){
        return null;
    }
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlUpload extends jFormsControl {
    public $type='upload';
    public $mimetype=array();
    public $maxsize=0;

    public $fileInfo = array();

    function check($value, $form){
        if(isset($_FILES[$this->ref]))
            $this->fileInfo = $_FILES[$this->ref];
        else
            $this->fileInfo = array('name'=>'','type'=>'','size'=>0,'tmp_name'=>'', 'error'=>UPLOAD_ERR_NO_FILE);

        if($this->fileInfo['error'] == UPLOAD_ERR_NO_FILE) {
            if($this->required)
                return JFORM_ERRDATA_REQUIRED;
        }else{
            if($this->fileInfo['error'] != UPLOAD_ERR_OK || !is_uploaded_file($this->fileInfo['tmp_name']))
                return JFORM_ERRDATA_INVALID;

            if($this->maxsize && $this->fileInfo['size'] > $this->maxsize)
                return JFORM_ERRDATA_INVALID;

            if(count($this->mimetype)){
                if($this->fileInfo['type']==''){
                    $this->fileInfo['type'] = mime_content_type($this->fileInfo['tmp_name']);
                }
                if(!in_array($this->fileInfo['type'], $this->mimetype))
                    return JFORM_ERRDATA_INVALID;
            }
        }
        return null;
    }
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlSubmit extends jFormsControlDatasource {
    public $type='submit';
    public $standalone = true;
    public function check($value, $form){
        return null;
    }
}

?>