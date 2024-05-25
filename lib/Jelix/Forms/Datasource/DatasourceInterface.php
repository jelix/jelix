<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Dominique Papin
 *
 * @copyright   2007-2024 Laurent Jouanneau
 * @copyright   2008 Dominique Papin
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Datasource;

use Jelix\Forms\FormInstance;

/**
 * Interface for objects which provides a source of data to fill some controls in a form,
 * like menulist, listbox etc...
 */
interface DatasourceInterface
{
    /**
     * load and returns data to fill a control. The returned array should be
     * an associative array  key => label.
     *
     * @param FormInstance $form the form
     *
     * @return array the data
     */
    public function getData($form);

    /**
     * Return the label corresponding to the given key
     *
     * @param string $key the key
     * @param FormInstance $form the form
     *
     * @return string the label
     */
    public function getLabel($key, $form);

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
}
