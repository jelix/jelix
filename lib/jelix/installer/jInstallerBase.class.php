<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* EXPERIMENTAL
* a class to install a module. You should override it into a install/install.php file. The 
* class should be named appInstaller
* @package     jelix
* @subpackage  installer
* @experimental
* @since 1.1
*/
abstract class jInstallerBase {

    /**
     * @var jIInstallReporter
     */
    public $reporter;


    function __construct($reporter, $basePath) {
        $this->reporter = $reporter;
        $this->basePath = $basePath;
    }

    /**
     * main method for the installation
     */
    abstract function install();

    /**
     * main method for the uninstallation
     */
    abstract function uninstall();

    /**
     * import a sql script into the given profile.
     * @param string $name the part of the file name : $name.databasetype.sql
     *               for example, if you provide example.mysql.sql and example.pgsql.sql, give 'example'
     */
    function execSQLScript($name, $profil='') {
        $tools = jDb::getTools($profil);
        $p = jDb::getProfil ($profil);
        $driver = $p['driver'];
        if($driver == 'pdo'){
            preg_match('/^(\w+)\:.*$/',$p['dsn'], $m);
            $driver = $m[1];
        }
        $tools->execSQLScript($this->basePath.$name.'.'.$driver.'.sql');
    }

    /**
     * @param string $filename relative path to the var/config directory
     * @return jIniFileModifier
     */
    function getConfig($filename) {
        return new jIniFileModifier(JELIX_APP_CONFIG_PATH.$filename);
    }

    /**
     * @param string $sourcePath
     * @param string $targetPath
     */
    function copyDirectoryContent($sourcePath, $targetPath) {
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

?>