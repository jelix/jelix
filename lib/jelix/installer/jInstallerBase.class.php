<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2009-2011 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* base class for installers
* @package     jelix
* @subpackage  installer
* @since 1.2
*/
abstract class jInstallerBase {

    /**
     * @var string name of the component
     */
    public $componentName;

    /**
     * @var string name of the installer
     */
    public $name;

    /**
     * @var string the version of the component
     */
    public $version = '0';

    /**
     * default configuration of the application
     * @var jIniMultiFilesModifier
     */
    public $config;
    
    /**
     * the entry point property on which the installer is called
     * @var jInstallerEntryPoint
     */
    public $entryPoint;
    
    /**
     * The path of the module
     * @var string
     */
    protected $path;

    /**
     * @var string the jDb profile for the component
     */
    protected $dbProfile = '';

    /**
     * @var string the default profile name for the component, if it exist. keep it to '' if not
     */
    protected $defaultDbProfile = '';

    /**
     * @var boolean true if this is an installation for the whole application.
     *              false if this is an installation in an
     *              already installed application. Always False for upgraders.
     */
    protected $installWholeApp = false;

    /**
     * parameters for the installer, indicated in the configuration file or
     * dynamically, by a launcher in a command line for instance.
     * @var array
     */
    protected $parameters = array();

    /**
     * @param string $componentName name of the component
     * @param string $name name of the installer
     * @param string $path the component path
     * @param string $version version of the component
     * @param boolean $installWholeApp true if the installation is during the whole app installation
     *                                 false if it is only few modules and this module
     */
    function __construct ($componentName, $name, $path, $version, $installWholeApp = false) {
        $this->path = $path;
        $this->version = $version;
        $this->name = $name;
        $this->componentName = $componentName;
        $this->installWholeApp = $installWholeApp;
    }

    function setParameters($parameters) {
        $this->parameters = $parameters;
    }

    function getParameter($name) {
        if (isset($this->parameters[$name]))
            return $this->parameters[$name];
        else
            return null;
    }

    /**
     * @var jDbTools
     */
    private $_dbTool = null;

    /**
     * @var jDbConnection
     */
    private $_dbConn = null;

    /**
     * is called to indicate that the installer will be called for the given
     * configuration, entry point and db profile.
     * @param jInstallerEntryPoint $ep the entry point
     * @param jIniMultiFilesModifier $config the configuration of the entry point
     * @param string $dbProfile the name of the current jdb profile. It will be replaced by $defaultDbProfile if it exists
     * @param array $contexts  list of contexts already executed
     */
    public function setEntryPoint($ep, $config, $dbProfile, $contexts) {
        $this->config = $config;
        $this->entryPoint = $ep;
        $this->contextId = $contexts;
        $this->newContextId = array();

        if ($this->defaultDbProfile != '') {
            $this->useDbProfile($this->defaultDbProfile);
        }
        else
            $this->useDbProfile($dbProfile);
    }

    /**
     * use the given database profile. check if this is an alias and use the
     * real db profiel if this is the case.
     * @param string $dbProfile the profile name
     */
    protected function useDbProfile($dbProfile) {

        if ($dbProfile == '')
            $dbProfile = 'default';

        $this->dbProfile = $dbProfile;

        $dbProfilesFile = $this->config->getValue('dbProfils');
        if ($dbProfilesFile == '')
            $dbProfilesFile = 'dbprofils.ini.php';

        if (file_exists(JELIX_APP_CONFIG_PATH.$dbProfilesFile)) {
            $dbprofiles = parse_ini_file(JELIX_APP_CONFIG_PATH.$dbProfilesFile);
            // let's resolve the db profile
            if (isset($dbprofiles[$dbProfile]) && is_string($dbprofiles[$dbProfile]))
                $this->dbProfile = $dbprofiles[$dbProfile];
        }

        $this->_dbConn = null; // we force to retrieve a db connection
    }

    protected $contextId = array();

