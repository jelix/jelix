<?php
/**
 * see Jelix/Core/Selector/SelectorInterface.php for documentation about selectors.
 *
 * @author      Laurent Jouanneau
 * @copyright   2005-2014 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Selector;

use Jelix\Core\App as App;

/**
 * base class for all selector concerning module files.
 *
 * General syntax for them : "module~resource".
 * Syntax of resource depend on the selector type.
 * module is optional.
 */
abstract class ModuleSelector implements SelectorInterface
{
    public $module;
    public $resource;

    protected $type = '_module';
    protected $_dirname = '';
    protected $_suffix = '';
    protected $_cacheSuffix = '.php';
    protected $_path;
    protected $_cachePath;
    protected $_compiler;
    protected $_compilerPath;
    protected $_useMultiSourceCompiler = false;

    public function __construct($sel)
    {
        if ($this->_scan_sel($sel)) {
            if ($this->module == '') {
                $this->module = App::getCurrentModule();
            }
            $this->_createPath();
            $this->_createCachePath();
        } else {
            throw new Exception('jelix~errors.selector.invalid.syntax', array($sel, $this->type));
        }
    }

    protected function _scan_sel($selStr)
    {
        if (preg_match('/^(([a-zA-Z0-9_\\.]+)~)?([a-zA-Z0-9_\\.]+)$/', $selStr, $m)) {
            if ($m[1] != '' && $m[2] != '') {
                $this->module = $m[2];
            } else {
                $this->module = '';
            }
            $this->resource = $m[3];

            return true;
        }

        return false;
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function getCompiledFilePath()
    {
        return $this->_cachePath;
    }

    public function getCompiler()
    {
        if ($this->_compiler == null) {
            return null;
        }
        $n = $this->_compiler;

        require_once $this->_compilerPath;
        $o = new $n();

        return $o;
    }

    public function useMultiSourceCompiler()
    {
        return $this->_useMultiSourceCompiler;
    }

    public function toString($full = false)
    {
        if ($full) {
            return $this->type.':'.$this->module.'~'.$this->resource;
        }

        return $this->module.'~'.$this->resource;
    }

    protected function _createPath()
    {
        if (!App::isModuleEnabled($this->module)) {
            throw new Exception('jelix~errors.selector.module.unknown', $this->toString(true));
        }
        $this->_path = App::getModulePath($this->module).$this->_dirname.$this->resource.$this->_suffix;
        if (!is_readable($this->_path)) {
            if ($this->type == 'loc') {
                throw new \Exception('(202) The file of the locale key "'.$this->toString().'" (lang '.$this->locale.') does not exist');
            }
            if ($this->toString() == 'jelix~errors.selector.invalid.target') {
                throw new \Exception("Jelix Panic ! don't find localization files to show you an other error message !");
            }

            throw new Exception('jelix~errors.selector.invalid.target', array($this->toString(), $this->type));
        }
    }

    protected function _createCachePath()
    {
        $this->_cachePath = App::tempPath('compiled/'.$this->_dirname.$this->module.'~'.$this->resource.$this->_cacheSuffix);
    }
}
