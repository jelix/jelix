<?php

/**
 * @author      Laurent Jouanneau
 * @copyright   2008-2026 Laurent Jouanneau
 *
 * @see        http://jelix.org
 *
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Module\API;

use Jelix\Dao\DbMapper;
use Jelix\DaoUtils\DaoContext;
use Jelix\Database\AbstractConnection;
use Jelix\Database\Schema\SqlToolsInterface;
use Jelix\Installer\GlobalSetup;

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
     * @var AbstractConnection
     */
    private $_dbConn;

    /**
     * global setup.
     *
     * @var GlobalSetup
     */
    protected $globalSetup;

    public function __construct(GlobalSetup $setup)
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
     * @return SqlToolsInterface the tool class of jDb
     */
    public function dbTool()
    {
        return $this->dbConnection()->tools();
    }

    /**
     * @return AbstractConnection the connection to the database used for the module
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
            $conf = $this->globalSetup->getFrameworkConfig()->getEnabledModulesPathsList();
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

        if (!file_exists($file)) {
            throw new \Exception('SQL script file '.$name.' not found');
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

    public function createTableFromDao($selectorStr)
    {
        $cnt = $this->dbConnection();
        $context = new DaoContext($cnt->getSQLType());
        $daoMapper = new DbMapper($context, $cnt);
        $daoMapper->createTableFromDao($context->resolveDaoPath($selectorStr));
    }

    /**
     * Insert data into a database, from a json file, using a DAO mapping.
     *
     * @param string     $relativeSourcePath name of the json file into the install directory
     * @param int        $option             one of SqlToolsInterface::IBD_* const
     * @param null|mixed $module
     *
     * @return int number of records inserted/updated
     *
     * @throws \Exception
     */
    public function insertDaoData($relativeSourcePath, $option, $module = null)
    {
        if ($module) {
            $path = $this->globalSetup->getFrameworkConfig()->getModulePath($module);
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
        $cnt = \jDb::getConnection($this->dbProfile);
        $context = new DaoContext($cnt->getSQLType());
        $daoMapper = new DbMapper($context, $cnt);
        $count = 0;
        foreach ($dataToInsert as $daoData) {
            if (!isset($daoData['dao'])
                || !isset($daoData['properties'])
                || !isset($daoData['data'])
            ) {
                throw new \Exception('Bad format for dao data file.');
            }
            $daoFile = $context->resolveDaoPath($daoData['dao']);
            $count += $daoMapper->insertDaoData(
                $daoFile,
                $daoData['properties'],
                $daoData['data'],
                $option
            );
        }

        return $count;
    }
}
