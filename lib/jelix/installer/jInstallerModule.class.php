<?php
/**
 * @package     jelix
 * @subpackage  installer
 *
 * @author      Laurent Jouanneau
 * @copyright   2008-2016 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
require_once JELIX_LIB_PATH.'installer/jIInstallerComponent.iface.php';

/**
 * A class that does processing to configure and install a module into
 * an application. A module should have a class that inherits from it
 * in order to configure itself into the application.
 *
 * @package     jelix
 * @subpackage  installer
 *
 * @since 1.2
 * @deprecated
 */
class jInstallerModule implements jIInstallerComponent
{
    /**
     * Called before the installation of all other modules
     * (dependents modules or the whole application).
     * Here, you should check if the module can be installed or not.
     *
     * @throws Exception if the module cannot be installed
     */
    public function preInstall()
    {
    }

    /**
     * should configure the module, install table into the database etc..
     * If an error occurs during the installation, you are responsible
     * to cancel/revert all things the method did before the error.
     *
     * @throws Exception if an error occurs during the installation
     */
    public function install()
    {
    }

    /**
     * Redefine this method if you do some additionnal process after the installation of
     * all other modules (dependents modules or the whole application).
     *
     * @throws Exception if an error occurs during the post installation
     */
    public function postInstall()
    {
    }

    /**
     * Called before the uninstallation of all other modules
     * (dependents modules or the whole application).
     * Here, you should check if the module can be uninstalled or not.
     *
     * @throws Exception if the module cannot be uninstalled
     */
    public function preUninstall()
    {
    }

    /**
     * should remove static files. Probably remove some data if the user is agree etc...
     *
     * @throws Exception if an error occurs during the install
     */
    public function uninstall()
    {
    }

    /**
     * @throws Exception if an error occurs during the install
     */
    public function postUninstall()
    {
    }

    /**
     * @var string name of the component
     */
    public $componentName;

    /**
     * @var string name of the installer
     */
    public $name;

    /**
     * the versions for which the installer should be called.
     * Useful for an upgrade which target multiple branches of a project.
     * Put the version for multiple branches. The installer will be called
     * only once, for the needed version.
     * If you don't fill it, the name of the class file should contain the
     * target version (deprecated behavior though).
     *
     * @var array list of version by asc order
     *
     * @since 1.2.6
     */
    public $targetVersions = array();

    /**
     * @var string the date of the release of the update. format: yyyy-mm-dd hh:ii
     *
     * @since 1.2.6
     */
    public $date = '';

    /**
     * @var string the version for which the installer is called
     */
    public $version = '0';

    /**
     * combination between mainconfig.ini.php (master) and entrypoint config (overrider).
     *
     * @var \Jelix\IniFile\MultiIniModifier
     *
     * @deprecated use entryPoint methods to access to different configuration files
     */
    protected $config;

    /**
     * the entry point property on which the installer is called.
     *
     * @var jInstallerEntryPoint
     */
    protected $entryPoint;

    /**
     * The path of the module.
     *
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
     * @var bool true if this is an installation for the whole application.
     *           false if this is an installation in an
     *           already installed application. Always False for upgraders.
     *
     * @deprecated
     */
    protected $installWholeApp = false;

    /**
     * parameters for the installer, indicated in the configuration file or
     * dynamically, by a launcher in a command line for instance.
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * list of new entrypoints.
     *
     * @var array keys are ep id, value are array with 'file', 'config', 'type' keys
     */
    private $newEntrypoints = array();

    /**
     * @param string $componentName   name of the component
     * @param string $name            name of the installer
     * @param string $path            the component path
     * @param string $version         version of the component
     * @param bool   $installWholeApp true if the installation is during the whole app installation
     *                                false if it is only few modules and this module. deprecated.
     */
    public function __construct($componentName, $name, $path, $version, $installWholeApp = false)
    {
        $this->path = $path;
        $this->version = $version;
        $this->name = $name;
        $this->componentName = $componentName;
        $this->installWholeApp = $installWholeApp;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function getParameter($name)
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }

