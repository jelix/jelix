<?php
/**
 * Plugin from smarty project and adapted for jtpl
 * @package    jelix
 * @subpackage jtpl_plugin
 * @author
 * @contributor Laurent Jouanneau (utf8 compliance)
 * @copyright  2001-2003 ispi of Lincoln, Inc., 2007 Laurent Jouanneau
 * @link http://smarty.php.net/
 * @link http://jelix.org/
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * modifier plugin :  Truncate a string
 *
 * Truncate a string to a certain length if necessary, optionally splitting in
 * the middle of a word, and appending the $etc string.
 * <pre>{$mytext|truncate}
 * {$mytext|truncate:40}
 * {$mytext|truncate:45:'...'}
 * {$mytext|truncate:60:'...':true}
 * </pre>
 * @param string
 * @param integer $length
 * @param string $etc
 * @param boolean $break_words
 * @return string
 */
function jtpl_modifier_common_truncate($string, $length = 80, $etc = '...',
                                  $break_words = false)
{
    if ($length == 0)
        return '';
    $charset = jTpl::getEncoding();
    if (iconv_strlen($string,$charset) > $length) {
        $length -= iconv_strlen($etc,$charset);
        if (!$break_words)
            $string = preg_replace('/\s+?(\S+)?$/', '', iconv_substr($string, 0, $length+1,$charset));

        return iconv_substr($string, 0, $length,$charset).$etc;
    } else
        return $string;
}
?>