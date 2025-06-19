<?php
/**
 * @author     Laurent Jouanneau
 * @author     Gerald Croes
 * @copyright  2001-2005 CopixTeam, 2005-2024 Laurent Jouanneau
 * Some parts of this file are took from Copix Framework v2.3dev20050901, CopixI18N.class.php, http://www.copix.org.
 * copyrighted by CopixTeam and released under GNU Lesser General Public Licence.
 * initial authors : Gerald Croes, Laurent Jouanneau.
 * enhancement by Laurent Jouanneau for Jelix.
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Locale;

use Jelix\Core\App;

/**
 * static class to get a localized string.
 */
class Locale
{
    /**
     * @var Bundle[][]
     */
    public static $bundles = array();

    /**
     * static class...
     */
    private function __construct()
    {
    }

    /**
     * gets the current locale (xx_YY).
     *
     * @return string
     *
     * @since 1.7.0
     */
    public static function getCurrentLocale()
    {
        return App::config()->locale;
    }

    /**
     * gets the current lang (xx from xx_YY).
     *
     * @return string
     */
    public static function getCurrentLang()
    {
        $s = App::config()->locale;

        return substr($s, 0, strpos($s, '_'));
    }

    /**
     * gets the current country (YY from xx_YY).
     *
     * @return string
     */
    public static function getCurrentCountry()
    {
        $s = App::config()->locale;

        return substr($s, strpos($s, '_') + 1);
    }

    /**
     * gets the correct string, for a given language.
     *   if it can't get the correct language, it will try to gets the string
     *   from the default language.
     *   if both fails, it will raise an exception.
     *
     * @param string $key             the key of the localized string
     * @param array  $args            arguments to apply to the localized string with sprintf
     * @param string $locale          the lang code. if null, use the default language
     * @param bool   $tryOtherLocales if true and if the method does not find
     *                                the locale file or the key, it will try with the default
     *                                locale, the fallback local or similar locale
     *
     * @throws Exception
     * @throws \Jelix\Core\Selector\Exception
     *
     * @return string the localized string
     */
    public static function get($key, $args = null, $locale = null, $tryOtherLocales = true)
    {
        list($bundle, $file) = self::getBundleAndSelector($key, $locale);

        //try to get the message from the bundle.
        $string = $bundle->get($file->messageKey);
        if ($string === null) {

            // locale key has not been found
            if (!$tryOtherLocales) {
                throw new Exception('(210)The given locale key "'.$file->toString().
                                    '" does not exists (lang:'.$file->locale.')');
            }

            $words = self::tryOtherLocales($key, $args, $locale, App::config());

            if ($words === null) {
                throw new Exception('(213)The given locale key "'.$file->toString().
                                    '" does not exists in any default languages');
            }

            return $words;
        }

        //here, we know the message
        if ($args !== null && $args !== array()) {
            $string = call_user_func_array('sprintf', array_merge(array($string), is_array($args) ? $args : array($args)));
        }

        return $string;
    }

    /**
     * @param $key
     * @param $locale
     * @return Bundle
     * @throws \Jelix\Core\Selector\Exception
     */
    public static function getBundle($key, $locale = null)
    {
        list($bundle, $selector) = self::getBundleAndSelector($key, $locale);
        return $bundle;
    }

    /**
     * @param $key
     * @param $locale
     * @return array
     * @throws \Jelix\Core\Selector\Exception
     */
    protected static function getBundleAndSelector($key, $locale = null)
    {
        try {
            $file = new LocaleSelector($key, $locale);
        } catch (\Jelix\Core\Selector\Exception $e) {
            // the file is not found
            if ($e->getCode() == 12) {
                // unknown module..
                throw $e;
            }

            throw new Exception('(212)No locale file found for the given locale key "'.$key
                .'" in any other default languages', 212, $e);
        }

        $locale = $file->locale;
        $keySelector = $file->module.'~'.$file->fileKey;

        if (!isset(self::$bundles[$keySelector][$locale])) {
            self::$bundles[$keySelector][$locale] = new Bundle($file, $locale);
        }
        return [ self::$bundles[$keySelector][$locale], $file ];
    }


