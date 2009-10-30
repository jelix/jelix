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
     * @param jIniMultiFilesModifier $config the configuration of the entry point
     * @param string $path the component path
     * @param string $version version of the component
     * 
     */
    function __construct ($config, $path, $version) {
        $this->config = $config;
        $this->path = $path;
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
    final protected function execSQLScript ($name, $profile='') {
        $tools = jDb::getTools($profile);
        $p = jDb::getProfile ($profile);
        $driver = $p['driver'];
        if ($driver == 'pdo') {
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
