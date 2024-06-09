<?php
/**
 * @author      Julien Issler
 * @contributor Laurent Jouanneau
 *
 * @copyright   2015 Julien Issler
 * @copyright   2015-2024 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * dummy class for compatibility.
 *
 * @see \Jelix\Forms\Datasource\DynamicDatasource
 * @deprecated
 */
abstract class jFormsDynamicDatasource implements jIFormsDynamicDatasource
{
    protected $criteriaFrom;
    protected $groupeBy = '';

    public function __construct($formid)
    {
    }

    abstract public function getData($form);

    abstract public function getLabel2($key, $form);

    public function getLabel($key)
    {
        throw new Exception('should not be called');
    }

    public function hasGroupedData()
    {
        return $this->groupeBy;
    }

    public function setGroupBy($group)
    {
        $this->groupeBy = $group;
    }

    public function getCriteriaControls()
    {
        return $this->criteriaFrom;
    }

    public function setCriteriaControls($criteriaFrom = null)
    {
        $this->criteriaFrom = $criteriaFrom;
    }

}