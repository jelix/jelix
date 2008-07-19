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
    public $label='';
    public $defaultValue='';
    public $hasHelp = false;
    public $hint='';
    public $alertInvalid='';
    public $alertRequired='';

    public $initialReadOnly = false;
    public $initialActivation = true;

    protected $form;
    protected $container;


    function __construct($ref){
        $this->ref = $ref;
        $this->datatype = new jDatatypeString();
    }

    function setForm($form) {
        $this->form = $form;
        $this->container = $form->getContainer();
        if($this->initialReadOnly)
            $this->container->setReadOnly($this->ref, true);
        if(!$this->initialActivation)
            $this->container->deactivate($this->ref, true);
    }

    /**
     * says if the control can have multiple values
     */
    function isContainer(){
        return false;
    }

    function check(){
        $value = $this->container->data[$this->ref];
        if($value == '') {
            if($this->required)
                return $this->container->errors[$this->ref] = jForms::ERRDATA_REQUIRED;
        }elseif(!$this->datatype->check($value)){
            return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;
        }elseif($this->datatype instanceof jIFilteredDatatype) {
            $this->container->data[$this->ref] = $this->datatype->getFilteredValue();
        }
        return null;
    }

    function setData($value) {
        if($this->container->data[$this->ref] != $value)
            $this->form->setModifiedFlag($this->ref);
        $this->container->data[$this->ref] = $value;
    }

    function setReadOnly($r){
        $this->container->setReadOnly($this->ref, $r);
    }

    function setValueFromRequest($request) {
        $this->setData($request->getParam($this->ref,''));
    }

    function setDataFromDao($value, $daoDatatype) {
        $this->setData($value);
    }

    function getDisplayValue($value){
        return $value;
    }

    public function deactivate($deactivation=true) {
        $this->container->deactivate($this->ref, $deactivation);
    }

    /**
    * check if the control is activated
    * @return boolean true if it is activated
    */
    public function isActivated() {
        return $this->container->isActivated($this->ref);
    }

    /**
     * check if the control is readonly
     * @return boolean true if it is readonly
     */
    public function isReadOnly() {
        return $this->container->isReadOnly($this->ref);
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

    function setDataFromDao($value, $daoDatatype) {
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
        $this->setData($value);
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

    function check(){
        if ($this->container->data[$this->ref] == '' && $this->required) {
            return $this->container->errors[$this->ref] = jForms::ERRDATA_REQUIRED;
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

    function check(){
        if($this->container->data[$this->ref] != $this->form->getData($this->primarySecret))
            return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;
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

    function check(){
        $value = $this->container->data[$this->ref];
        if($value != $this->valueOnCheck && $value != $this->valueOnUncheck)
            return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;
        return null;
    }

    function setValueFromRequest($request) {
        $value = $request->getParam($this->ref);
        if($value){
            $this->setData($this->valueOnCheck);
        }else{
            $this->setData($this->valueOnUncheck);
        }
    }

    function setData($value) {
        if($value != $this->valueOnCheck){
            if($value =='on')
                $value = $this->valueOnCheck;
            else
                $value = $this->valueOnUncheck;
        }
        parent::setData($value);
    }

    function setDataFromDao($value, $daoDatatype) {
        if( $daoDatatype == 'boolean') {
            if($value == 'TRUE'||  $value == 't'|| $value == '1'|| $value == true){
                $value = $this->valueOnCheck;
            }else {
                $value = $this->valueOnUncheck;
            }
        }
        $this->setData($value);
    }

}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlOutput extends jFormsControl {
    public $type='output';

    public function check(){
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

    function check(){
        if(isset($_FILES[$this->ref]))
            $this->fileInfo = $_FILES[$this->ref];
        else
            $this->fileInfo = array('name'=>'','type'=>'','size'=>0,'tmp_name'=>'', 'error'=>UPLOAD_ERR_NO_FILE);

        if($this->fileInfo['error'] == UPLOAD_ERR_NO_FILE) {
            if($this->required)
                return $this->container->errors[$this->ref] = jForms::ERRDATA_REQUIRED;
        }else{
            if($this->fileInfo['error'] != UPLOAD_ERR_OK || !is_uploaded_file($this->fileInfo['tmp_name']))
                return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;

            if($this->maxsize && $this->fileInfo['size'] > $this->maxsize)
                return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;

            if(count($this->mimetype)){
                if($this->fileInfo['type']==''){
                    $this->fileInfo['type'] = mime_content_type($this->fileInfo['tmp_name']);
                }
                if(!in_array($this->fileInfo['type'], $this->mimetype))
                    return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;
            }
        }
        return null;
    }

    function setValueFromRequest($request) {
        if(isset($_FILES[$this->ref])){
            $this->setData($_FILES[$this->ref]['name']);
        }else{
            $this->setData('');
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

    public function check(){
        return null;
    }

    function setValueFromRequest($request) {

        $value = $request->getParam($this->ref,'');

        if($value && !$this->standalone) {
            // because IE send the <button> content as value instead of the content of the
            // "value" attribute, we should verify it and get the real value
            // or when using <input type="submit">, we have only the label as value (in all browsers...)
            $data = $this->datasource->getData($this->form);
            if(!isset($data[$value])) {
                $data=array_flip($data);
                if(isset($data[$value])) {
                    $value = $data[$value];
                }
            }
        }
        $this->setData($value);
    }

}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlReset extends jFormsControl {
    public $type='reset';

    public function check(){
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
    function check(){
        $value = $this->container->data[$this->ref];
        if($value == '') {
            return $this->container->errors[$this->ref] = jForms::ERRDATA_REQUIRED;
        }elseif($value !=  $this->container->privateData[$this->ref]){
            return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;
        }
        return null;
    }

    function initExpectedValue(){
        $numbers = jLocale::get('jelix~captcha.number');
        $id = rand(1,intval($numbers));
        $this->question = jLocale::get('jelix~captcha.question.'.$id);
        $this->container->privateData[$this->ref] = jLocale::get('jelix~captcha.response.'.$id);
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

    function check(){
        $value = $this->container->data[$this->ref];
        if(is_array($value)){
            if(count($value) == 0 && $this->required){
                return $this->container->errors[$this->ref] = jForms::ERRDATA_REQUIRED;
            }
        }else{
            if($value == ''){
                if($this->required)
                    return $this->container->errors[$this->ref] = jForms::ERRDATA_REQUIRED;
            }else{
                return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;
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

    function check(){
        $value = $this->container->data[$this->ref];
        if(is_array($value)){
            if(!$this->multiple){
                return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;
            }
            if(count($value) == 0 && $this->required){
                return $this->container->errors[$this->ref] = jForms::ERRDATA_REQUIRED;
            }
        }else{
            if($value == '' && $this->required){
                return $this->container->errors[$this->ref] = jForms::ERRDATA_REQUIRED;
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
    public $defaultValue='';
    function check(){
        if($this->container->data[$this->ref] == '' && $this->required) {
            return $this->container->errors[$this->ref] = jForms::ERRDATA_REQUIRED;
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
    public $defaultValue='';
}


/**
 * abstract classes for controls which contain other controls
 * @package     jelix
 * @subpackage  forms
 */
abstract class jFormsControlGroups extends jFormsControl {
    public $type = 'groups';

    /**
     * all child controls of the group
     */
    protected $childControls = array();

    function check(){
        $rv = null;
        foreach($this->childControls as $ctrl) {
            if(($rv2 = $ctrl->check())!==null) {
                $rv = $rv2;
            }
        }
        return $rv;
    }

    function getDisplayValue($value){
        return $value;
    }

    function setValueFromRequest($request) {
        foreach($this->childControls as $name => $ctrl) {
            if(!$this->form->isActivated($name) || $this->form->isReadOnly($name))
                continue;
            $ctrl->setValueFromRequest($request);
        }
        $this->setData($request->getParam($this->ref,''));
    }

    function addChildControl($control, $itemName = '') {
        $this->childControls[$control->ref]=$control;
    }

    function getChildControls() { return $this->childControls;}

    function setReadOnly($r){
        $this->container->setReadOnly($this->ref, $r);
        foreach($this->childControls as $ctrl) {
           $ctrl->setReadOnly($r);
        }
    }

    public function deactivate($deactivation=true) {
        $this->container->deactivate($this->ref, $deactivation);
        foreach($this->childControls as $ctrl) {
            $ctrl->deactivate($deactivation);
        }
    }
}

/**
 * group
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlGroup extends jFormsControlGroups {
    public $type="group";
}


/**
 * choice
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlChoice extends jFormsControlGroups {

    public $type="choice";

    /**
     * list of item. Each value is an array which contains corresponding controls of the item
     * an item could not have controls, in this case its value is an empty array
     */
    public $items = array();

    public $itemsNames = array();

    function check(){
        if(isset($this->items[$this->container->data[$this->ref]])) {
            $rv = null;
            foreach($this->items[$this->container->data[$this->ref]] as $ctrl) {
                if (($rv2 = $ctrl->check()) !== null) {
                    $rv = $rv2;
                }
            }
            return $rv;
        } else {
            return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;
        }
    }

    function createItem($value, $label) {
        $this->items[$value] = array();
        $this->itemsNames[$value]= $label;
    }

    function addChildControl($control, $itemValue = '') {
        $this->childControls[$control->ref]=$control;
        $this->items[$itemValue][$control->ref] = $control;
    }

    function setData($value) {
        parent::setData($value);
        // we deactivate controls which are not selected
        foreach($this->items as $item => $list) {
            $ro = ($item != $value);
            foreach($list as $ref=>$ctrl) {
                $this->form->setReadOnly($ref, $ro);
            }
        }
    }

    function setValueFromRequest($request) {
        $this->setData($request->getParam($this->ref,''));
        if(isset($this->items[$this->container->data[$this->ref]])){
            foreach($this->items[$this->container->data[$this->ref]] as $name=>$ctrl) {
                $ctrl->setValueFromRequest($request);
            }
        }
    }
}

/**
 * switch
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
class jFormsControlSwitch extends jFormsControlChoice {
    public $type="switch";


    function setValueFromRequest($request) {
        //$this->setData($request->getParam($this->ref,''));
        if(isset($this->items[$this->container->data[$this->ref]])){
            foreach($this->items[$this->container->data[$this->ref]] as $name=>$ctrl) {
                $ctrl->setValueFromRequest($request);
            }
        }
    }
}

/*
 * repeat
 * @package     jelix
 * @subpackage  forms
 */
/*
class jFormsControlRepeat extends jFormsControlGroups {
    public $type="repeat";
}*/

