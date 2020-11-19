<?php
/**
 * @package     jelix
 * @subpackage  forms
 *
 * @author      Laurent Jouanneau
 * @contributor Thomas
 *
 * @copyright   2012 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlButton extends jFormsControl
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
