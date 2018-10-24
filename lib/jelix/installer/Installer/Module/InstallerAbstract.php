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

    use HelpersTrait;

    /**
     * @var string the jDb profile for the component
     */
    protected $dbProfile = '';

    /**
     * @var string the default profile name for the component, if it exist. keep it to '' if not
     */
    protected $defaultDbProfile = '';


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


    /**
     * default config and main config combined
     * @return \Jelix\IniFile\IniModifierArray
     * @since 1.7
     */
    protected final function getConfigIni() {
        return $this->globalSetup->getAppConfigIni();
    }

    /**
     * the localconfig.ini.php file combined with main and default config
     * @return \Jelix\IniFile\IniModifierArray
     * @since 1.7
     */
    protected final function getLocalConfigIni() {
        $ini = $this->globalSetup->getAppConfigIni(true);
        $ini['local'] = $this->globalSetup->getLocalConfigIni();
        return $ini;
    }

    /**
     * the liveconfig.ini.php file combined with localconfig, main and default config
     * @return \Jelix\IniFile\IniModifierArray
     * @since 1.7
     */
    protected final function getLiveConfigIni() {
        return $this->globalSetup->getFullConfigIni(true);
    }

    /**
     * Point d'entrée principal de l'application (en général index.php)
     * @return \Jelix\Installer\EntryPoint
     */
    protected final function getMainEntryPoint() {
        return $this->globalSetup->getMainEntryPoint();
    }

    /**
     * List of entry points of the application
     *
     * @return \Jelix\Installer\EntryPoint[]
     */
    protected final function getEntryPointsList() {
        return $this->globalSetup->getEntryPointsList();
    }

    /**
     * @param $epId
     * @return \Jelix\Installer\EntryPoint
     */
    protected final function getEntryPointsById($epId) {
        return $this->globalSetup->getEntryPointById($epId);
    }



    /**
     * internal use
     * @param string $dbProfile the name of the current jdb profile. It will be replaced by $defaultDbProfile if it exists
     */
    public final function initDbProfile($dbProfile) {
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
    protected final function useDbProfile($dbProfile) {

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
    protected final function dbTool () {
        return $this->dbConnection()->tools();
    }

    /**
     * @return \jDbConnection  the connection to the database used for the module
     */
    protected final function dbConnection () {
        if (!$this->_dbConn)
            $this->_dbConn = \jDb::getConnection($this->dbProfile);
        return $this->_dbConn;
    }

    /**
     * @param string $profile the db profile
     * @return string the name of the type of database
     */
    protected final function getDbType($profile = null) {
        if (!$profile)
            $profile = $this->dbProfile;
        $conn = \jDb::getConnection($profile);
        return $conn->dbms;
    }

    /**
     * execute a sql script with the current profile.
     *
     * The name of the script should be store in install/$name.databasetype.sql
     * in the directory of the component. (replace databasetype by mysql, pgsql etc.)
     * You can however provide a script compatible with all databases, but then
     * you should indicate the full name of the script, with a .sql extension.
     *
     * @param string $name the name of the script
     * @param string $module the module from which we should take the sql file. null for the current module
     * @param boolean $inTransaction indicate if queries should be executed inside a transaction
     * @throws \Exception
     */
    final protected function execSQLScript ($name, $module = null, $inTransaction = true)
    {
        $conn = $this->dbConnection();
        $tools = $this->dbTool();

        if ($module) {
            $conf = $this->globalSetup->getMainEntryPoint()->getConfigObj()->_modulesPathList;
            if (!isset($conf[$module])) {
                throw new \Exception('execSQLScript : invalid module name');
            }
            $path = $conf[$module];
        }
        else {
            $path = $this->path;
        }

        $file = $path.'install/'.$name;
        if (substr($name, -4) != '.sql')
            $file .= '.'.$conn->dbms.'.sql';

        if ($inTransaction)
            $conn->beginTransaction();
        try {
            $tools->execSQLScript($file);
            if ($inTransaction) {
                $conn->commit();
            }
        }
        catch(\Exception $e) {
            if ($inTransaction)
                $conn->rollback();
            throw $e;
        }
    }


    /**
     * Insert data into a database, from a json file, using a DAO mapping
     *
     * @param string $relativeSourcePath name of the json file into the install directory
     * @param integer $option one of jDbTools::IBD_* const
     * @return integer number of records inserted/updated
     * @throws \Exception
     * @since 1.6.16
     */
    final protected function insertDaoData($relativeSourcePath, $option, $module = null) {

        if ($module) {
            $conf = $this->globalSetup->getMainEntryPoint()->getModulesList();
            if (!isset($conf[$module])) {
                throw new \Exception('insertDaoData : invalid module name');
            }
            $path = $conf[$module];
        }
        else {
            $path = $this->path;
        }

        $file = $path.'install/'.$relativeSourcePath;
        $dataToInsert = json_decode(file_get_contents($file), true);
        if (!$dataToInsert) {
            throw new \Exception("Bad format for dao data file.");
        }
        if (is_object($dataToInsert)) {
            $dataToInsert = array($dataToInsert);
        }
        $daoMapper = new \jDaoDbMapper($this->dbProfile);
        $count = 0;
        foreach($dataToInsert as $daoData) {
            if (!isset($daoData['dao']) ||
                !isset($daoData['properties']) ||
                !isset($daoData['data'])
            ) {
                throw new \Exception("Bad format for dao data file.");
            }
            $count += $daoMapper->insertDaoData($daoData['dao'],
                $daoData['properties'], $daoData['data'], $option);
        }
        return $count;
    }

}

