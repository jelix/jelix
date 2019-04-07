<?php
/**
 * @package     jelix
 * @subpackage  forms
 *
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 *
 * @copyright   2006-2008 Laurent Jouanneau
 * @copyright   2007 Loic Mathaud
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlSecret extends jFormsControl
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
