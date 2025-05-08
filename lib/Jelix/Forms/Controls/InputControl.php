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
class InputControl extends AbstractControl
{
    public $type = 'input';
    public $size = 0;
    public $placeholder = '';

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

    /**
     * @since 1.2
     */
    public function isHtmlContent()
    {
        return $this->datatype instanceof \jDatatypeHtml;
    }
}