    protected $newContextId = array();

    /**
     *
     */
    protected function firstExec($contextId) {
        if (in_array($contextId, $this->contextId)) {
            return false;
        }

        if (!in_array($contextId, $this->newContextId)) {
            $this->newContextId[] = $contextId;
        }
        return true;
    }

    /**
     *
     */
    protected function firstDbExec($profile = '') {
        if ($profile == '')
            $profile = $this->dbProfile;
        return $this->firstExec('db:'.$profile);
    }

    /**
     *
     */
    protected function firstConfExec($config = '') {
        if ($config == '')
            $config = $this->entryPoint->configFile;
        return $this->firstExec('cf:'.$config);
    }

    /**
     *
     */
    public function getContexts() {
        return array_unique(array_merge($this->contextId, $this->newContextId));
    }

    /**
     * @return jDbTools  the tool class of jDb
     */
    protected function dbTool () {
        return $this->dbConnection()->tools();
    }

    /**
     * @return jDbConnection  the connection to the database used for the module
     */
    protected function dbConnection () {
        if (!$this->_dbConn)
            $this->_dbConn = jDb::getConnection($this->dbProfile);
        return $this->_dbConn;
    }

    /**
     * @param string $profile the db profile
     * @return string the name of the type of database
     */
    protected function getDbType($profile = null) {
        if (!$profile)
            $profile = $this->dbProfile;
        $p = jDb::getProfile ($profile);
        $driver = $p['driver'];
        if ($driver == 'pdo') {
            preg_match('/^(\w+)\:.*$/',$p['dsn'], $m);
            $driver = $m[1];
        }
        return $driver;
    }


    /**
     * import a sql script into the current profile.
     *
     * The name of the script should be store in install/$name.databasetype.sql
     * in the directory of the component. (replace databasetype by mysql, pgsql etc.)
     * You can however provide a script compatible with all databases, but then
     * you should indicate the full name of the script, with a .sql extension.
     * 
     * @param string $name the name of the script
     * @param string $module the module from which we should take the sql file. null for the current module
     * @param boolean $inTransaction indicate if queries should be executed inside a transaction
     */
    final protected function execSQLScript ($name, $module = null, $inTransaction = true) {

        $tools = $this->dbTool();

        $driver = $this->getDbType($this->dbProfile);

        if ($module) {
            $conf = $this->entryPoint->config->_modulesPathList;
            if (!isset($conf[$module])) {
                throw new Exception('execSQLScript : invalid module name');
            }
            $path = $conf[$module];
        }
        else {
            $path = $this->path;
        }
        $file = $path.'install/'.$name;
        if (substr($name, -4) != '.sql')
            $file .= '.'.$driver.'.sql';

        if ($inTransaction)
            $this->dbConnection()->beginTransaction();
        try {
            $tools->execSQLScript($file);
            if ($inTransaction) {
                $this->dbConnection()->commit();
            }
        }
        catch(Exception $e) {
            if ($inTransaction)
                $this->dbConnection()->rollback();
            throw $e;
        }
    }

    /**
     * copy the whole content of a directory existing in the install/ directory
     * of the component, to the given directory
     * @param string $relativeSourcePath relative path to the install/ directory of the component
     * @param string $targetPath the full path where to copy the content
     */
    final protected function copyDirectoryContent($relativeSourcePath, $targetPath, $overwrite = false) {
        $targetPath = $this->expandPath($targetPath);
        $this->_copyDirectoryContent ($this->path.'install/'.$relativeSourcePath, $targetPath, $overwrite);
    }

