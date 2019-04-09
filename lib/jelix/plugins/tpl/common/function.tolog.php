<?php
/**
 * @package    jelix
 * @subpackage jtpl_plugin
 *
 * @copyright  2018 Laurent Jouanneau
 *
 * @see http://jelix.org/
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 *
 * @param mixed $tpl
 * @param mixed $message
 */

/**
 * Dump a value into log files.
 *
 * @param jTpl  $tpl
 * @param mixed $message
 */
function jtpl_function_common_tolog($tpl, $message)
{
    jLog::log($message);
}
