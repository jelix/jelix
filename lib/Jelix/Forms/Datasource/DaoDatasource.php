<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Dominique Papin, Julien Issler
 *
 * @copyright   2006-2024 Laurent Jouanneau
 * @copyright   2008 Dominique Papin
 * @copyright   2010-2015 Julien Issler
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Forms\Datasource;
use jDao;

/**
 * A datasource which is based on a dao.
 */
class DaoDatasource extends DynamicDatasource
{
    protected $selector;
    protected $method;
    protected $labelProperty = array();
    protected $labelSeparator;
    public $labelMethod = 'get';
    protected $keyProperty;
    protected $profile;

    protected $criteria;

    protected $dao;

    public function __construct($selector, $method, $label, $key, $profile = '', $criteria = null, $criteriaFrom = null, $labelSeparator = '')
    {
        $this->selector = $selector;
        $this->profile = $profile;
        $this->method = $method;
        $this->labelProperty = preg_split('/[\s,]+/', $label);
        $this->labelSeparator = $labelSeparator;
        if ($criteria !== null) {
            $this->criteria = preg_split('/[\s,]+/', $criteria);
        }
        if ($criteriaFrom !== null) {
            $this->setCriteriaControls(preg_split('/[\s,]+/', $criteriaFrom));
        }

        if ($key == '') {
            $rec = jDao::createRecord($this->selector, $this->profile);
            $pfields = $rec->getPrimaryKeyNames();
            $key = $pfields[0];
        }
        $this->keyProperty = $key;
    }

    public function getData($form)
    {
        if ($this->dao === null) {
            $this->dao = jDao::get($this->selector, $this->profile);
        }
        if ($this->criteria !== null) {
            $found = call_user_func_array(array($this->dao, $this->method), $this->criteria);
        } elseif ($this->criteriaFrom !== null) {
            $args = array();
            foreach ((array)$this->criteriaFrom as $criteria) {
                array_push($args, $form->getData($criteria));
            }
            $found = call_user_func_array(array($this->dao, $this->method), $args);
        } else {
            $found = $this->dao->{$this->method}();
        }

        $result = array();

        foreach ($found as $obj) {
            $label = $this->buildLabel($obj);
            $value = $obj->{$this->keyProperty};
            if ($this->groupeBy) {
                $group = (string)$obj->{$this->groupeBy};
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

    public function getLabel2($key, $form)
    {
        if ($key === null || $key == '') {
            return null;
        }

        if ($this->dao === null) {
            $this->dao = jDao::get($this->selector, $this->profile);
        }

        $method = $this->labelMethod;

        if ($this->criteria !== null || $this->criteriaFrom !== null) {
            $countPKeys = count($this->dao->getPrimaryKeyNames());
            if ($this->criteria !== null) {
                $values = $this->criteria;
                array_unshift($values, $key);
            } elseif ($this->criteriaFrom !== null) {
                $values = array($key);
                foreach ((array)$this->criteriaFrom as $criteria) {
                    array_push($values, $form->getData($criteria));
                }
            }

            if ($method == 'get') {
                // in the case where the number of criterias doesn't correspond
                // to the number of field of the primary key, we give only
                // the expected number of values. So the retrieved record
                // won't correspond to the criterias. However, in some case,
                // it could make sens.
                // for example, the dependence could be just a filter...
                while (count($values) != $countPKeys) {
                    array_pop($values);
                }
            }
            $rec = call_user_func_array(array($this->dao, $method), $values);
        } else {
            $rec = $this->dao->{$method}($key);
        }
        if ($rec) {
            return $this->buildLabel($rec);
        }

        return null;
    }

    protected function buildLabel($rec)
    {
        $label = '';
        foreach ((array)$this->labelProperty as $property) {
            if ((string)$rec->{$property} !== '') {
                $label .= $rec->{$property} . $this->labelSeparator;
            }
        }
        if ($this->labelSeparator != '') {
            $label = substr($label, 0, -strlen($this->labelSeparator));
        }

        return $label;
    }
}
