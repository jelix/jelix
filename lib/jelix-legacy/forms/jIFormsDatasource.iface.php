<?php
/**
 * @package     jelix
 * @subpackage  forms
 *
 * @author      Laurent Jouanneau
 * @contributor Dominique Papin
 *
 * @copyright   2007-2022 Laurent Jouanneau
 * @copyright   2008 Dominique Papin
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
interface jIFormsDatasource
{
    /**
     * load and returns data to fill a control. The returned array should be
     * an associative array  key => label.
     *
     * @param jFormsBase $form the form
     *
     * @return array the data
     */
    public function getData($form);

    /**
     * Return the label corresponding to the given key
     * if the class implements also jIFormsDatasource2,
     * you must not call getLabel but getLabel2.
     *
     * @param string $key the key
     *
     * @return string the label
     */
    public function getLabel($key);
}