    /**
     * return the list of alternative locales to the given one.
     *
     * @param string $locale
     * @param object $config the configuration object
     *
     * @return array
     */
    public static function getAlternativeLocales($locale, $config)
    {
        $otherLocales = array();
        $similarLocale = self::langToLocale(substr($locale, 0, strpos($locale, '_')));
        if ($similarLocale != $locale) {
            $otherLocales[] = $similarLocale;
        }

        if ($locale != $config->locale) {
            $otherLocales[] = $config->locale;
        }

        if ($config->fallbackLocale && $locale != $config->fallbackLocale) {
            $otherLocales[] = $config->fallbackLocale;
        }

        return $otherLocales;
    }

    protected static function tryOtherLocales($key, $args, $locale, $config)
    {
        $otherLocales = self::getAlternativeLocales($locale, $config);
        foreach ($otherLocales as $loc) {
            try {
                return Locale::get($key, $args, $loc, false);
            } catch (\Exception $e) {
            }
        }

        return null;
    }

    /**
     * says if the given locale or lang code is available in the application.
     *
     * @param string $locale               the locale code (xx_YY) or a lang code (xx)
     * @param bool   $strictCorrespondance if true don't try to find a locale from an other country
     * @param mixed  $l
     *
     * @return string the corresponding locale
     */
    public static function getCorrespondingLocale($l, $strictCorrespondance = false)
    {
        if (strpos($l, '_') === false) {
            $l = self::langToLocale($l);
        }

        if ($l != '') {
            $avLoc = &App::config()->availableLocales;
            if (in_array($l, $avLoc)) {
                return $l;
            }
            if ($strictCorrespondance) {
                return '';
            }
            $l2 = self::langToLocale(substr($l, 0, strpos($l, '_')));
            if ($l2 != $l && in_array($l2, $avLoc)) {
                return $l2;
            }
        }

        return '';
    }

    /**
     * returns the locale corresponding of one of the accepted language indicated
     * by the browser, and which is available in the application.
     *
     * @return string the locale. empty if not found.
     */
    public static function getPreferedLocaleFromRequest()
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return '';
        }

        $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($languages as $bl) {
            if (!preg_match('/^([a-zA-Z]{2,3})(?:[-_]([a-zA-Z]{2,3}))?(;q=[0-9]\\.[0-9])?$/', $bl, $match)) {
                continue;
            }
            $l = strtolower($match[1]);
            if (isset($match[2])) {
                $l .= '_'.strtoupper($match[2]);
            }
            $lang = self::getCorrespondingLocale($l);
            if ($lang != '') {
                return $lang;
            }
        }

        return '';
    }

    /**
     * @var array content of the lang_to_locale.ini.php
     */
    protected static $langToLocale;

    /**
     * returns the locale corresponding to a lang.
     *
     * The file lang_to_locale gives corresponding locales, but you can override these
     * association into the langToLocale section of the main configuration
     *
     * @param string $lang a lang code (xx)
     *
     * @return string the corresponding locale (xx_YY)
     */
    public static function langToLocale($lang)
    {
        $conf = App::config();
        if (isset($conf->langToLocale['locale'][$lang])) {
            return $conf->langToLocale['locale'][$lang];
        }
        if (is_null(self::$langToLocale)) {
            $content = @parse_ini_file(__DIR__.'/lang_to_locale.ini.php');
            self::$langToLocale = $content['locale'];
        }
        if (isset(self::$langToLocale[$lang])) {
            return self::$langToLocale[$lang];
        }

        return '';
    }

    /**
     * @var string[][] first key is lang code of translation of names, second key is lang code
     */
    protected static $langNames = array();

    /**
     * @param string $lang       the lang for which we want the name
     * @param string $langOfName if empty, return the name in its own language
     *
     * @since 1.7.0
     */
    public static function getLangName($lang, $langOfName = '')
    {
        if ($langOfName == '') {
            $langOfName = '_';
        }

        if (!isset(self::$langNames[$langOfName])) {
            $fileName = 'lang_names_'.$langOfName.'.ini';
            if (!file_exists(__DIR__.'/'.$fileName)) {
                $fileName = 'lang_names_en.ini';
            }
            $names = parse_ini_file($fileName, false, INI_SCANNER_RAW);
            self::$langNames[$langOfName] = $names['names'];
        }

        if (isset(self::$langNames[$langOfName][$lang])) {
            return self::$langNames[$langOfName][$lang];
        }

        return $lang;
    }
}
