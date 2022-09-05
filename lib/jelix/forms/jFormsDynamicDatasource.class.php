<?php
/**
 * @package     jelix
 * @subpackage  forms
 *
 * @author      Laurent Jouanneau
 * @contributor Dominique Papin, Julien Issler
 *
 * @copyright   2006-2015 Laurent Jouanneau
 * @copyright   2008 Dominique Papin
 * @copyright   2010-2015 Julien Issler
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
/**
 * Base class for a datasource which is based on a class and can be used for dynamic
 * listboxes or menulists.
 *
 * @package     jelix
 * @subpackage  forms
 */
abstract class jFormsDynamicDatasource implements jFormsDynamicDatasourceInterface
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