        return null;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTargetVersions()
    {
        return $this->targetVersions;
    }

    public function setTargetVersions($versions)
    {
        $this->targetVersions = $versions;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @var jDbConnection
     */
    private $_dbConn;

    /**
     * is called to indicate that the installer will be called for the given
     * configuration, entry point and db profile.
     *
     * @param jInstallerEntryPoint $ep        the entry point
     * @param string               $dbProfile the name of the current jdb profile. It will be replaced by $defaultDbProfile if it exists
     * @param array                $contexts  list of contexts already executed
     */
    public function setEntryPoint(jInstallerEntryPoint $ep, $dbProfile)
    {
        $this->entryPoint = $ep;
        $this->config = $ep->configIni;
        $this->initDbProfile($dbProfile);
        $this->newEntrypoints = array();
    }

    /**
     * internal use.
     *
     * @param string $dbProfile the name of the current jdb profile. It will be replaced by $defaultDbProfile if it exists
     */
    public function initDbProfile($dbProfile)
    {
        if ($this->defaultDbProfile != '') {
            $this->useDbProfile($this->defaultDbProfile);
        } else {
            $this->useDbProfile($dbProfile);
        }
    }

    /**
     * use the given database profile. check if this is an alias and use the
     * real db profiel if this is the case.
     *
     * @param string $dbProfile the profile name
     */
    protected function useDbProfile($dbProfile)
    {
        if ($dbProfile == '') {
            $dbProfile = 'default';
        }

        $this->dbProfile = $dbProfile;

        // we check if it is an alias
        if (file_exists(jApp::varConfigPath('profiles.ini.php'))) {
            $dbprofiles = parse_ini_file(jApp::varConfigPath('profiles.ini.php'), true, INI_SCANNER_TYPED);
            if (isset($dbprofiles['jdb'][$dbProfile])) {
                $this->dbProfile = $dbprofiles['jdb'][$dbProfile];
            }
        }

        $this->_dbConn = null; // we force to retrieve a db connection
    }

    protected $contextId = array();

    protected $newContextId = array();

    /**
     * @param array $contexts list of contexts already executed
     */
    public function setContext($contexts)
    {
        $this->contextId = $contexts;
        $this->newContextId = array();
    }

    /**
     * @param mixed $contextId
     */
    protected function firstExec($contextId)
    {
        if (in_array($contextId, $this->contextId)) {
            return false;
        }

        if (!in_array($contextId, $this->newContextId)) {
            $this->newContextId[] = $contextId;
        }

        return true;
    }

    /**
     * @param mixed $profile
     */
    protected function firstDbExec($profile = '')
    {
        if ($profile == '') {
            $profile = $this->dbProfile;
        }

        return $this->firstExec('db:'.$profile);
    }

    /**
     * @param mixed $config
     */
    protected function firstConfExec($config = '')
    {
        if ($config == '') {
            $config = $this->entryPoint->getConfigFile();
        }

        return $this->firstExec('cf:'.$config);
    }

    public function getContexts()
    {
        return array_unique(array_merge($this->contextId, $this->newContextId));
    }

    /**
     * @return jDbTools the tool class of jDb
     */
    protected function dbTool()
    {
        return $this->dbConnection()->tools();
    }

    /**
     * @return jDbConnection the connection to the database used for the module
     */
    protected function dbConnection()
    {
        if (!$this->_dbConn) {
            $this->_dbConn = jDb::getConnection($this->dbProfile);
        }

        return $this->_dbConn;
    }

    /**
     * @param string $profile the db profile
     *
     * @return string the name of the type of database
     */
    protected function getDbType($profile = null)
    {
        if (!$profile) {
            $profile = $this->dbProfile;
        }
        $conn = jDb::getConnection($profile);

        return $conn->dbms;
    }

    /**
     * import a sql script into the current profile.
     *
     * The name of the script should be store in install/$name.databasetype.sql
     * in the directory of the component. (replace databasetype by mysql, pgsql etc.)
     * You can however provide a script compatible with all databases, but then
     * you should indicate the full name of the script, with a .sql extension.
     *
     * @param string $name          the name of the script
     * @param string $module        the module from which we should take the sql file. null for the current module
     * @param bool   $inTransaction indicate if queries should be executed inside a transaction
     *
     * @throws Exception
     */
    final protected function execSQLScript($name, $module = null, $inTransaction = true)
    {
        $conn = $this->dbConnection();
        $tools = $this->dbTool();

        if ($module) {
            $conf = $this->entryPoint->getConfigObj()->_modulesPathList;
            if (!isset($conf[$module])) {
                throw new Exception('execSQLScript : invalid module name');
            }
            $path = $conf[$module];
        } else {
            $path = $this->path;
        }
        $file = $path.'install/'.$name;
        if (substr($name, -4) != '.sql') {
            $file .= '.'.$conn->dbms.'.sql';
        }

        if ($inTransaction) {
            $conn->beginTransaction();
        }

        try {
            $tools->execSQLScript($file);
            if ($inTransaction) {
                $conn->commit();
            }
        } catch (Exception $e) {
            if ($inTransaction) {
                $conn->rollback();
            }

            throw $e;
        }
    }

    /**
     * copy the whole content of a directory existing in the install/ directory
     * of the component, to the given directory.
     *
     * @param string $relativeSourcePath relative path to the install/ directory of the component
     * @param string $targetPath         the full path where to copy the content
     * @param mixed  $overwrite
     */
    final protected function copyDirectoryContent($relativeSourcePath, $targetPath, $overwrite = false)
    {
        $targetPath = $this->expandPath($targetPath);
        \Jelix\FileUtilities\Directory::copy($this->path.'install/'.$relativeSourcePath, $targetPath, $overwrite);
    }

    /**
     * copy a file from the install/ directory to an other.
     *
     * @param string $relativeSourcePath relative path to the install/ directory of the file to copy
     * @param string $targetPath         the full path where to copy the file
     * @param mixed  $overwrite
     */
    final protected function copyFile($relativeSourcePath, $targetPath, $overwrite = false)
    {
        $targetPath = $this->expandPath($targetPath);
        if (!$overwrite && file_exists($targetPath)) {
            return;
        }
        $dir = dirname($targetPath);
        jFile::createDir($dir);
        copy($this->path.'install/'.$relativeSourcePath, $targetPath);
    }

    protected function expandPath($path)
    {
        if (strpos($path, 'www:') === 0) {
            $path = str_replace('www:', jApp::wwwPath(), $path);
        } elseif (strpos($path, 'jelixwww:') === 0) {
            $p = $this->entryPoint->getEpConfigIni()->getValue('jelixWWWPath', 'urlengine');
            if (substr($p, -1) != '/') {
                $p .= '/';
            }
            $path = str_replace('jelixwww:', jApp::wwwPath($p), $path);
        } elseif (strpos($path, 'varconfig:') === 0) {
            $path = str_replace('varconfig:', jApp::varConfigPath(), $path);
        } elseif (strpos($path, 'appconfig:') === 0) {
            $path = str_replace('appconfig:', jApp::appSystemPath(), $path);
        } elseif (strpos($path, 'appsystem:') === 0) {
            $path = str_replace('appsystem:', jApp::appSystemPath(), $path);
        } elseif (strpos($path, 'epconfig:') === 0) {
            $p = dirname(jApp::appSystemPath($this->entryPoint->getConfigFile()));
            $path = str_replace('epconfig:', $p.'/', $path);
        } elseif (strpos($path, 'config:') === 0) {
            $path = str_replace('config:', jApp::varConfigPath(), $path);
        }

        return $path;
    }

    /**
     * declare a new db profile. if the content of the section is not given,
     * it will declare an alias to the default profile.
     *
     * @param string            $name           the name of the new section/alias
     * @param null|array|string $sectionContent the content of the new section, or null
     *                                          to create an alias
     * @param bool              $force          true:erase the existing profile
     *
     * @return bool true if the ini file has been changed
     */
    protected function declareDbProfile($name, $sectionContent = null, $force = true)
    {
        $profiles = new \Jelix\IniFile\IniModifier(jApp::varConfigPath('profiles.ini.php'));
        if ($sectionContent == null) {
            if (!$profiles->isSection('jdb:'.$name)) {
                // no section
                if ($profiles->getValue($name, 'jdb') && !$force) {
                    // already a name
                    return false;
                }
            } elseif ($force) {
                // existing section, and no content provided : we erase the section
                // and add an alias
                $profiles->removeValue('', 'jdb:'.$name);
            } else {
                return false;
            }
            $default = $profiles->getValue('default', 'jdb');
            if ($default) {
                $profiles->setValue($name, $default, 'jdb');
            } else { // default is a section
                $profiles->setValue($name, 'default', 'jdb');
            }
        } else {
            if ($profiles->getValue($name, 'jdb') !== null) {
                if (!$force) {
                    return false;
                }
                $profiles->removeValue($name, 'jdb');
            }
            if (is_array($sectionContent)) {
                foreach ($sectionContent as $k => $v) {
                    if ($force || !$profiles->getValue($k, 'jdb:'.$name)) {
                        $profiles->setValue($k, $v, 'jdb:'.$name);
                    }
                }
            } else {
                $profile = $profiles->getValue($sectionContent, 'jdb');
                if ($profile !== null) {
                    $profiles->setValue($name, $profile, 'jdb');
                } else {
                    $profiles->setValue($name, $sectionContent, 'jdb');
                }
            }
        }
        $profiles->save();
        jProfiles::clear();

        return true;
    }

    /**
     * Before Jelix 1.7, it allowed to declare a plugins directory.
     *
     * Starting from 1.7, it does nothing as plugins path are declared
     * at runtime.
     * This method is still here to avoid PHP errors
     *
     * @param string $path a path. it could contains aliases like 'app:', 'lib:' or 'module:'
     *
     * @deprecated
     */
    public function declarePluginsPath($path)
    {
        // it does nothing
    }

    /**
     * @param string $entryPointFile      path to the entrypoint file to copy, from the install directory
     * @param string $configurationFile   path to the configuration file of the entrypoint to copy, from the install directory
     * @param string $targetConfigDirName directory name into var/config where to copy the configuration
     *                                    file. by default, the directory name is the entrypoint name.
     * @param string $type                type of the entrypoint
     */
    public function createEntryPoint($entryPointFile, $configurationFile, $targetConfigDirName = '', $type = 'classic')
    {
        $entryPointFileName = basename($entryPointFile);
        $entryPointId = str_replace('.php', '', $entryPointFileName);
        $configurationFileName = basename($configurationFile);
        if ($targetConfigDirName == '') {
            $targetConfigDirName = $entryPointId;
        }

        if ($this->firstExec('ep:'.$entryPointFileName)) {

            // copy the entrypoint and its configuration
            $newEpPath = jApp::wwwPath($entryPointFileName);

            $this->copyFile($entryPointFile, $newEpPath, true);
            $this->copyFile($configurationFile, jApp::varConfigPath($targetConfigDirName.'/'.$configurationFileName), false);

            $this->newEntrypoints[$entryPointId] = array(
                'file' => $entryPointFileName,
                'config' => $targetConfigDirName.'/'.$configurationFileName,
                'type' => $type,
            );

            // change the path to application.init.php into the entrypoint
            // depending of the application, the path of www/ is not always at the same place, relatively to
            // application.init.php
            $relativePath = \Jelix\FileUtilities\Path::shortestPath(jApp::wwwPath(), jApp::appPath());

            $epCode = file_get_contents($newEpPath);
            $epCode = preg_replace('#(require\s*\(?\s*[\'"])(.*)(application\.init\.php[\'"])#m', '\\1'.$relativePath.'/\\3', $epCode);
            file_put_contents($newEpPath, $epCode);
        }
    }

    /**
     * @return array
     */
    public function getNewEntrypoints()
    {
        return $this->newEntrypoints;
    }
}
