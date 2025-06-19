<?php
/**
 *
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 *
 * @copyright   2006-2024 Laurent Jouanneau
 * @copyright   2007 Loic Mathaud
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;

/**
 */
class SecretControl extends AbstractControl
{
    public $type = 'secret';
    public $size = 0;

    public function getDisplayValue($value)
    {
        if ($value == '' && $this->emptyValueLabel !== null) {
            return $this->emptyValueLabel;
        }

        return str_repeat('*', strlen($value));
    }
}
