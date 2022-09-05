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
 * Interface for objects which provides a source of data to fill some controls in a form,
 * like menulist, listbox etc...
 *
 * @package     jelix
 * @subpackage  forms
 */
interface jFormsDynamicDatasourceInterface extends jFormsDatasource2
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