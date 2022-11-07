<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2008-2018 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Module\API;

/**
 * Trait for installer/configurator classes.
 *
 * @since 1.7
 */
class DatabaseHelpers
{
    /**
     * @var string the jDb profile for the component
     */
    protected $dbProfile = '';

    /**
     * @var \jDbConnection
     */
    private $_dbConn;

    /**
     * global setup.
     *
     * @var \Jelix\Installer\GlobalSetup
     */
    protected $globalSetup;

    public function __construct(\Jelix\Installer\GlobalSetup $setup)
    {
        $this->globalSetup = $setup;
    }

    /**
     * use the given database profile. check if this is an alias and use the
     * real db profile if this is the case.
     *
     * @param string $dbProfile the profile name
     */
    public function useDbProfile($dbProfile)
    {
        if ($dbProfile == '') {
            $dbProfile = 'default';
        }

        $this->dbProfile = $dbProfile;
        $this->_dbConn = null; // we force to retrieve a db connection
    }

    public function getDbProfile()
    {
        return $this->dbProfile;
    }

    /**
     * @return \jDbTools the tool class of jDb
     */
    public function dbTool()
    {
        return $this->dbConnection()->tools();
    }

    /**
     * @return \jDbConnection the connection to the database used for the module
     */
    public function dbConnection()
    {
        if (!$this->_dbConn) {
            $this->_dbConn = \jDb::getConnection($this->dbProfile);
        }

        return $this->_dbConn;
    }

    /**
     * @param string $profile the db profile
     *
     * @return string the name of the type of database
     */
    public function getDbType($profile = null)
    {
        if (!$profile) {
            $profile = $this->dbProfile;
        }
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
     * @param string $name          the name of the script
     * @param string $module        the module from which we should take the sql file. null for the current module
     * @param bool   $inTransaction indicate if queries should be executed inside a transaction
     *
     * @throws \Exception
     */
    public function execSQLScript($name, $module = null, $inTransaction = true)
    {
        $conn = $this->dbConnection();
        $tools = $this->dbTool();

        if ($module) {
            $conf = $this->globalSetup->getMainEntryPoint()->getConfigObj()->_modulesPathList;
            if (!isset($conf[$module])) {
                throw new \Exception('execSQLScript : invalid module name');
            }
            $path = $conf[$module];
        } else {
            $path = $this->globalSetup->getCurrentModulePath();
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
        } catch (\Exception $e) {
            if ($inTransaction) {
                $conn->rollback();
            }

            throw $e;
        }
    }

    /**
     * @param $selectorStr
     */
    public function createTableFromDao($selectorStr)
    {
        $daoMapper = new \jDaoDbMapper($this->dbProfile);
        $daoMapper->createTableFromDao($selectorStr);
    }

    /**
     * Insert data into a database, from a json file, using a DAO mapping.
     *
     * @param string     $relativeSourcePath name of the json file into the install directory
     * @param int        $option             one of jDbTools::IBD_* const
     * @param null|mixed $module
     *
     * @throws \Exception
     *
     * @return int number of records inserted/updated
     */
    public function insertDaoData($relativeSourcePath, $option, $module = null)
    {
        if ($module) {
            $conf = $this->globalSetup->getMainEntryPoint()->getModulesList();
            if (!isset($conf[$module])) {
                throw new \Exception('insertDaoData : invalid module name');
            }
            $path = $conf[$module];
        } else {
            $path = $this->globalSetup->getCurrentModulePath();
        }

        $file = $path.'install/'.$relativeSourcePath;
        $dataToInsert = json_decode(file_get_contents($file), true);
        if (!$dataToInsert) {
            throw new \Exception('Bad format for dao data file.');
        }
        if (is_object($dataToInsert)) {
            $dataToInsert = array($dataToInsert);
        }
        $daoMapper = new \jDaoDbMapper($this->dbProfile);
        $count = 0;
        foreach ($dataToInsert as $daoData) {
            if (!isset($daoData['dao'])
                || !isset($daoData['properties'])
                || !isset($daoData['data'])
            ) {
                throw new \Exception('Bad format for dao data file.');
            }
            $count += $daoMapper->insertDaoData(
                $daoData['dao'],
                $daoData['properties'],
                $daoData['data'],
                $option
            );
        }

        return $count;
    }
}
