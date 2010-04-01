<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2009-2010 Laurent Jouanneau
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
     * @return string|array an identifier or a list of identifiers
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
     * import a sql script into the given profile.
     *
     * The name of the script should be store in install/$name.databasetype.sql
     * in the directory of the component. (replace databasetype by mysql, pgsql etc.)
     * 
     * @param string $name the name of the script, without suffixes
     * @param string $profile the profile to use. null for the default profile
     * @param string $module the module from which we should take the sql file. null for the current module
     */
    final protected function execSQLScript ($name, $profile = null, $module = null) {

        if (!$profile) {
            $profile = $this->dbProfile;
            $tools = $this->dbTool();
        }
        else {
            $cnx = jDb::getConnection($profile);
            $tools = $cnx->tools();
        }
        $p = jDb::getProfile ($profile);
        $driver = $p['driver'];
        if ($driver == 'pdo') {
            preg_match('/^(\w+)\:.*$/',$p['dsn'], $m);
            $driver = $m[1];
        }

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
        $file = $path.'install/'.$name.'.'.$driver.'.sql';
        $tools->execSQLScript($path.'install/'.$name.'.'.$driver.'.sql');
    }

    /**
     * copy the whole content of a directory existing in the install/ directory
     * of the component, to the given directory
     * @param string $relativeSourcePath relative path to the install/ directory of the component
     * @param string $targetPath the full path where to copy the content
     */
    final protected function copyDirectoryContent($relativeSourcePath, $targetPath) {
        $this->_copyDirectoryContent ($this->path.'install/'.$relativeSourcePath, $targetPath);
    }

    /**
     * private function which copy the content of a directory to an other
     *
     * @param string $sourcePath 
     * @param string $targetPath
     */
    private function _copyDirectoryContent($sourcePath, $targetPath) {
        jFile::createDir($targetPath);
        $dir = new DirectoryIterator($sourcePath);
        foreach ($dir as $dirContent) {
            if ($dirContent->isFile()) {
                copy($dirContent->getPathName(), $targetPath.substr($dirContent->getPathName(), strlen($dirContent->getPath())));
            } else {
                if (!$dirContent->isDot() && $dirContent->isDir()) {
                    $newTarget = $targetPath.substr($dirContent->getPathName(), strlen($dirContent->getPath()));
                    $this->_copyDirectoryContent($dirContent->getPathName(),$newTarget );
                }
            }
        }
    }


    /**
     * copy a file from the install/ directory to an other
     * @param string $relativeSourcePath relative path to the install/ directory of the file to copy
     * @param string $targetPath the full path where to copy the file
     */
    final protected function copyFile($relativeSourcePath, $targetPath) {
        copy ($this->path.'install/'.$relativeSourcePath, $targetPath);
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
