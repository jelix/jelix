<?php
/**
 * Plugin from smarty project and adapted for jtpl
 * @package    jelix
 * @subpackage jtpl_plugin
 * @author
 * @copyright  2001-2003 ispi of Lincoln, Inc.
 * @link http://smarty.php.net/
 * @link http://jelix.org/
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * Modifier plugin:  indent lines of text
 *
 * <pre>{$mytext|indent}
 * {$mytext|indent:$number_of_spaces}
 * {$mytext|indent:$number_of_chars:$chars_to_repeat}
 * </pre>
 * @param string
 * @param integer
 * @param string
 * @return string
 */
function jtpl_modifier_common_indent($string,$chars=4,$char=" ")
{
    return preg_replace('!^!m',str_repeat($char,$chars),$string);
}

?>