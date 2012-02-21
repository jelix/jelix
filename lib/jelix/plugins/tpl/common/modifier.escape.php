<?php
/**
 * Plugin from smarty project and adapted for jtpl
 *
 * @package    jelix
 * @subpackage jtpl_plugin
 * @author     Monte Ohrt <monte at ohrt dot com>
 * @link http://www.smarty.net/
 * @link http://www.smarty.net/manual/en/language.modifier.count.characters.php count_characters (Smarty online manual)
 * @link http://jelix.org/
 */

/**
 * Smarty escape modifier plugin
 *
 * Type:     modifier<br>
 * Name:     escape<br>
 * Purpose:  escape string for output
 *
 * <pre>
 *  {$var|escape}  
 *  {$var|escape:"htmlall"}
 *  {$var|escape:"quotes"}
 *  {$var|escape:"javascript"}
 * </pre>
 * @param string  $string        input string
 * @param string  $esc_type      escape type
 * @param string  $char_set      character set, used for htmlspecialchars() or htmlentities()
 * @param boolean $double_encode encode already encoded entitites again, used for htmlspecialchars() or htmlentities()
 * @return string escaped input string
 */
function jtpl_modifier_common_escape($string, $esc_type = 'html', $char_set = 'UTF-8', $double_encode = true)
{
    if (!$char_set) {
        $char_set = 'UTF-8';
    }

    switch ($esc_type) {
        case 'html':
            return htmlspecialchars($string, ENT_QUOTES, $char_set, $double_encode);

        case 'htmlall':
            if (function_exists('mb_strlen')) {
                // mb_convert_encoding ignores htmlspecialchars()
                $string = htmlspecialchars($string, ENT_QUOTES, $char_set, $double_encode);
                // htmlentities() won't convert everything, so use mb_convert_encoding
                return mb_convert_encoding($string, 'HTML-ENTITIES', $char_set);
            }

            // no MBString fallback
            return htmlentities($string, ENT_QUOTES, $char_set, $double_encode);

        case 'url':
            return rawurlencode($string);

        case 'urlpathinfo':
            return str_replace('%2F', '/', rawurlencode($string));

        case 'quotes':
            // escape unescaped single quotes
            return preg_replace("%(?<!\\\\)'%", "\\'", $string);

        case 'hex':
            // escape every byte into hex
            // Note that the UTF-8 encoded character ä will be represented as %c3%a4
            $return = '';
            $_length = strlen($string);
            for ($x = 0; $x < $_length; $x++) {
                $return .= '%' . bin2hex($string[$x]);
            }
            return $return;

        case 'javascript':
            // escape quotes and backslashes, newlines, etc.
            return strtr($string, array('\\' => '\\\\', "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/'));

        default:
            return $string;
    }
}

?>