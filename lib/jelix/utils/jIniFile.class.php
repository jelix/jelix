<?php
/**
 * @package    jelix
 * @subpackage utils
 *
 * @author     Loic Mathaud
 * @contributor Laurent Jouanneau, Erika31, Julien Issler
 *
 * @copyright  2006 Loic Mathaud, 2008-2012 Laurent Jouanneau, 2017 Erika31, 2017 Julien Issler
 *
 * @see        http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * utility class to read and write an ini file.
 *
 * @package    jelix
 * @subpackage utils
 *
 * @since 1.0b1
 * @deprecated
 */
class jIniFile extends \Jelix\IniFile\Util
{
}

trigger_error('jIniFile is deprecated, use \\Jelix\\IniFile\\Util instead', E_USER_DEPRECATED);
