<?php
/**
 *
 * @author      Laurent Jouanneau
 * @copyright   2006-2024 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;

/**
 * base class for controls which uses a datasource to fill their contents.
 *
 */
abstract class AbstractDatasourceControl extends AbstractControl
{
    public $type = 'datasource';

    /**
     * @var \jIFormsDatasource|\Jelix\Forms\Datasource\DatasourceInterface
     */
    public $datasource;
    public $defaultValue = array();

    public function getDisplayValue($value)
    {
        if (is_array($value)) {
            $labels = array();
            foreach ($value as $val) {
                $labels[$val] = $this->_getLabel($val);
            }
            if (count($labels) == 0 && $this->emptyValueLabel !== null) {
                return $this->emptyValueLabel;
            }

            return $labels;
        }

        $label = $this->_getLabel($value);
        if ($label == '' && $this->emptyValueLabel !== null) {
            return $this->emptyValueLabel;
        }

        return $label;
    }

    protected function _getLabel($value)
    {
        if ($this->datasource instanceof \Jelix\Forms\Datasource\DatasourceInterface) {
            return $this->datasource->getLabel($value, $this->form);
        }
        // deprecated
        if ($this->datasource instanceof \jIFormsDatasource2) {
            return $this->datasource->getLabel2($value, $this->form);
        }
        // deprecated
        return $this->datasource->getLabel($value);
    }
}
