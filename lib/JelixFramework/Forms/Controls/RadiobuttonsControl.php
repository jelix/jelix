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
 * control which contains several radio buttons.
 *
 */
class RadiobuttonsControl extends AbstractDatasourceControl
{
    public $type = 'radiobuttons';
    public $defaultValue = '';

    public function check()
    {
        if ($this->container->data[$this->ref] == '' && $this->required) {
            return $this->container->errors[$this->ref] = \Jelix\Forms\Forms::ERRDATA_REQUIRED;
        }

        return null;
    }
}
