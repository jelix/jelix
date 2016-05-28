<?php
/**
* @author     Laurent Jouanneau
* @author     Gerald Croes
* @copyright  2001-2005 CopixTeam, 2005-2016 Laurent Jouanneau
* Some parts of this file are took from Copix Framework v2.3dev20050901, CopixI18N.class.php, http://www.copix.org.
* copyrighted by CopixTeam and released under GNU Lesser General Public Licence.
* initial authors : Gerald Croes, Laurent Jouanneau.
* enhancement by Laurent Jouanneau for Jelix.
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Locale;
use Jelix\Core\App;

/**
 * static class to get a localized string
 */
class Locale {
    /**
     *
     */
    static $bundles = array();

    /**
     * static class...
     */
    private function __construct(){}

    /**
     * gets the current lang
     * @return string
     */
    static function getCurrentLang(){
        $s = App::config()->locale;
        return substr($s,0, strpos($s,'_'));
    }
    /**
     * gets the current country.
     * @return string
     */
    static function getCurrentCountry (){
        $s = App::config()->locale;
        return substr($s,strpos($s,'_')+1);
    }

    /**
    * gets the correct string, for a given language.
    *   if it can't get the correct language, it will try to gets the string
    *   from the default language.
    *   if both fails, it will raise an exception.
    * @param string $key the key of the localized string
    * @param array $args arguments to apply to the localized string with sprintf
    * @param string $locale  the lang code. if null, use the default language
    * @param string $charset the charset code. if null, use the default charset
    * @param boolean $tryOtherLocales if true and if the method does not find
    *                   the locale file or the key, it will try with the default
    *                   locale, the fallback local or similar locale
    * @return string the localized string
    */
    static function get ($key, $args=null, $locale=null, $charset=null, $tryOtherLocales=true) {

        $config = App::config();
        try {
            $file = new LocaleSelector($key, $locale, $charset);
        }
        catch (\Jelix\Core\Selector\Exception $e) {
            // the file is not found
            if ($e->getCode() == 12) {
                // unknown module..
                throw $e;
            }
            if ($locale === null)  {
                $locale = $config->locale;
            }
            if ($charset === null) {
                $charset = $config->charset;
            }
            if (!$tryOtherLocales) {
                throw new Exception('(211)No locale file found for the given locale key "'.$key
                                .'" (charset '.$charset.', lang '.$locale.')');
            }

            $words = self::tryOtherLocales($key, $args, $locale, $charset, $config);
            if ($words === null) {
                throw new Exception('(212)No locale file found for the given locale key "'.$key
                                .'" in any other default languages (charset '.$charset.')');
            }
            return $words;
        }

        $locale = $file->locale;
        $keySelector = $file->module.'~'.$file->fileKey;

        if (!isset (self::$bundles[$keySelector][$locale])) {
            self::$bundles[$keySelector][$locale] =  new Bundle ($file, $locale);
        }

        $bundle = self::$bundles[$keySelector][$locale];

        //try to get the message from the bundle.
        $string = $bundle->get ($file->messageKey, $file->charset);
        if ($string === null) {

            // locale key has not been found
            if (!$tryOtherLocales) {
                throw new Exception('(210)The given locale key "'.$file->toString().
                                    '" does not exists (lang:'.$file->locale.
                                    ', charset:'.$file->charset.')');
            }

            $words = self::tryOtherLocales($key, $args, $locale, $charset, $config);
            if ($words === null) {
                throw new Exception('(213)The given locale key "'.$file->toString().
                                    '" does not exists in any default languages for the '.$file->charset.' charset');
            }
            return $words;
        }
        else {
            //here, we know the message
            if ($args !== null && $args !== array()) {
                $string = call_user_func_array('sprintf', array_merge (array ($string), is_array ($args) ? $args : array ($args)));
            }
            return $string;
        }
    }

    static protected function tryOtherLocales($key, $args, $locale, $charset, $config) {
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

            foreach($otherLocales as $loc) {
                try {
                    return Locale::get ($key, $args, $loc, $charset, false);
                }
                catch(\Exception $e) {
                }
            }
            return null;
    }
    
    /**
     * says if the given locale or lang code is available in the application
     * @param string $locale the locale code (xx_YY) or a lang code (xx)
     * @param boolean $strictCorrespondance if true don't try to find a locale from an other country
     * @return string the corresponding locale
     */
    static function getCorrespondingLocale($l, $strictCorrespondance=false) {

        if (strpos($l, '_') === false) {
            $l = self::langToLocale($l);
        }

        if ($l != '') {
            $avLoc = &App::config()->availableLocales;
            if (in_array($l, $avLoc)) {
                return $l;
            }
            if ($strictCorrespondance)
                return '';
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
     * @return string the locale. empty if not found.
     */
    static function getPreferedLocaleFromRequest() {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
            return '';

        $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($languages as $bl) {
            if (!preg_match("/^([a-zA-Z]{2,3})(?:[-_]([a-zA-Z]{2,3}))?(;q=[0-9]\\.[0-9])?$/",$bl,$match))
                continue;
            $l = strtolower($match[1]);
            if (isset($match[2]))
                $l .= '_'.strtoupper($match[2]);
            $lang = self::getCorrespondingLocale($l);
            if ($lang != '')
                return $lang;
        }
        return '';
    }

    /**
     * @var array content of the lang_to_locale.ini.php
     */
    static protected $langToLocale = null;

    /**
     * returns the locale corresponding to a lang.
     *
     * The file lang_to_locale give corresponding locale, but you can override these
     * association into the langToLocale section of the main configuration
     * @param string $lang a lang code (xx)
     * @return string the corresponding locale (xx_YY)
     */
    static function langToLocale($lang) {
        $conf = App::config();
        if (isset($conf->langToLocale[$lang]))
            return $conf->langToLocale[$lang];
        if (is_null(self::$langToLocale)) {
            self::$langToLocale = @parse_ini_file(__DIR__.'/lang_to_locale.ini.php');
        }
        if (isset(self::$langToLocale[$lang])) {
            return self::$langToLocale[$lang];
        }
        return '';
    }
}
