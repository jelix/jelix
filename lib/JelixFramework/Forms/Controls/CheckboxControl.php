<?php
/**
 *
 * @author      Laurent Jouanneau
 * @contributor Julien Issler
 *
 * @copyright   2006-2024 Laurent Jouanneau
 * @copyright   2008 Julien Issler
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;

/**
 */
class CheckboxControl extends AbstractControl
{
    public $type = 'checkbox';
    public $defaultValue = '0';

    /**
     * value that is stored when the checkbox is checked.
     */
    public $valueOnCheck = '1';

    /**
     * value that is stored when the checkbox is unchecked.
     */
    public $valueOnUncheck = '0';

    /**
     * label that is displayed when the value has to be displayed, when the checkbox is checked.
     * If empty, the value of valueOnCheck is displayed.
     */
    public $valueLabelOnCheck = '';

    /**
     * label that is displayed when the value has to be displayed, when the checkbox is unchecked.
     * If empty, the value of valueOnUncheck is displayed.
     */
    public $valueLabelOnUncheck = '';

    public function __construct($ref)
    {
        parent::__construct($ref);
        $this->datatype = new \jDatatypeBoolean();
    }

    public function check()
    {
        $value = $this->container->data[$this->ref];
        if ($this->required && $value == $this->valueOnUncheck) {
            return $this->container->errors[$this->ref] = \Jelix\Forms\Forms::ERRDATA_REQUIRED;
        }
        if ($value != $this->valueOnCheck && $value != $this->valueOnUncheck) {
            return $this->container->errors[$this->ref] = \Jelix\Forms\Forms::ERRDATA_INVALID;
        }

        return null;
    }

    public function setValueFromRequest($request)
    {
        $value = $request->getParam($this->ref);
        if ($value) {
            $this->setData($this->valueOnCheck);
        } else {
            $this->setData($this->valueOnUncheck);
        }
    }

    public function setData($value)
    {
        $value = (string) $value;
        if ($value != $this->valueOnCheck) {
            if ($value == 'on') {
                $value = $this->valueOnCheck;
            } else {
                $value = $this->valueOnUncheck;
            }
        }
        parent::setData($value);
    }

    public function setDataFromDao($value, $daoDatatype)
    {
        if ($daoDatatype == 'boolean') {
            if ($value !== null && ($value === true || strtolower($value) == 'true' || $value === 't' || intval($value) == 1 || $value === 'on')) {
                $value = $this->valueOnCheck;
            } else {
                $value = $this->valueOnUncheck;
            }
        }
        $this->setData($value);
    }

    public function getDisplayValue($value)
    {
        if ($value == $this->valueOnCheck) {
            return $this->valueLabelOnCheck !== '' ? $this->valueLabelOnCheck : $value;
        }

        return $this->valueLabelOnUncheck !== '' ? $this->valueLabelOnUncheck : $value;
    }
}
