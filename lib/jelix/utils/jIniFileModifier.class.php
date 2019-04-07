<?php
/**
 * @package    jelix
 * @subpackage utils
 *
 * @author     Laurent Jouanneau
 * @copyright  2008-2013 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * utility class to modify an ini file by preserving comments, whitespace..
 * It follows same behaviors of parse_ini_file, except when there are quotes
 * inside values. it doesn't support quotes inside values, because parse_ini_file
 * is totally bugged, depending cases.
 *
 * @package    jelix
 * @subpackage utils
 *
 * @since 1.1
 * @deprecated
 */
class jIniFileModifier extends \Jelix\IniFile\IniModifier
{
}

trigger_error('jIniFileModifier is deprecated, use \\Jelix\\IniFile\\IniModifier instead', E_USER_DEPRECATED);
