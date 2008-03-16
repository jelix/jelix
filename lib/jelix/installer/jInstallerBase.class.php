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
     * import a sql script into 
     * @param string $name the part of the file name : $name.databasetype.sql
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
     * @param string $path relative path to the install directory
     * @param string $target  'www' for the www path
     */
    function copyDirectoryContent($path, $target) {
        throw new Exception("copyDirectoryContent not implemented");
    }
}

?>