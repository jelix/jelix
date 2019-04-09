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
 * @param mixed $value
 */

/**
 * Dump any value.
 *
 * @param mixed
 *
 * @return string
 */
function jtpl_modifier_html_vardump($value)
{
    return var_export($value, true);
}
