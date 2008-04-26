<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @contributor Loic Mathaud, Dominique Papin
* @copyright   2006-2008 Laurent Jouanneau, 2007 Dominique Papin
* @copyright   2007 Loic Mathaud
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

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
    public $defaultValue='';
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

    function check($form){
        $value = $form->getContainer()->data[$this->ref];
        if($value == '') {
            if($this->required)
                return jForms::ERRDATA_REQUIRED;
        }elseif(!$this->datatype->check($value)){
            return jForms::ERRDATA_INVALID;
        }elseif($this->datatype instanceof jIFilteredDatatype) {
            $form->getContainer()->data[$this->ref] = $this->datatype->getFilteredValue();
        }
        return null;
    }

    function getDisplayValue($value){
        return $value;
    }

    function getValueFromRequest($form, $requestValue) {
        return $requestValue;
    }

    function prepareValueFromDao($value, $daoDatatype) {
        return $value;
    }

}


/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlInput extends jFormsControl {
    public $type='input';
    public $size=0;

    function prepareValueFromDao($value, $daoDatatype) {
        if($this->datatype instanceof jDatatypeLocaleDateTime
            && $daoDatatype == 'datetime') {
            if($value != '') {
                $dt = new jDateTime();
                $dt->setFromString($value, jDateTime::DB_DTFORMAT);
                $value = $dt->toString(jDateTime::LANG_DTFORMAT);
            }
        }elseif($this->datatype instanceof jDatatypeLocaleDate
                && $daoDatatype == 'date') {
            if($value != '') {
                $dt = new jDateTime();
                $dt->setFromString($value, jDateTime::DB_DFORMAT);
                $value = $dt->toString(jDateTime::LANG_DFORMAT);
            }
        }
        return $value;
    }


}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlTextarea extends jFormsControl {
    public $type='textarea';
    public $rows=5;
    public $cols=40;
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 * @since 1.1
 */
class jFormsControlHtmlEditor extends jFormsControl {
    public $type='htmleditor';
    public $rows=5;
    public $cols=40;
    public $config='default';
    public $skin='default';
    function __construct($ref){
        $this->ref = $ref;
        $this->datatype = new jDatatypeHtml();
    }
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlSecret extends jFormsControl {
    public $type='secret';
    public $size=0;

    function check($form){
        if ($form->getContainer()->data[$this->ref] == '' && $this->required) {
            return jForms::ERRDATA_REQUIRED;
        }
        return null;
    }
    function getDisplayValue($value){
        return str_repeat("*", strlen($value));
    }
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlSecretConfirm extends jFormsControl {
    public $type='secretconfirm';
    public $size=0;
    public $primarySecret='';

    function check($form){
        if($form->getContainer()->data[$this->ref] != $form->getData($this->primarySecret))
            return jForms::ERRDATA_INVALID;
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

    function check($form){
        $value = $form->getContainer()->data[$this->ref];
        if($value != $this->valueOnCheck && $value != $this->valueOnUncheck)
            return jForms::ERRDATA_INVALID;
        return null;
    }

    function getValueFromRequest($form, $requestValue) {
        if($requestValue){
            return  $this->valueOnCheck;
        }else{
            return $this->valueOnUncheck;
        }
    }

    function prepareValueFromDao($value, $daoDatatype) {
        if( $daoDatatype == 'boolean') {
            if($value == 'TRUE'||  $value == 't'|| $value == '1'|| $value == true){
                $value = $this->valueOnCheck;
            }else {
                $value = $this->valueOnUncheck;
            }
        }
        return $value;
    }

}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlOutput extends jFormsControl {
    public $type='output';

    public function check($form){
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

    function check($form){
        if(isset($_FILES[$this->ref]))
            $this->fileInfo = $_FILES[$this->ref];
        else
            $this->fileInfo = array('name'=>'','type'=>'','size'=>0,'tmp_name'=>'', 'error'=>UPLOAD_ERR_NO_FILE);

        if($this->fileInfo['error'] == UPLOAD_ERR_NO_FILE) {
            if($this->required)
                return jForms::ERRDATA_REQUIRED;
        }else{
            if($this->fileInfo['error'] != UPLOAD_ERR_OK || !is_uploaded_file($this->fileInfo['tmp_name']))
                return jForms::ERRDATA_INVALID;

            if($this->maxsize && $this->fileInfo['size'] > $this->maxsize)
                return jForms::ERRDATA_INVALID;

            if(count($this->mimetype)){
                if($this->fileInfo['type']==''){
                    $this->fileInfo['type'] = mime_content_type($this->fileInfo['tmp_name']);
                }
                if(!in_array($this->fileInfo['type'], $this->mimetype))
                    return jForms::ERRDATA_INVALID;
            }
        }
        return null;
    }

    function getValueFromRequest($form, $requestValue) {
        if(isset($_FILES[$this->ref])){
            return $_FILES[$this->ref]['name'];
        }else{
            return '';
        }
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
    public function check($form){
        return null;
    }

    function getValueFromRequest($form, $requestValue) {

        if($requestValue && !$this->standalone) {
            // because IE send the <button> content as value instead of the content of the
            // "value" attribute, we should verify it and get the real value
            // or when using <input type="submit">, we have only the label as value (in all browsers...
            $data = $this->datasource->getData($form);
            if(!isset($data[$requestValue])) {
                $data=array_flip($data);
                if(isset($data[$requestValue])) {
                    $requestValue = $data[$requestValue];
                }
            }
        }
        return $requestValue;
    }

}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlReset extends jFormsControl {
    public $type='reset';
    public function check($form){
        return null;
    }
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlHidden extends jFormsControlReset {
    public $type='hidden';
}

/**
 * captcha control
 * @package     jelix
 * @subpackage  forms
 * @since 1.1
 */
class jFormsControlCaptcha extends jFormsControl {
    public $type = 'captcha';
    public $question='';
    public $required = true;
    function check($form){
        $value = $form->getContainer()->data[$this->ref];
        if($value == '') {
            return jForms::ERRDATA_REQUIRED;
        }elseif($value != $form->getContainer()->privateData[$this->ref]){
            return jForms::ERRDATA_INVALID;
        }
        return null;
    }

    function initExpectedValue($form){
        $numbers = jLocale::get('jelix~captcha.number');
        $id = rand(1,intval($numbers));
        $this->question = jLocale::get('jelix~captcha.question.'.$id);
        $form->getContainer()->privateData[$this->ref] = jLocale::get('jelix~captcha.response.'.$id);
    }
}

/**
 * base class for controls which uses a datasource to fill their contents.
 * @package     jelix
 * @subpackage  forms
 */
abstract class jFormsControlDatasource extends jFormsControl {

    public $type="datasource";

    /**
     * @var jIFormsDatasource
     */
    public $datasource;
    public $defaultValue=array();

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
 * Checkboxes control (contains several checkboxes)
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlCheckboxes extends jFormsControlDatasource {
    public $type="checkboxes";

    function isContainer(){
        return true;
    }

    function check($form){
        $value = $form->getContainer()->data[$this->ref];
        if(is_array($value)){
            if(count($value) == 0 && $this->required){
                return jForms::ERRDATA_REQUIRED;
            }
        }else{
            if($value == ''){
                if($this->required)
                    return jForms::ERRDATA_REQUIRED;
            }else{
                return jForms::ERRDATA_INVALID;
            }
        }
        return null;
    }
}

/**
 * listbox
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

    function check($form){
        $value = $form->getContainer()->data[$this->ref];
        if(is_array($value)){
            if(!$this->multiple){
                return jForms::ERRDATA_INVALID;
            }
            if(count($value) == 0 && $this->required){
                return jForms::ERRDATA_REQUIRED;
            }
        }else{
            if($value == '' && $this->required){
                return jForms::ERRDATA_REQUIRED;
            }
        }
        return null;
    }
}

/**
 * control which contains several radio buttons
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlRadiobuttons extends jFormsControlDatasource {
    public $type="radiobuttons";

    function check($form){
        if($form->getContainer()->data[$this->ref] == '' && $this->required) {
            return jForms::ERRDATA_REQUIRED;
        }
        return null;
    }
}

/**
 * menulist/combobox
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlMenulist extends jFormsControlRadiobuttons {
    public $type="menulist";
}


