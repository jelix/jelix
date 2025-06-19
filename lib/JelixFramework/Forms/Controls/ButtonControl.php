<?php
/**
 *
 * @author      Laurent Jouanneau
 * @contributor Thomas
 *
 * @copyright   2012-2024 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;

/**
 */
class ButtonControl extends AbstractControl
{
    public $type = 'button';

    public function setValueFromRequest($request)
    {
    }

    public function check()
    {
        return null;
    }

    public function setDataFromDao($value, $daoDatatype)
    {
        $this->setData($value);
    }
}
