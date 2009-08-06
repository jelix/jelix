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
* EXPERIMENTAL
* base class for installers
* @package     jelix
* @subpackage  installer
* @experimental
* @since 1.2
*/
abstract class jInstallerBase {

    public $version = '0';


    /**
     * default configuration of the application
     * @var jIniFileModifier
     */
    protected $config;
    
    /**
     * The path of the module
     * @var string
     */
    protected $path;

    function __construct ($defaultConfig, $modulePath, $version) {
        $this->config = $defaultConfig;
        $this->path = $modulePath;
        $this->version = $version;
    }


    /**
     * import a sql script into the given profile.
     *
     * The name of the script should be store in install/sql/$name.databasetype.sql
     * in the directory of the component. (replace databasetype by mysql, pgsql etc.)
     * 
     * @param string $name the name of the script, without suffixes
     */
    final protected function execSQLScript($name, $profile='') {
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
