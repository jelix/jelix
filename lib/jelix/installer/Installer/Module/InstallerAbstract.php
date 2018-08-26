<?php
/**
* @author      Laurent Jouanneau
* @copyright   2008-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer\Module;

/**
 * Base class for installers and uninstallers
 *
 * @since 1.7
 */
abstract class InstallerAbstract {

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
     * @var \Jelix\Installer\GlobalSetup
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
     * @var \jDbConnection
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

    function setGlobalSetup(\Jelix\Installer\GlobalSetup $setup) {
        $this->globalSetup = $setup;
    }

    /**
     * default config and main config combined
     * @return \Jelix\IniFile\IniModifierArray
     * @since 1.7
     */
    protected function getConfigIni() {
        return $this->globalSetup->getConfigIni();
    }

    /**
     * the localconfig.ini.php file combined with main and default config
     * @return \Jelix\IniFile\IniModifierArray
     * @since 1.7
     */
    protected function getLocalConfigIni() {
        return $this->globalSetup->getLocalConfigIni();
    }

    /**
     * the liveconfig.ini.php file combined with localconfig, main and default config
     * @return \Jelix\IniFile\IniModifierArray
     * @since 1.7
     */
    protected function getLiveConfigIni() {
        return $this->globalSetup->getLiveConfigIni();
    }

    /**
     * Point d'entrée principal de l'application (en général index.php)
     * @return \Jelix\Installer\EntryPoint
     */
    protected function getMainEntryPoint() {
        return $this->globalSetup->getMainEntryPoint();
    }

    /**
     * List of entry points of the application
     *
     * @return \Jelix\Installer\EntryPoint[]
     */
    protected function getEntryPointsList() {
        return $this->globalSetup->getEntryPointsList();
    }

    /**
     * @param $epId
     * @return \Jelix\Installer\EntryPoint
     */
    protected function getEntryPointsById($epId) {
        return $this->globalSetup->getEntryPointById($epId);
    }

    protected function getProfilesIni() {
        return $this->globalSetup->getProfilesIni();
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
     * @return \jDbTools  the tool class of jDb
     */
    protected function dbTool () {
        return $this->dbConnection()->tools();
    }

    /**
     * @return \jDbConnection  the connection to the database used for the module
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


}

