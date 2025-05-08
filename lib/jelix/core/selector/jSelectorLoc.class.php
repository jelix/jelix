<?php
/**
 * see jISelector.iface.php for documentation about selectors.
 *
 * @package     jelix
 * @subpackage  core_selector
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
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * Selector for localization string.
 *
 * Localization strings are stored in file properties.
 * Syntax: "module~prefixFile.keyString".
 * Corresponding file: locales/xx_XX/prefixFile.CCC.properties.
 * xx_XX and CCC are lang and charset set in the configuration
 *
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorLoc extends jSelectorModule
{
    protected $type = 'loc';
    public $fileKey = '';
    public $messageKey = '';
    public $locale = '';
    public $charset = '';
    public $_compiler = 'jLocalesCompiler';
    protected $_where;

    public function __construct($sel, $locale = null, $charset = null)
    {
        if ($locale === null) {
            $locale = jApp::config()->locale;
        }
        if ($charset === null) {
            $charset = jApp::config()->charset;
        }
        if (strpos($locale, '_') === false) {
            $locale = jLocale::langToLocale($locale);
        }
        $this->locale = $locale;
        $this->charset = $charset;
        $this->_suffix = '.'.$charset.'.properties';
        $this->_compilerPath = JELIX_LIB_CORE_PATH.'jLocalesCompiler.class.php';

        if (jelix_scan_locale_sel($sel, $this)) {
            if ($this->module == '') {
                $this->module = jApp::getCurrentModule();
            }
            $this->_createPath();
            $this->_createCachePath();
        } else {
            throw new jExceptionSelector('jelix~errors.selector.invalid.syntax', array($sel, $this->type));
        }
    }

    protected function _createPath()
    {
        if (!jApp::isModuleEnabled($this->module)) {
            if ($this->module == 'jelix') {
                throw new Exception('jelix module is not enabled !!');
            }

            throw new jExceptionSelector('jelix~errors.selector.module.unknown', $this->toString());
        }

        $this->_cacheSuffix = '.'.$this->locale.'.'.$this->charset.'.php';

        $resolutionInCache = jApp::config()->compilation['sourceFileResolutionInCache'];

        if ($resolutionInCache) {
            $resolutionPath = jApp::tempPath('resolved/'.$this->module.'/locales/'.$this->locale.'/'.$this->resource.$this->_suffix);
            $resolutionCachePath = 'resolved/';
            if (file_exists($resolutionPath)) {
                $this->_path = $resolutionPath;
                $this->_where = $resolutionCachePath;

                return;
            }
            jFile::createDir(dirname($resolutionPath));
        }

        if (!$this->findPath($this->locale)) {
            $locales = jLocale::getAlternativeLocales($this->locale, jApp::config());
            $found = false;
            foreach ($locales as $locale) {
                if ($this->findPath($locale)) {
                    $found = true;

                    break;
                }
            }
            if (!$found) {
                // To avoid infinite loop in a specific lang or charset, we should check if we don't
                // try to retrieve the same message as the one we use for the exception below.
                // If it is this message, it means that the error message doesn't exist
                // in the specific lang or charset, so we retrieve it in en_US language and UTF-8 charset.
                if ($this->toString() == 'jelix~errors.selector.invalid.target') {
                    $l = 'en_US';
                    $c = 'UTF-8';
                } else {
                    $l = null;
                    $c = null;
                }

                throw new jExceptionSelector('jelix~errors.selector.invalid.target', array($this->toString(), 'locale'), 1, $l, $c);
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
        $overloadedPath = jApp::varPath('overloads/'.$this->module.'/locales/'.$locale.'/'.$this->resource.$this->_suffix);
        if (is_readable($overloadedPath)) {
            $this->_path = $overloadedPath;
            $this->_where = 'var/overloaded/';

            return true;
        }

        // check if the locale is available in the locales directory in var/
        $localesPath = jApp::varPath('locales/'.$locale.'/'.$this->module.'/locales/'.$this->resource.$this->_suffix);
        if (is_readable($localesPath)) {
            $this->_path = $localesPath;
            $this->_where = 'var/locales/';

            return true;
        }

        // check if the locale has been overloaded in app/
        $overloadedPath = jApp::appPath('app/overloads/'.$this->module.'/locales/'.$locale.'/'.$this->resource.$this->_suffix);
        if (is_readable($overloadedPath)) {
            $this->_path = $overloadedPath;
            $this->_where = 'app/overloaded/';

            return true;
        }

        // check if the locale is available in the locales directory in app/
        $localesPath = jApp::appPath('app/locales/'.$locale.'/'.$this->module.'/locales/'.$this->resource.$this->_suffix);
        if (is_readable($localesPath)) {
            $this->_path = $localesPath;
            $this->_where = 'app/locales/';

            return true;
        }

        // check into other paths (that could be into a Composer package for example)
        $dirs = jApp::getDeclaredLocalesDir();
        foreach ($dirs as $k => $dir) {
            $localesPath = $dir.'/'.$locale.'/'.$this->module.'/locales/'.$this->resource.$this->_suffix;
            if (is_readable($localesPath)) {
                $this->_path = $localesPath;
                $this->_where = 'outside/'.$k.'/';

                return true;
            }
        }

        // else check for the original locale file in the module
        $path = jApp::getModulePath($this->module).'locales/'.$locale.'/'.$this->resource.$this->_suffix;
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
        $this->_cachePath = jApp::tempPath('compiled/locales/'.$this->_where.$this->module.'/'.$this->resource.$this->_cacheSuffix);
    }

    public function toString($full = false)
    {
        if ($full) {
            return $this->type.':'.$this->module.'~'.$this->fileKey.'.'.$this->messageKey;
        }

        return $this->module.'~'.$this->fileKey.'.'.$this->messageKey;
    }
}
