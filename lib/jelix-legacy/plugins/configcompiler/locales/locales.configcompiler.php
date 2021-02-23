<?php
/**
 * @package      jelix
 * @subpackage   core_config_plugin
 *
 * @author       Laurent Jouanneau
 * @copyright    2012 Laurent Jouanneau
 *
 * @see         http://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class localesConfigCompilerPlugin implements \Jelix\Core\Config\CompilerPluginInterface
{
    public function getPriority()
    {
        return 10;
    }

    public function atStart($config)
    {
        if (trim($config->timeZone) === '') {
            $tz = ini_get('date.timezone');
            if ($tz != '') {
                $config->timeZone = $tz;
            } else {
                $config->timeZone = 'Europe/Paris';
            }
        }

        // lang to locale
        $availableLocales = explode(',', $config->availableLocales);
        foreach ($availableLocales as $locale) {
            if (preg_match('/^([a-z]{2,3})_([A-Z]{2,3})$/', $locale, $m)) {
                if (!isset($config->langToLocale['locale'][$m[1]])) {
                    $config->langToLocale['locale'][$m[1]] = $locale;
                }
            } else {
                throw new Exception("Error in the main configuration. Bad locale code in available locales -- availableLocales: '{$locale}' is not a locale code");
            }
        }

        $locale = $config->locale;
        if (preg_match('/^([a-z]{2,3})_([A-Z]{2,3})$/', $locale, $m)) {
            $config->langToLocale['locale'][$m[1]] = $locale;
        } else {
            throw new Exception("Error in the main configuration. Bad locale code in default locale -- config->locale: '{$locale}' is not a locale code");
        }

        if (!in_array($locale, $availableLocales)) {
            array_unshift($availableLocales, $locale);
        }

        $config->availableLocales = $availableLocales;
    }

    public function onModule($config, Jelix\Core\Infos\ModuleInfos $module)
    {
    }

    public function atEnd($config)
    {
    }
}
