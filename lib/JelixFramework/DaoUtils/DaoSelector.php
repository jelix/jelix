<?php
/**
 * @package     jelix
 * @subpackage  core_selector
 *
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 *
 * @copyright   2005-2026 Laurent Jouanneau, 2007 Loic Mathaud
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\DaoUtils;

use Jelix\Core\App;
use Jelix\Core\Profiles;
use Jelix\Core\Selector\ModuleSelector;
use Jelix\Core\Selector\Exception;
use Jelix\Dao\DaoFileInterface2;

/**
 * Selector for dao file
 * syntax : "module~daoName".
 * file : daos/<daoName>.dao.xml.
 *
 */
class DaoSelector extends ModuleSelector implements DaoFileInterface2
{
    protected $type = 'dao';
    protected $_dirname = 'daos/';
    protected $_suffix = '.dao.xml';
    protected $_where;

    protected $escapedModule;
    protected $escapedName;

    /**
     * the name of the jDb driver used for the connection.
     *
     * @var string
     * @deprecated
     */
    public $driver;

    /**
     * name of the database type used by the connection.
     */
    public $dbType;

    protected $buildPath;

    public function __construct($sel, $profile)
    {
        $p = Profiles::get('jdb', $profile);
        $this->driver = $p['driver'];
        $this->dbType = $p['dbtype'];
        parent::__construct($sel);
        $this->escapedName = ucfirst($this->resource);
        $this->escapedModule = ucfirst($this->module);
        $this->buildPath =  App::varLibPath();
    }

    protected function _createPath()
    {
        if (!App::isModuleEnabled($this->module)) {
            throw new Exception('jelix~errors.selector.module.unknown', $this->toString());
        }

        // check if the dao was redefined (overloaded) in var/
        $overloadedPath = App::varPath('overloads/'.$this->module.'/'.$this->_dirname.$this->resource.$this->_suffix);
        if (is_readable($overloadedPath)) {
            $this->_path = $overloadedPath;
            $this->_where = 'var/';

            return;
        }

        // check if the dao was redefined (overloaded) in app/
        $overloadedPath = App::appPath('app/overloads/'.$this->module.'/'.$this->_dirname.$this->resource.$this->_suffix);
        if (is_readable($overloadedPath)) {
            $this->_path = $overloadedPath;
            $this->_where = 'app/';

            return;
        }

        // else check if the module exists in the current module
        $this->_path = App::getModulePath($this->module).$this->_dirname.$this->resource.$this->_suffix;

        if (!is_readable($this->_path)) {
            throw new Exception('jelix~errors.selector.invalid.target', array($this->toString(), 'dao'));
        }
        $this->_where = 'modules/';
    }

    protected function _createCachePath()
    {
        // nothing, useless
    }

    /**
     * A name that allow to identify easily the dao.
     *
     * @return string a filename, a URI or another identifier
     */
    function getName()
    {
        return $this->module . '~' . $this->resource;
    }

    /**
     * @return string
     * @deprecated use getCompiledFactoryClass() instead
     */
    public function getDaoClass()
    {
        return $this->getCompiledFactoryClass();
    }

    /**
     * @return string
     * @deprecated use getCompiledRecordClass() instead
     */
    public function getDaoRecordClass()
    {
        return $this->getCompiledRecordClass();
    }

    /**
     * @return string name of the factory class that should be used by the generator
     */
    function getCompiledFactoryClass()
    {
        return 'Jelix\\BuiltComponents\\Daos\\' . $this->escapedModule . '\\' . $this->escapedName . ucfirst($this->dbType) . 'Factory';
    }

    /**
     * @return string name of the record class that should be used by the generator
     */
    function getCompiledRecordClass()
    {
        return 'Jelix\\BuiltComponents\\Daos\\' . $this->escapedModule . '\\' . $this->escapedName . ucfirst($this->dbType) . 'Record';
    }

    public function getCompiledRecordFilePath()
    {
        return $this->buildPath.'/Daos/'.$this->escapedModule.'/'.$this->escapedName.ucfirst($this->dbType).'Record.php';
    }

    public function getCompiledFactoryFilePath()
    {
        return $this->buildPath.'/Daos/'.$this->escapedModule.'/'.$this->escapedName.ucfirst($this->dbType).'Factory.php';
    }
}
