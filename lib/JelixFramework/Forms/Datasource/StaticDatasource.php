<?php
/**
 * @author      Laurent Jouanneau
 *
 * @copyright   2006-2024 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Forms\Datasource;


/**
 * A datasource which is based on static values.
 */
class StaticDatasource implements DatasourceInterface
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

    public function getLabel($key, $form)
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
