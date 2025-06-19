<?php
/**
 * @package  Jelix\Legacy
 *
 * @author   Laurent Jouanneau
 * @contributor
 *
 * @copyright 2014 Laurent Jouanneau
 *
 * @see     http://www.jelix.org
 * @licence  MIT
 */

/**
 * dummy class for compatibility.
 *
 * @see \Jelix\Locale\Locale
 * @deprecated
 */
class jLocale extends \Jelix\Locale\Locale
{
    public static function get($key, $args = null, $locale = null, $charset = null, $tryOtherLocales = true)
    {
        if ($charset !== null) {
            trigger_error("jLocale::get(): charset parameter is deprecated and not used any more.", E_USER_DEPRECATED);
        }
        return parent::get($key, $args, $locale, $tryOtherLocales);
    }
}
