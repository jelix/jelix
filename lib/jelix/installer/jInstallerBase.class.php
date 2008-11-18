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
* a class to install a component (module or plugin) 
* @package     jelix
* @subpackage  installer
* @experimental
* @since 1.1
*/
abstract class jInstallerBase {
    
    /**
     * the path of the directory of the component
     * it should be set by the constructor
     */
    protected $path = '';
    
    function __construct($name) {
        
    }

    abstract function isInstalled();

    abstract function isActivated();

    /**
     * install the component. It just call the _install.php of the component
     *
     * It should expose a variable $installer to the _install.php,
     * $installer should be the current instance of this class.
     */
    abstract function install();

    /**
     * uninstall the component. It just call the _uninstall.php
     * of the component
     *
     * It should expose a variable $installer to the _uninstall.php,
     * $installer should be the current instance of this class.
     */
    abstract function uninstall();

    /**
     * activate the component.
     * It just call the _activate.php of the component ?
     *
     * It should expose a variable $installer to the _activate.php,
     * $installer should be the current instance of this class.
     */
    abstract function activate();

    /**
     * deactivate the component. It just call the _deactivate.php
     * of the component
     *
     * It should expose a variable $installer to the _deactivate.php,
     * $installer should be the current instance of this class.
     */
    abstract function deactivate();

    /**
     * import a sql script into the given profile.
     *
     * The name of the script should be store in install/sql/$name.databasetype.sql
     * in the directory of the component. (replace databasetype by mysql, pgsql etc.)
     * 
     * @param string $name the name of the script, without suffixes
     */
    public function execSQLScript($name, $profile='') {
        $tools = jDb::getTools($profile);
        $p = jDb::getProfile ($profile);
        $driver = $p['driver'];
        if($driver == 'pdo'){
            preg_match('/^(\w+)\:.*$/',$p['dsn'], $m);
            $driver = $m[1];
        }
        $tools->execSQLScript($this->path.'install/sql/'.$name.'.'.$driver.'.sql');
    }

    /**
     * @param string $sourcePath
     * @param string $targetPath
     */
    static function copyDirectoryContent($sourcePath, $targetPath) {
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

