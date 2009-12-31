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
     * @var boolean true if this is an installation for the whole application.
     *              false if this is an installation in an
     *              already installed application. Always False for upgraders.
     */
    protected $installWholeApp = false;

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
     * configuration, entry point and db profile. It should return a corresponding
     * install session id. It should be a unique id, calculated with some
     * criterias, depending if you want the installer to be called for each
     * entrypoint or not, for different db profile or not etc..
     * Typically, you could return an md5 value on a string which could contain
     * the name of the given entry point, and/or the name of the given dbprofile
     * and/or any other criteria.
     * @param jInstallerEntryPoint $ep the entry point
     * @param jIniMultiFilesModifier $config the configuration of the entry point
     * @param string $dbProfile the name of the jdb profile
     * @return string an identifier
     */
    public function setEntryPoint($ep, $config, $dbProfile) {
        $this->config = $config;
        $this->entryPoint = $ep;
        $this->dbProfile = $dbProfile;
        return "0";
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
    
    final protected function copyFile($relativeSourcePath, $targetPath) {
        copy ($this->path.'install/'.$relativeSourcePath, $targetPath);
    }
}
