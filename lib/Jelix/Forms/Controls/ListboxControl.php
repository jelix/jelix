<?php
/**
 *
 * @author      Laurent Jouanneau
 * @copyright   2006-2024 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;

/**
 * listbox.
 *
 */
class ListboxControl extends AbstractDatasourceControl
{
    public $type = 'listbox';
    public $multiple = false;
    public $size = 4;
    public $emptyItemLabel;

    public function isContainer()
    {
        return $this->multiple;
    }

    public function check()
    {
        $value = $this->container->data[$this->ref];
        if (is_array($value)) {
            if (!$this->multiple) {
                return $this->container->errors[$this->ref] = \Jelix\Forms\Forms::ERRDATA_INVALID;
            }
            if (count($value) == 0 && $this->required) {
                return $this->container->errors[$this->ref] = \Jelix\Forms\Forms::ERRDATA_REQUIRED;
            }
        } else {
            if (trim($value) == '' && $this->required) {
                return $this->container->errors[$this->ref] = \Jelix\Forms\Forms::ERRDATA_REQUIRED;
            }
        }

        return null;
    }
}
