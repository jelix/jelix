<?php
/**
 * @package    jelix
 * @subpackage utils
 *
 * @author     Laurent Jouanneau
 * @copyright  2008-2010 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * utility class to read and modify two ini files at the same time :
 * one master file, and one file which overrides values of the master file,
 * like we have in jelix with mainconfig.ini.php and config.ini.php of an entry point.
 *
 * @package    jelix
 * @subpackage utils
 *
 * @since 1.1
 * @deprecated
 */
class jIniMultiFilesModifier extends \Jelix\IniFile\MultiIniModifier
{
}

trigger_error('jIniMultiFilesModifier is deprecated, use \\Jelix\\IniFile\\MultiIniModifier instead', E_USER_DEPRECATED);
