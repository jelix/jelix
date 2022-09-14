<?php
/**
 * @package     jelix
 * @subpackage  forms
 *
 * @author      Laurent Jouanneau
 *
 * @copyright   2006-2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */


/**
 * A datasource which is based on static values.
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsStaticDatasource implements jIFormsDatasource2
{
    /**
     * associative array which contains keys and labels.
     *
     * @var array
     */
    public $data = array();

    protected $grouped = false;

    public function getData($form)
    {
        return $this->data;
    }

    public function getLabel2($key, $form)
    {
        return $this->getLabel($key);
    }

    public function getLabel($key)
    {
        if ($this->grouped) {
            foreach ($this->data as $group => $data) {
                if (isset($data[$key])) {
                    return $data[$key];
                }
            }
        } elseif (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }

    public function hasGroupedData()
    {
        return $this->grouped;
    }

    public function setGroupBy($group)
    {
        $this->grouped = $group;
    }
}
