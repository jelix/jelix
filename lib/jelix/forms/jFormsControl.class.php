<?php
/**
 * @package     jelix
 * @subpackage  forms
 *
 * @author      Laurent Jouanneau
 * @contributor Dominique Papin, Olivier Demah, Adrien Lagroy de Croutte
 *
 * @copyright   2006-2018 Laurent Jouanneau, 2008 Dominique Papin
 * @copyright   2009 Olivier Demah, 2020 Adrien Lagroy de Croutte
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * base class for all jforms control.
 *
 * @package     jelix
 * @subpackage  forms
 */
abstract class jFormsControl
{
    /** @var string a type name that identify the control type */
    public $type;

    /** @var string the identifiant of the control */
    public $ref = '';

    /** @var jDatatype the object that manage constraints on the value */
    public $datatype;

    /** @var bool true if the control should be filled by the user */
    public $required = false;

    /** @var string the label */
    public $label = '';

    /** @var mixed the value when the form is created (and not initialized by a data source */
    public $defaultValue = '';

    /** @var string the message for the help on the control (typically help displayed in a popup) */
    public $help = '';

    /** @var string the message for tips on the control (typically the tooltip value) */
    public $hint = '';

    /** @var string the message when the value is invalid */
    public $alertInvalid = '';

    /** @var string the message when there is no value and it is required */
    public $alertRequired = '';

    /** @var bool indicate if the control is in read only mode */
    public $initialReadOnly = false;

    /** @var bool */
    public $initialActivation = true;

    /** @var string label displayed when only values are displayed, and when there is no value */
    public $emptyValueLabel;

    /** @var jFormsBase the form object */
    protected $form;

    /** @var jFormsDataContainer content all values of the form */
    protected $container;

    /** @var array miscellaneous values attached to the control */
    protected $attributes = array();

    /**
     * @param string $ref the identifiant of the control
     */
    public function __construct($ref)
    {
        $this->ref = $ref;
        $this->datatype = new jDatatypeString();
    }

    /**
     * @return string the default widget type to use to render the control
     *
     * @since 1.6.14
     */
    public function getWidgetType()
    {
        return $this->type;
    }

    /**
     * @param jFormsBase $form
     */
    public function setForm($form)
    {
        $this->form = $form;
        $this->container = $form->getContainer();
        if ($this->initialReadOnly) {
            $this->container->setReadOnly($this->ref, true);
        }
        if (!$this->initialActivation) {
            $this->container->deactivate($this->ref, true);
        }
    }

    /**
     * says if the control can have multiple values.
     */
    public function isContainer()
    {
        return false;
    }

    /**
     * check and filter the value of the control.
     *
     * It is the responsability of the implementation to fill the "errors" or "data"
     * properties of the container.
     *
     * @return null|int null if it is ok, or one of jForms::ERRDATA_* constants when there is an error
     */
    public function check()
    {
        $value = $this->container->data[$this->ref];
        $trimedValue = trim($value);
        if (!$this->datatype->allowWhitespace()) {
            $this->container->data[$this->ref] = $value = $trimedValue;
        }
        if ($trimedValue == '') {
            if ($this->required) {
                return $this->container->errors[$this->ref] = jForms::ERRDATA_REQUIRED;
            }
        } elseif (!$this->datatype->check($value)) {
            return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;
        }
        if ($this->datatype instanceof jIFilteredDatatype) {
            $this->container->data[$this->ref] = $this->datatype->getFilteredValue();
        }

        return null;
    }

    public function setData($value)
    {
        if ($value === null) {
            $value = '';
        }
        $this->container->data[$this->ref] = $value;
    }

    public function setReadOnly($r = true)
    {
        $this->container->setReadOnly($this->ref, $r);
    }

    /**
     * @param jRequest $request
     */
    public function setValueFromRequest($request)
    {
        $this->setData($request->getParam($this->ref, ''));
    }

    public function setDataFromDao($value, $daoDatatype)
    {
        $this->setData($value);
    }

    public function getDisplayValue($value)
    {
        if ($value == '' && $this->emptyValueLabel !== null) {
            return $this->emptyValueLabel;
        }

        return $value;
    }

    /**
     * says if the content is html or not.
     *
     * @since 1.2
     */
    public function isHtmlContent()
    {
        return false;
    }

    public function deactivate($deactivation = true)
    {
        $this->container->deactivate($this->ref, $deactivation);
    }

    /**
     * check if the control is activated.
     *
     * @return bool true if it is activated
     */
    public function isActivated()
    {
        return $this->container->isActivated($this->ref);
    }

    /**
     * check if the control is readonly.
     *
     * @return bool true if it is readonly
     */
    public function isReadOnly()
    {
        return $this->container->isReadOnly($this->ref);
    }

    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function getAttribute($name)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return null;
    }

    public function isModified()
    {
        $orig = &$this->container->originalData;
        $value = $this->container->data[$this->ref];

        if (!array_key_exists($this->ref, $orig)) {
            // if the key does not exist in original data, we cannot compare
            return false;
        }

        return $this->_diffValues($orig[$this->ref], $value);
    }

    /**
     * @param mixed $v1
     * @param mixed $v2
     *
     * @return bool true if the values are not equals
     */
    protected function _diffValues(&$v1, &$v2)
    {
        if (is_array($v1) && is_array($v2)) {
            $comp = array_merge(array_diff($v1, $v2), array_diff($v2, $v1));

            return !empty($comp);
        }

        if ($v1 === $v2) {
            return false;
        }

        if (($v1 === '' && $v2 === null) || ($v1 === null && $v2 === '')) {
            return false;
        }

        if (is_numeric($v1) != is_numeric($v2)) {
            return true;
        }

        if (empty($v1) && empty($v2)) {
            return false;
        }

        if (is_array($v1) || is_array($v2)) {
            return true;
        }

        return $v1 != $v2;
    }
}
