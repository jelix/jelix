<?php
/**
* @author      Laurent Jouanneau
* @copyright   2008-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer\Module;

/**
 * Bas class for classes that does processing to install a module into
 * an instance of the application. A module should have a class that inherits
 * from it in order to setup itself into the application.
 *
 * @since 1.7
 */
class Installer extends InstallerAbstract implements InstallerInterface {

    use InstallerHelpersTrait;

    /**
     * @inheritdoc
     */
    function preInstall() {

    }

    /**
     * @inheritdoc
     */
    function install() {

    }

    /**
     * @inheritdoc
     */
    function postInstall() {

    }

    /**
     * the versions for which the installer should be called.
     *
     * Useful for an upgrade which target multiple branches of a project.
     * Put the version for multiple branches. The installer will be called
     * only once, for the needed version.
     * If you don't fill it, the name of the class file should contain the
     * target version (deprecated behavior though)
     *
     * @var array $targetVersions list of version by asc order
     */
    protected $targetVersions = array();

    /**
     * @var string the date of the release of the update. format: yyyy-mm-dd hh:ii
     */
    protected $date = '';

    /**
     * @var string the version for which the installer is called
     */
    protected $version = '0';


    function getTargetVersions() {
        return $this->targetVersions;
    }

    function setTargetVersions($versions) {
        $this->targetVersions = $versions;
    }

    function getDate() {
        return $this->date;
    }

    function getVersion() {
        return $this->version;
    }

    function setVersion($version) {
        $this->version = $version;
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

