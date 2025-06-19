<?php
/**
 * see Jelix/Core/Selector/SelectorInterface.php for documentation about selectors.
 *
 * @author      Laurent Jouanneau
 * @copyright   2005-2023 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Template;

use Jelix\Core\App;
use Jelix\Core\Selector\Exception;
use Jelix\Core\Selector\ModuleSelector;

/**
 * Template selector.
 *
 * syntax : "module~tplName".
 * file : templates/tplName.tpl .
 *
 */
class TemplateSelector extends ModuleSelector
{
    protected $type = 'tpl';
    protected $_dirname = 'templates/';
    protected $_suffix = '.tpl';
    protected $_cachePrefix;
    public $outputType = 'html';
    public $trusted = true;
    public $userModifiers = array();
    public $userFunctions = array();

    /**
     * @param string $sel        the template selector
     * @param string $outputtype the type of output (html, text..) By default, it takes the response type
     * @param bool   $trusted    says if the template file is trusted or not
     */
    public function __construct($sel, $outputtype = '', $trusted = true)
    {
        if ($outputtype == '') {
            if (App::router()) {
                if (App::router()->response) {
                    $this->outputType = App::router()->response->getFormatType();
                } else {
                    $this->outputType = App::router()->request->defaultResponseType;
                }
            }
        } else {
            $this->outputType = $outputtype;
        }
        $this->trusted = $trusted;
        parent::__construct($sel);
    }

    /**
     * @throws Exception
     */
    protected function _createPath()
    {
        if (!App::isModuleEnabled($this->module)) {
            throw new Exception('jelix~errors.selector.module.unknown', $this->toString());
        }
        $config = App::config();
        $locale = $config->locale;
        $lpath = $locale.'/'.$this->resource;
        $flpath = '';
        $fallbackLocale = $config->fallbackLocale;
        if ($locale != $fallbackLocale && $fallbackLocale) {
            $flpath = $fallbackLocale.'/'.$this->resource;
        }

        $resolutionInCache = $config->compilation['sourceFileResolutionInCache'];

        if ($resolutionInCache) {
            $resolutionPath = App::tempPath('resolved/'.$this->module.'/'.$this->_dirname.$config->theme.'/'.$lpath.'.tpl');
            $resolutionCachePath = 'resolved/'.$this->module.'/'.$config->theme.'/'.$lpath;
            if (file_exists($resolutionPath)) {
                $this->_path = $resolutionPath;
                $this->_cachePrefix = $resolutionCachePath;

                return;
            }
            \jFile::createDir(dirname($resolutionPath));
        }

        $this->findPath($config, $lpath, $flpath);
        if ($resolutionInCache) {
            symlink($this->_path, $resolutionPath);
            $this->_path = $resolutionPath;
            $this->_cachePrefix = $resolutionCachePath;
        }
    }

    public function getCompiler()
    {
        return new TemplateCompiler();
    }

    protected function findPath($config, $lpath, $flpath)
    {
        $mpath = App::getModulePath($this->module).$this->_dirname;
        if ($config->theme != 'default') {
            if ($this->checkThemePath($config->theme, $lpath, $flpath, $mpath, $this->resource)) {
                return;
            }
        }

        if ($this->checkThemePath('default', $lpath, $flpath, $mpath, $this->resource)) {
            return;
        }

        // check if the template exists in the current module

        $this->_path = $mpath.$lpath.'.tpl';
        if (is_readable($this->_path)) {
            $this->_cachePrefix = 'modules/'.$this->module.'/'.$lpath;

            return;
        }

        if ($flpath) {
            $this->_path = $mpath.$flpath.'.tpl';
            if (is_readable($this->_path)) {
                $this->_cachePrefix = 'modules/'.$this->module.'/'.$flpath;

                return;
            }
        }

        $this->_path = $mpath.$this->resource.'.tpl';
        if (is_readable($this->_path)) {
            $this->_cachePrefix = 'modules/'.$this->module.'/'.$this->resource;

            return;
        }

        throw new Exception('jelix~errors.selector.invalid.target', array($this->toString(), 'template'));
    }

    protected function checkThemePath($theme, $lpath, $flpath, $mpath, $path)
    {
        $subDir = $theme.'/'.$this->module;
        if (file_exists(App::varPath('themes/'.$subDir))) {
            // check if there is a redefined template for the current theme & locale in var/theme
            $this->_path = App::varPath('themes/'.$subDir.'/'.$lpath.'.tpl');
            if (is_readable($this->_path)) {
                $this->_cachePrefix = 'var/themes/'.$subDir.'/'.$lpath;

                return true;
            }

            if ($flpath) {
                // check if there is a redefined template for the current theme & fallback locale in var/theme
                $this->_path = App::varPath('themes/'.$subDir.'/'.$flpath.'.tpl');
                if (is_readable($this->_path)) {
                    $this->_cachePrefix = 'var/themes/'.$subDir.'/'.$flpath;

                    return true;
                }
            }

            // check if there is a redefined template for the current theme in var/theme
            $this->_path = App::varPath('themes/'.$subDir.'/'.$path.'.tpl');
            if (is_readable($this->_path)) {
                $this->_cachePrefix = 'var/themes/'.$subDir.'/'.$path;

                return true;
            }
        }

        if (file_exists(App::appPath('app/themes/'.$subDir))) {
            // check if there is a redefined template for the current theme & locale in app/theme
            $this->_path = App::appPath('app/themes/'.$subDir.'/'.$lpath.'.tpl');
            if (is_readable($this->_path)) {
                $this->_cachePrefix = 'app/themes/'.$subDir.'/'.$lpath;

                return true;
            }

            if ($flpath) {
                // check if there is a redefined template for the current theme & fallback locale in app/theme
                $this->_path = App::appPath('app/themes/'.$subDir.'/'.$flpath.'.tpl');
                if (is_readable($this->_path)) {
                    $this->_cachePrefix = 'app/themes/'.$subDir.'/'.$flpath;

                    return true;
                }
            }

            // check if there is a redefined template for the current theme in app/theme
            $this->_path = App::appPath('app/themes/'.$subDir.'/'.$path.'.tpl');
            if (is_readable($this->_path)) {
                $this->_cachePrefix = 'app/themes/'.$subDir.'/'.$path;

                return true;
            }
        }

        $mpath .= 'themes/'.$theme;
        if (file_exists($mpath)) {
            // check if there is a redefined template for the current theme & locale in <module>/themes
            $this->_path = $mpath.'/'.$lpath.'.tpl';
            if (is_readable($this->_path)) {
                $this->_cachePrefix = 'modules/'.$this->module.'/themes/'.$theme.'/'.$lpath;

                return true;
            }

            if ($flpath) {
                // check if there is a redefined template for the current theme & fallback locale in <module>/themes
                $this->_path = $mpath.'/'.$flpath.'.tpl';
                if (is_readable($this->_path)) {
                    $this->_cachePrefix = 'modules/'.$this->module.'/themes/'.$theme.'/'.$flpath;

                    return true;
                }
            }

            // check if there is a redefined template for the current theme in <module>/themes
            $this->_path = $mpath.'/'.$path.'.tpl';
            if (is_readable($this->_path)) {
                $this->_cachePrefix = 'modules/'.$this->module.'/themes/'.$theme.'/'.$path;

                return true;
            }
        }

        return false;
    }

    protected function _createCachePath()
    {
        // don't share the same cache for all the possible dirs
        // in case of overload removal
        $this->_cachePath = App::tempPath('compiled/templates/'.$this->_cachePrefix.'_'.$this->outputType.($this->trusted ? '_t' : '').'_15'.$this->_cacheSuffix);
    }
}
