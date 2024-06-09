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
namespace Jelix\Forms\Datasource;

/**
 * Interface for objects which provides a source of data to fill some controls in a form,
 * like menulist, listbox etc...
 */
interface DynamicDatasourceInterface extends DatasourceInterface
{
    /**
     * Return the list of controls name that provide criterion values.
     *
     * @return string[]
     */
    public function getCriteriaControls();

    /**
     * set the list of controls name that provide criterion values.
     *
     * @param string[] $criteriaFrom
     */
    public function setCriteriaControls($criteriaFrom = null);
}