    /**
     * private function which copy the content of a directory to an other
     *
     * @param string $sourcePath 
     * @param string $targetPath
     */
    private function _copyDirectoryContent($sourcePath, $targetPath, $overwrite) {
        jFile::createDir($targetPath);
        $dir = new DirectoryIterator($sourcePath);
        foreach ($dir as $dirContent) {
            if ($dirContent->isFile()) {
                $p = $targetPath.substr($dirContent->getPathName(), strlen($dirContent->getPath()));
                if ($overwrite || !file_exists($p))
                    copy($dirContent->getPathName(), $p);
            } else {
                if (!$dirContent->isDot() && $dirContent->isDir()) {
                    $newTarget = $targetPath.substr($dirContent->getPathName(), strlen($dirContent->getPath()));
                    $this->_copyDirectoryContent($dirContent->getPathName(),$newTarget, $overwrite);
                }
            }
        }
    }


    /**
     * copy a file from the install/ directory to an other
     * @param string $relativeSourcePath relative path to the install/ directory of the file to copy
     * @param string $targetPath the full path where to copy the file
     */
    final protected function copyFile($relativeSourcePath, $targetPath, $overwrite = false) {
        $targetPath = $this->expandPath($targetPath);
        if (!$overwrite && file_exists($targetPath))
            return;
        $dir = dirname($targetPath);
        jFile::createDir($dir);
        copy ($this->path.'install/'.$relativeSourcePath, $targetPath);
    }

    protected function expandPath($path) {
         if (strpos($path, 'www:') === 0)
            $path = str_replace('www:', JELIX_APP_WWW_PATH, $path);
        elseif (strpos($path, 'jelixwww:') === 0) {
            $p = $this->config->getValue('jelixWWWPath','urlengine');
            if (substr($p, -1) != '/')
                $p.='/';
            $path = str_replace('jelixwww:', JELIX_APP_WWW_PATH.$p, $path);
        }
        elseif (strpos($path, 'config:') === 0) {
            $path = str_replace('config:', JELIX_APP_CONFIG_PATH, $path);
        }
        elseif (strpos($path, 'epconfig:') === 0) {
            $p = dirname(JELIX_APP_CONFIG_PATH.$this->entryPoint->configFile);
            $path = str_replace('epconfig:', $p.'/', $path);
        }
        return $path;
    }

    /**
     * declare a new db profile. if the content of the section is not given,
     * it will declare an alias to the default profile
     * @param string $name  the name of the new section/alias
     * @param null|string|array  $sectionContent the content of the new section, or null
     *     to create an alias.
     * @param boolean $force true:erase the existing profile
     * @return boolean true if the ini file has been changed
     */
    protected function declareDbProfile($name, $sectionContent = null, $force = true ) {
        $dbProfilesFile = $this->config->getValue('dbProfils');
        if ($dbProfilesFile == '')
            $dbProfilesFile = 'dbprofils.ini.php';
        $dbprofiles = new jIniFileModifier(JELIX_APP_CONFIG_PATH.$dbProfilesFile);
        if ($sectionContent == null) {
            if (!$dbprofiles->isSection($name)) {
                // no section
                if ($dbprofiles->getValue($name) && !$force) {
                    // already a name
                    return false;
                }
            }
            else if ($force) {
                // existing section, and no content provided : we erase the section
                // and add an alias
                $dbprofiles->removeValue('', $name);
            }
            else {
                return false;
            }
            $default = $dbprofiles->getValue('default');
            if($default) {
                $dbprofiles->setValue($name, $default);
            }
            else // default is a section
                $dbprofiles->setValue($name, 'default');
        }
        else {
            if ($dbprofiles->getValue($name) !== null) {
                if (!$force)
                    return false;
                $dbprofiles->removeValue($name);
            }
            if (is_array($sectionContent)) {
                foreach($sectionContent as $k=>$v) {
                    $dbprofiles->setValue($k,$v, $name);
                }
            }
            else {
                $profile = $dbprofiles->getValue($sectionContent);
                if ($profile !== null) {
                    $dbprofiles->setValue($name, $profile);
                }
                else
                    $dbprofiles->setValue($name, $sectionContent);
            }
        }
        
        $dbprofiles->save();
        jDb::clearProfiles();
        return true;
    }
}
