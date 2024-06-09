<?php
/**
 *
 * @author      Laurent Jouanneau
 * @contributor Thomas
 *
 * @copyright   2006-2024 Laurent Jouanneau, 2009 Thomas
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;

/**
 */
class OutputControl extends AbstractControl
{
    public $type = 'output';

    public function setValueFromRequest($request)
    {
    }

    public function check()
    {
        return null;
    }

    public function setDataFromDao($value, $daoDatatype)
    {
        if ($this->datatype instanceof \jDatatypeLocaleDateTime
            && $daoDatatype == 'datetime') {
            if ($value != '') {
                $dt = new \jDateTime();
                $dt->setFromString($value, \jDateTime::DB_DTFORMAT);
                $value = $dt->toString(\jDateTime::LANG_DTFORMAT);
            }
        } elseif ($this->datatype instanceof \jDatatypeLocaleDate
                && $daoDatatype == 'date') {
            if ($value != '') {
                $dt = new \jDateTime();
                $dt->setFromString($value, \jDateTime::DB_DFORMAT);
                $value = $dt->toString(\jDateTime::LANG_DFORMAT);
            }
        }
        $this->setData($value);
    }
}
