<?php
/**
 * see jISelector.iface.php for documentation about selectors.
 *
 * @package     jelix
 * @subpackage  core_selector
 *
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 *
 * @copyright   2005-2019 Laurent Jouanneau, 2007 Loic Mathaud
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

use Jelix\Core\Profiles;

/**
 * Selector for dao file
 * syntax : "module~daoName".
 * file : daos/daoName.dao.xml.
 *
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorDao extends jSelectorModule
{
    protected $type = 'dao';

    public $profile;

    /**
     * the name of the jDb driver used for the connection.
     *
     * @var string
     */
    public $driver;

    /**
     * name of the database type used by the connection.
     */
    public $dbType;

    protected $_dirname = 'daos/';
    protected $_suffix = '.dao.xml';
    protected $_where;

    public function __construct($sel, $profile)
    {
        $p = Profiles::get('jdb', $profile);
        if ($p['driver'] == 'pdo') {
            $this->driver = substr($p['dsn'], 0, strpos($p['dsn'], ':'));
        } else {
            $this->driver = $p['driver'];
        }
        $this->dbType = $p['dbtype'];
        $this->_compiler = 'jDaoCompiler';
        $this->_compilerPath = JELIX_LIB_PATH.'dao/jDaoCompiler.class.php';
        parent::__construct($sel);
    }

    protected function _createPath()
    {
        if (!jApp::isModuleEnabled($this->module)) {
            throw new jExceptionSelector('jelix~errors.selector.module.unknown', $this->toString());
        }

        $resolutionInCache = jApp::config()->compilation['sourceFileResolutionInCache'];

        if ($resolutionInCache) {
            $resolutionPath = jApp::tempPath('resolved/'.$this->module.'/'.$this->_dirname.$this->resource.$this->_suffix);
            $resolutionCachePath = 'resolved/';
            if (file_exists($resolutionPath)) {
                $this->_path = $resolutionPath;
                $this->_where = $resolutionCachePath;

                return;
            }
            jFile::createDir(dirname($resolutionPath));
        }

        $this->findPath();

        if ($resolutionInCache) {
            symlink($this->_path, $resolutionPath);
            $this->_path = $resolutionPath;
            $this->_where = $resolutionCachePath;
        }
    }

    protected function findPath()
    {

        // check if the dao was redefined (overloaded) in var/
        $overloadedPath = jApp::varPath('overloads/'.$this->module.'/'.$this->_dirname.$this->resource.$this->_suffix);
        if (is_readable($overloadedPath)) {
            $this->_path = $overloadedPath;
            $this->_where = 'var/';

            return;
        }

        // check if the dao was redefined (overloaded) in app/
        $overloadedPath = jApp::appPath('app/overloads/'.$this->module.'/'.$this->_dirname.$this->resource.$this->_suffix);
        if (is_readable($overloadedPath)) {
            $this->_path = $overloadedPath;
            $this->_where = 'app/';

            return;
        }

        // else check if the module exists in the current module
        $this->_path = jApp::getModulePath($this->module).$this->_dirname.$this->resource.$this->_suffix;

        if (!is_readable($this->_path)) {
            throw new jExceptionSelector('jelix~errors.selector.invalid.target', array($this->toString(), 'dao'));
        }
        $this->_where = 'modules/';
    }

    protected function _createCachePath()
    {
        // don't share the same cache for all the possible dirs
        // in case of overload removal
        $this->_cachePath = jApp::tempPath('compiled/daos/'.$this->_where.$this->module.'/'.$this->resource.'~'.$this->dbType.'_15'.$this->_cacheSuffix);
    }

    public function getDaoClass()
    {
        return 'cDao_'.$this->module.'_Jx_'.$this->resource.'_Jx_'.$this->dbType;
    }

    public function getDaoRecordClass()
    {
        return 'cDaoRecord_'.$this->module.'_Jx_'.$this->resource.'_Jx_'.$this->dbType;
    }
}
