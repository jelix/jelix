<?php
/**
 *
 * @author      Adrien Lagroy de Croutte
 * @contributor Laurent Jouanneau
 *
 * @copyright   2020 Adrien Lagroy de Croutte, 2020-2024 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;

/**
 */
class TimeControl extends AbstractControl
{
    public $type = 'time';
    public $enableSeconds = false;
    public $timepickerConfig = false;

    public function __construct($ref)
    {
        parent::__construct($ref);
        $this->datatype = new \jDatatypeTime();
    }

    public function setValueFromRequest($request)
    {
        $value = $request->getParam($this->ref, '');
        if (is_array($value)) {
            $value = $value['hour'].':'.$value['minutes'].':'.$value['seconds'];
        }
        if ($value == '::') {
            $value = '';
        }
        $this->setData($value);
    }

    public function getDisplayValue($value)
    {
        if ($value != '') {
            $dt = new \jDateTime();
            $dt->setFromString($value, \jDateTime::DB_TFORMAT);
            $value = $dt->toString(\jDateTime::LANG_TFORMAT);
        } elseif ($this->emptyValueLabel !== null) {
            return $this->emptyValueLabel;
        }

        return $value;
    }
}
