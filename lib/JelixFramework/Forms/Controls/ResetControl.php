<?php
/**
 *
 * @author      Laurent Jouanneau
 * @contributor Dominique Papin
 *
 * @copyright   2006-2024 Laurent Jouanneau, 2007 Dominique Papin
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;

/**
 */
class ResetControl extends AbstractControl
{
    public $type = 'reset';

    public function check()
    {
        return null;
    }

    public function isModified()
    {
        return false;
    }
}
