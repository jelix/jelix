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
interface jFormsDatasource2 extends jFormsDatasource
{
    /**
     * Says if data are grouped, ie, if getData() returns a simple array
     * value=>label (false) or if it returns an array of simple arrays
     * array('group label'=>array(value=>label,)) (true).
     *
     * @return bool
     */
    public function hasGroupedData();

    /**
     * set a parameter indicating how data are grouped.
     *
     * @param string $group the group parameter
     */
    public function setGroupBy($group);

    /**
     * Return the label corresponding to the given key.
     * It replace getLabel so it should be called instead of getLabel.
     *
     * @param string     $key  the key
     * @param jFormsBase $form the form
     *
     * @return string the label
     */
    public function getLabel2($key, $form);
}