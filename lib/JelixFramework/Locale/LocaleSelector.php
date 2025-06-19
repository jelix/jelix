<?php
/**
 * see Jelix/Core/Selector/SelectorInterface.php for documentation about selectors.
 *
 * @author      Laurent Jouanneau
 * @contributor Rahal
 * @contributor Julien Issler
 * @contributor Baptiste Toinot
 *
 * @copyright   2005-2025 Laurent Jouanneau
 * @copyright   2007 Rahal
 * @copyright   2008 Julien Issler
 * @copyright   2008 Baptiste Toinot
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Locale;

use Jelix\Core\App;

/**
 * Selector for localization string.
 *
 * Localization strings are stored in file properties.
 * Syntax: "module~prefixFile.keyString".
 * Corresponding file: locales/xx_XX/prefixFile.UTF-8.properties.
 * xx_XX is lang code set in the configuration
 */
class LocaleSelector extends \Jelix\Core\Selector\ModuleSelector
{
    protected $type = 'loc';
    public $fileKey = '';
    public $messageKey = '';
    public $locale = '';

    public function __construct($sel, $locale = null)
    {
        if ($locale === null) {
            $locale = App::config()->locale;
        }
        if (strpos($locale, '_') === false) {
            $locale = Locale::langToLocale($locale);
        }
        $this->locale = $locale;
        $this->_suffix = '.properties';

        if ($this->_scan_sel($sel)) {
            if ($this->module == '') {
                $this->module = App::getCurrentModule();
            }
            $this->_createPath();
        } else {
            throw new \Jelix\Core\Selector\Exception('jelix~errors.selector.invalid.syntax', array($sel, $this->type));
        }
    }

    protected function _scan_sel($selStr)
    {
        if (preg_match('/^(([a-zA-Z0-9_\\.]+)~)?([a-zA-Z0-9_]+)\\.([a-zA-Z0-9_\\-\\.]+)$/', $selStr, $m)) {
            if ($m[1] != '' && $m[2] != '') {
                $this->module = $m[2];
            } else {
                $this->module = '';
            }
            $this->resource = $m[3];
            $this->fileKey = $m[3];
            $this->messageKey = $m[4];

            return true;
        }

        return false;
    }

    protected function _createPath()
    {
        if (!App::isModuleEnabled($this->module)) {
            if ($this->module == 'jelix') {
                throw new Exception('jelix module is not enabled !!');
            }

            throw new \Jelix\Core\Selector\Exception('jelix~errors.selector.module.unknown', $this->toString());
        }

        $this->_path = App::getModulePath($this->module).'locales/'.$this->locale.'/'.$this->resource.$this->_suffix;

        if (!$this->findPath($this->locale)) {
            $locales = Locale::getAlternativeLocales($this->locale, App::config());
            $found = false;
            foreach ($locales as $locale) {
                if ($this->findPath($locale)) {
                    $found = true;

                    break;
                }
            }
            if (!$found) {
                // To avoid infinite loop in a specific lang, we should check if we don't
                // try to retrieve the same message as the one we use for the exception below.
                // If it is this message, it means that the error message doesn't exist
                // in the specific lang, so we retrieve it in en_US language.
                throw new Exception('(212)No locale file found for the given locale key "'.$this->toString()
                    .'" in any languages', 212);
            }
        }
    }

    protected function findPath($locale)
    {
        $this->_cachePath = LocaleCompiler::getCacheFileName($this->module, $locale, $this->resource.$this->_suffix, App::buildPath());
        return (is_readable($this->_cachePath));
    }

    protected function _createCachePath()
    {
        // nothing
    }

    public function toString($full = false)
    {
        if ($full) {
            return $this->type.':'.$this->module.'~'.$this->fileKey.'.'.$this->messageKey;
        }

        return $this->module.'~'.$this->fileKey.'.'.$this->messageKey;
    }
}
