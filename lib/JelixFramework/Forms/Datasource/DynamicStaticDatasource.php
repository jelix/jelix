<?php
/**
 * @package     jelix
 * @subpackage  forms
 *
 * @author      Laurent Jouanneau
 *
 * @copyright   2025 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     https://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Forms\Datasource;

use Jelix\Forms\FormInstance;

/**
 * A datasource which is based on a list of associative array
 *
 * @package     jelix
 * @subpackage  forms
 */
class DynamicStaticDatasource extends DynamicDatasource
{

    /**
     * array of associative array which contains keys, labels and other values that can serve as filters.
     *
     * @var array[]
     */
    public $data = array();

    /**
     * List of array keys that can serve as labels.
     * @var string[]
     */
    protected $labelKeys = array('label');

    /**
     * Separator to glue labels
     * @var string
     */
    protected $labelSeparator = '';

    /**
     * @var string the array key that contain the key value
     */
    protected $keyKey = 'value';

    /**
     * @var array mapping for criterias : form control name => key of the array item
     */
    protected $filterKeysMapping = array();

    public function setKeyAndLabelKeys($keyName, array $labelKeyNames, $separator = ',')
    {
        $this->labelKeys = $labelKeyNames;
        $this->labelSeparator = $separator;
        $this->keyKey = $keyName;
    }

    public function setFilterKeysMapping(array $mapping)
    {
        $this->filterKeysMapping = $mapping;
    }

    public function getData($form)
    {
        if ($this->criteriaFrom !== null) {
            $found = $this->filterWithCriteria($form);
        } else {
            $found = $this->data;
        }

        $result = array();
        $keyProp = $this->keyKey;
        $groupBy = $this->groupeBy;

        foreach ($found as $item) {
            $label = $this->buildLabel($item);
            $value = $item[$keyProp];
            if ($groupBy) {
                $group = (string)$item[$groupBy];
                if (!isset($result[$group])) {
                    $result[$group] = array();
                }
                $result[$group][$value] = $label;
            } else {
                $result[$value] = $label;
            }
        }

        return $result;
    }

    protected function filterWithCriteria(FormInstance $form)
    {
        $args = array();
        foreach ((array)$this->criteriaFrom as $criteria) {

            if (isset($this->filterKeysMapping[$criteria])) {
                $k = $this->filterKeysMapping[$criteria];
            } else {
                $k = $criteria;
            }

            $args[$k] = $form->getData($criteria);
        }

        return array_filter($this->data, function ($item) use ($args) {
            $result = array_intersect_assoc($item, $args);
            return count($result) == count($args);
        });
    }

    public function getLabel($key, $form)
    {
        if ($key === null || $key == '') {
            return null;
        }

        foreach ($this->data as $item) {
            if ($item[$this->keyKey] == $key) {
                return $this->buildLabel($item);
            }
        }

        return null;
    }

    protected function buildLabel($rec)
    {
        if (count($this->labelKeys) == 1) {
            return $rec[$this->labelKeys[0]];
        }

        $label = [];
        foreach ($this->labelKeys as $labelPart) {
            if ((string)$rec[$labelPart] !== '') {
                $label[] = $rec[$labelPart];
            }
        }
        return implode($this->labelSeparator, $label);
    }
}
