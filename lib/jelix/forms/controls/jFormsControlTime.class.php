<?php

/**
 *
 * @package     jelix
 * @subpackage  forms
 */

class jFormsControlTime extends jFormsControl
{
    public $type = 'time';

    public function __construct($ref)
    {
        $this->ref = $ref;
        $this->datatype = new jDatatypeTime();
    }

    function setValueFromRequest($request) {
        $value = $request->getParam($this->ref,'');
        if (is_array($value))
            $value = $value['hour'].':'.$value['minute'].':'.$value['second'];
        if($value == '::')
            $value = '';
        $this->setData($value);
    }

    function getDisplayValue($value) {
        if ($value != '') {
            $dt = new jDateTime();
            $dt->setFromString($value, jDateTime::DB_TFORMAT);
            $value = $dt->toString(jDateTime::LANG_TFORMAT);
        }
        else if ($this->emptyValueLabel !== null) {
            return $this->emptyValueLabel;
        }

        return $value;
    }
}