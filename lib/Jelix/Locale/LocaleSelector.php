<?php
/**
 * see Jelix/Core/Selector/SelectorInterface.php for documentation about selectors.
 *
 * @author      Laurent Jouanneau
 * @contributor Rahal
 * @contributor Julien Issler
 * @contributor Baptiste Toinot
 *
 * @copyright   2005-2020 Laurent Jouanneau
 * @copyright   2007 Rahal
 * @copyright   2008 Julien Issler
 * @copyright   2008 Baptiste Toinot
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Locale;

use Jelix\Core\App;

/**
 * selector for localisation string.
 *
 * localisation string are stored in file properties.
 * syntax : "module~prefixFile.keyString".
 * Corresponding file : locales/xx_XX/prefixFile.UTF-8.properties.
 * xx_XX is lang code set in the configuration
 */
class LocaleSelector extends \Jelix\Core\Selector\ModuleSelector
{
    protected $type = 'loc';
    public $fileKey = '';
    public $messageKey = '';
    public $locale = '';

    protected $_where;

    public function __construct($sel, $locale = null)
    {
        if ($locale === null) {
            $locale = App::config()->locale;
        }
        if (strpos($locale, '_') === false) {
            $locale = Locale::langToLocale($locale);
        }
        $this->locale = $locale;
        $this->_suffix = '.UTF-8.properties';

        if ($this->_scan_sel($sel)) {
            if ($this->module == '') {
                $this->module = App::getCurrentModule();
            }
            $this->_createPath();
            $this->_createCachePath();
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

        $this->_cacheSuffix = '.'.$this->locale.'.UTF-8.php';

        $resolutionInCache = App::config()->compilation['sourceFileResolutionInCache'];

        if ($resolutionInCache) {
            $resolutionPath = App::tempPath('resolved/'.$this->module.'/locales/' . $this->locale.'/'.$this->resource.$this->_suffix);
            $resolutionCachePath = 'resolved/';
            if (file_exists($resolutionPath)) {
                $this->_path = $resolutionPath;
                $this->_where = $resolutionCachePath;

                return;
            }
            \jFile::createDir(dirname($resolutionPath));
        }

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
                // to avoid infinite loop in a specific lang, we should check if we don't
                // try to retrieve the same message as the one we use for the exception below,
                // and if it is this message, it means that the error message doesn't exist
                // in the specific lang, so we retrieve it in en_US language
                if ($this->toString() == 'jelix~errors.selector.invalid.target') {
                    $l = 'en_US';
                } else {
                    $l = null;
                }

                throw new \jExceptionSelector('jelix~errors.selector.invalid.target', array($this->toString(), 'locale'), 1, $l);
            }
        }
        if ($resolutionInCache) {
            symlink($this->_path, $resolutionPath);
            $this->_path = $resolutionPath;
            $this->_where = $resolutionCachePath;
        }
    }

    protected function findPath($locale)
    {
        // check if the locale has been overloaded in var/
        $overloadedPath = App::varPath('overloads/'.$this->module.'/locales/'.$locale.'/'.$this->resource.$this->_suffix);
        if (is_readable($overloadedPath)) {
            $this->_path = $overloadedPath;
            $this->_where = 'var/overloaded/';

            return true;
        }

        // check if the locale is available in the locales directory in var/
        $localesPath = App::varPath('locales/'.$locale.'/'.$this->module.'/locales/'.$this->resource.$this->_suffix);
        if (is_readable($localesPath)) {
            $this->_path = $localesPath;
            $this->_where = 'var/locales/';

            return true;
        }

        // check if the locale has been overloaded in app/
        $overloadedPath = App::appPath('app/overloads/'.$this->module.'/locales/'.$locale.'/'.$this->resource.$this->_suffix);
        if (is_readable($overloadedPath)) {
            $this->_path = $overloadedPath;
            $this->_where = 'app/overloaded/';

            return true;
        }

        // check if the locale is available in the locales directory in app/
        $localesPath = App::appPath('app/locales/'.$locale.'/'.$this->module.'/locales/'.$this->resource.$this->_suffix);
        if (is_readable($localesPath)) {
            $this->_path = $localesPath;
            $this->_where = 'app/locales/';

            return true;
        }

        // else check for the original locale file in the module
        $path = App::getModulePath($this->module).'locales/'.$locale.'/'.$this->resource.$this->_suffix;
        if (is_readable($path)) {
            $this->_where = 'modules/';
            $this->_path = $path;

            return true;
        }

        return false;
    }

    protected function _createCachePath()
    {
        // don't share the same cache for all the possible dirs
        // in case of overload removal
        $this->_cachePath = App::tempPath('compiled/locales/'.$this->_where.$this->module.'/'.$this->resource.$this->_cacheSuffix);
    }

    public function toString($full = false)
    {
        if ($full) {
            return $this->type.':'.$this->module.'~'.$this->fileKey.'.'.$this->messageKey;
        }

        return $this->module.'~'.$this->fileKey.'.'.$this->messageKey;
    }
}
