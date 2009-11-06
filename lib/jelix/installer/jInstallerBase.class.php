<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2009 Laurent Jouanneau
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
     * The path of the module
     * @var string
     */
    protected $path;

    /**
     * @var string the jDb profile for the component
     */
    protected $dbProfile = '';

    
    

    /**
     * @param string $name name of the component
     * @param jIniMultiFilesModifier $config the configuration of the entry point
     * @param string $path the component path
     * @param string $version version of the component
     * @param string $dbProfile name of the jdb profile to use to install the component
     * 
     */
    function __construct ($name, $path, $version) {
        $this->path = $path;
        $this->version = $version;
        $this->name = $name;
    }

    /**
     * @var jDbTools
     */
    private $_dbTool = null;

    /**
     * @var jDbConnection
     */
    private $_dbConn = null;

    private $_dbpInstalled = array();

    /**
     * an installer should call this method before doing things with jDb or jDao, in order to know
     * if it haven't already been called for the same jdb profile and entry point, because the installer
     * is called for each entry points on which the component is activated.
     */
    protected function isDbAlreadyInstalled() {
        return (isset($this->_dbpInstalled[$this->dbProfile]) && $this->_dbpInstalled[$this->dbProfile] != $this->entryPointId);
    }

    public function setEntryPoint($epId, $config, $dbProfile) {
        $this->config = $config;
        $this->entryPointId = $epId;
        $this->dbProfile = $dbProfile;
        if (!isset($this->_dbpInstalled[$this->dbProfile]))
            $this->_dbpInstalled[$this->dbProfile] = $epId;
    }


    /**
     * @return jDbTools  the tool class of jDb
     */
    protected function dbTool () {
        if (!$this->_dbTool)
            $this->_dbTool = jDb::getTools($this->dbProfile);
        return $this->_dbTool;
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
     * import a sql script into the given profile.
     *
     * The name of the script should be store in install/$name.databasetype.sql
     * in the directory of the component. (replace databasetype by mysql, pgsql etc.)
     * 
     * @param string $name the name of the script, without suffixes
     */
    final protected function execSQLScript ($name, $profile = null) {
        $tools = $this->dbTool();
        $p = jDb::getProfile ($this->dbProfile);
        $driver = $p['driver'];
        if ($driver == 'pdo') {
            preg_match('/^(\w+)\:.*$/',$p['dsn'], $m);
            $driver = $m[1];
        }
        $tools->execSQLScript($this->path.'install/'.$name.'.'.$driver.'.sql');
    }

    /**
     * @param string $sourcePath
     * @param string $targetPath
     */
    final protected function copyDirectoryContent($sourcePath, $targetPath) {
        jFile::createDir($targetPath);
        $dir = new DirectoryIterator($sourcePath);
        foreach ($dir as $dirContent) {
            if ($dirContent->isFile()) {
                copy($dirContent->getPathName(), $targetPath.substr($dirContent->getPathName(), strlen($dirContent->getPath())));
            } else {
                if (!$dirContent->isDot() && $dirContent->isDir()) {
                    $newTarget = $targetPath.substr($dirContent->getPathName(), strlen($dirContent->getPath()));
                    $this->copyDirectoryContent($dirContent->getPathName(),$newTarget );
                }
            }
        }
    }
}
