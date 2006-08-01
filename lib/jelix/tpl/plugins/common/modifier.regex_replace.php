<?php
/**
 * Plugin from smarty project and adapted for jtpl
 * @package    jelix
 * @subpackage jtpl_plugin
 * @version    $Id$
 * @author
 * @copyright  2001-2003 ispi of Lincoln, Inc.
 * @link http://smarty.php.net/
 * @link http://jelix.org/
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * modifier plugin : regular epxression search/replace
 * @param string
 * @param string|array
 * @param string|array
 * @return string
 */
function jtpl_modifier_regex_replace($string, $search, $replace)
{
    if (preg_match('!\W(\w+)$!s', $search, $match) &&
            (strpos($match[1], 'e') !== false)) {
        /* remove eval-modifier from $search */
        $search = substr($search, 0, -strlen($match[1])) .
            str_replace('e', '', $match[1]);
    }
    return preg_replace($search, $replace, $string);
}

?>