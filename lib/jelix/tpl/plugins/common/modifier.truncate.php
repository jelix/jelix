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
 * modifier plugin :  Truncate a string
 *
 * Truncate a string to a certain length if necessary, optionally splitting in
 * the middle of a word, and appending the $etc string.
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @return string
 */
function jtpl_modifier_truncate($string, $length = 80, $etc = '...',
                                  $break_words = false)
{
    if ($length == 0)
        return '';

    if (strlen($string) > $length) {
        $length -= strlen($etc);
        if (!$break_words)
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));

        return substr($string, 0, $length).$etc;
    } else
        return $string;
}
?>