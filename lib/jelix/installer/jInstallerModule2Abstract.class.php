<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2008-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * Base class for installers and uninstallers
 *
 * @package     jelix
 * @subpackage  installer
 * @since 1.7
 */
class jInstallerModule2Abstract {

    /**
     * @var string name of the component
     */
    protected $componentName;

    /**
     * @var string name of the installer
     */
    protected $name;

    /**
     * global setup
     * @var jInstallerGlobalSetup
     */
    protected $globalSetup;

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
     * parameters for the installer, indicated in the configuration file or
     * dynamically, by a launcher in a command line for instance.
     * @var array
     */
    protected $parameters = array();

    /**
     * @var jDbConnection
     */
    private $_dbConn = null;

    /**
     * @param string $componentName name of the component
     * @param string $name name of the installer
     * @param string $path the component path
     * @param string $version version of the component
     * @param boolean $installWholeApp deprecated
     */
    function __construct ($componentName, $name, $path, $version, $installWholeApp=true) {
        $this->path = $path;
        $this->version = $version;
        $this->name = $name;
        $this->componentName = $componentName;
    }

    function getName() {
        return $this->name;
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

    function setGlobalSetup(jInstallerGlobalSetup $setup) {
        $this->globalSetup = $setup;
    }

    /**
     * default config and main config combined
     * @return \Jelix\IniFile\IniModifierArray
     * @since 1.7
     */
    public function getConfigIni() {
        return $this->globalSetup->getConfigIni();
    }

    /**
     * the localconfig.ini.php file combined with main and default config
     * @return \Jelix\IniFile\IniModifierArray
     * @since 1.7
     */
    public function getLocalConfigIni() {
        return $this->globalSetup->getLocalConfigIni();
    }

    /**
     * the liveconfig.ini.php file combined with localconfig, main and default config
     * @return \Jelix\IniFile\IniModifierArray
     * @since 1.7
     */
    public function getLiveConfigIni() {
        return $this->globalSetup->getLiveConfigIni();
    }

    /**
     * Point d'entrée principal de l'application (en général index.php)
     * @return jInstallerEntryPoint2
     */
    public function getMainEntryPoint() {
        return $this->globalSetup->getMainEntryPoint();
    }

    /**
     * List of entry points of the application
     *
     * @return jInstallerEntryPoint2[]
     */
    public function getEntryPointsList() {
        return $this->globalSetup->getEntryPointsList();
    }

    /**
     * @param $epId
     * @return jInstallerEntryPoint2
     */
    protected function getEntryPointsById($epId) {
        return $this->globalSetup->getEntryPointById($epId);
    }

    /**
     * internal use
     * @param string $dbProfile the name of the current jdb profile. It will be replaced by $defaultDbProfile if it exists
     */
    public function initDbProfile($dbProfile) {
        if ($this->defaultDbProfile != '') {
            $this->useDbProfile($this->defaultDbProfile);
        }
        else
            $this->useDbProfile($dbProfile);
    }

    /**
     * use the given database profile. check if this is an alias and use the
     * real db profile if this is the case.
     * @param string $dbProfile the profile name
     */
    protected function useDbProfile($dbProfile) {

        if ($dbProfile == '')
            $dbProfile = 'default';

        $this->dbProfile = $dbProfile;

        // we check if it is an alias
        $profilesIni = $this->globalSetup->getProfilesIni();
        $alias = $profilesIni->getValue($dbProfile, 'jdb');
        if ($alias) {
            $this->dbProfile = $alias;
        }

        $this->_dbConn = null; // we force to retrieve a db connection
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
        $conn = jDb::getConnection($profile);
        return $conn->dbms;
    }

    protected function expandPath($path) {
        if (strpos($path, 'www:') === 0)
            $path = str_replace('www:', jApp::wwwPath(), $path);
        elseif (strpos($path, 'jelixwww:') === 0) {
            $p = $this->globalSetup->getConfigIni()->getValue('jelixWWWPath','urlengine');
            if (substr($p, -1) != '/') {
                $p .= '/';
            }
            $path = str_replace('jelixwww:', jApp::wwwPath($p), $path);
        }
        elseif (strpos($path, 'varconfig:') === 0) {
            $path = str_replace('varconfig:', jApp::varConfigPath(), $path);
        }
        elseif (strpos($path, 'appconfig:') === 0) {
            $path = str_replace('appconfig:', jApp::appConfigPath(), $path);
        }
        elseif (strpos($path, 'epconfig:') === 0) {
            throw new \Exception("'epconfig:' alias is no more supported in path");
        }
        elseif (strpos($path, 'config:') === 0) {
            throw new \Exception("'config:' alias is no more supported in path");
        }
        return $path;
    }

    /**
     * return the section name of configuration of a plugin for the coordinator
     * or the IniModifier for the configuration file of the plugin if it exists.
     * @param \Jelix\IniFile\IniModifier $config  the global configuration content
     * @param string $pluginName
     * @return array|null null if plugin is unknown, else array($iniModifier, $section)
     * @throws Exception when the configuration filename is not found
     */
    public function getCoordPluginConf(\Jelix\IniFile\IniModifierInterface $config, $pluginName) {
        $conf = $config->getValue($pluginName, 'coordplugins');
        if (!$conf) {
            return null;
        }
        if ($conf == '1') {
            $pluginConf = $config->getValues($pluginName);
            if ($pluginConf) {
                return array($config, $pluginName);
            }
            else {
                // old section naming. deprecated
                $pluginConf = $config->getValues('coordplugin_' . $pluginName);
                if ($pluginConf) {
                    return array($config, 'coordplugin_' . $pluginName);
                }
            }
            return null;
        }
        // the configuration value is a filename
        $confpath = jApp::appConfigPath($conf);
        if (!file_exists($confpath)) {
            $confpath = jApp::varConfigPath($conf);
            if (!file_exists($confpath)) {
                return null;
            }
        }
        return array(new \Jelix\IniFile\IniModifier($confpath), 0);
    }
}

