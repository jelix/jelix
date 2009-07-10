<?php

/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2008-2009 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
class jInstaller {

    const STATUS_INSTALLED = 1;
    const STATUS_ACTIVATED = 2;
    const STATUS_PUBLIC = 3;
    
    const INSTALL_ERROR_MISSING_DEPENDENCIES = 1;
    const INSTALL_ERROR_CIRCULAR_DEPENDENCY = 2;

    static public $iniFile = null;

    static protected $config = null;
    static protected $modules = array();

    static function init() {
        if (!self::$config) {
            self::$config = jConfigCompiler::read('defaultconfig.ini.php', true);
            self::$iniFile = new jIniFileModifier(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php');
            self::$modules = array();
            foreach($config->_allModulesPathList as $name=>$path) {
                $status = $config->modules[$name.'.status'];
                self::$modules[$name] = new jInstallerModule($name, $path, $status, $config->modules[$name.'.version']);
            }
        }
    }

    /**
     * get a module by its name
     * @return jInstallerModule
     */
    static function getModule($name) {
        if (isset(self::$modules[$name]))
            return self::$modules[$name];
        else
            return null;
    }

    /**
     * install the given modules and plugins
     *
     * @param array $list  list of jInstallerModule/jInstallerPlugin objects
     * @throw jException if the install has failed
     */
    static function install($list) {
        self::init();
        self::$_installingComponents = array();
        // call the install() method of each object.
        foreach($list as $component) {
            if (!$component->isInstalled()) {
                self::_installComponent($component);
            }
        }
    }
    
    static protected $_installingComponents = array();
    
    /**
     * install a module or a plugin
     * @param jInstallerBase $component
     */
    static protected function _installComponent($component) {

        $component->init();
        
        if (isset(self::$_installingComponents[$component->name])) {
            $component->inError = self::INSTALL_ERROR_CIRCULAR_DEPENDENCY;
            throw new Exception ("circular dependency ! Cannot install the component ".$component->name);
        }
        self::$_installingComponents[$component->name] = true;

        $compNeeded = '';
        foreach($component->dependencies as $compInfo) {
            $comp = self::getModule($compInfo['name']);
            if (!$comp)
                $compNeeded.=$compInfo['name'].', ';
            elseif (!$comp->isInstalled()) {
                self::_installComponent($comp);
            }
        }

        if ($compNeeded) {
            unset(self::$_installingComponents[$component->name]);
            $component->inError = self::INSTALL_ERROR_MISSING_DEPENDENCIES;
            throw new Exception ('To install '.$component->name.' these modules are needed: '.$compNeeded);
        }

        $component->install();
        
        unset(self::$_installingComponents[$component->name]);
    }

    /**
     * uninstall the given modules and plugins, by checking dependencies.
     *
     * @param array $list  list of jInstallerModule/jInstallerPlugin objects
     */
    static function uninstall($list) {
        // call the uninstall() method of each object.
    }


    /**
     * return the list of modules
     * @param integer $status  combination of STATUS_*
     * @return array array of jInstallerModule
     */
    static function getModulesList($moduleList, $status = 0) {
        // TODO: getting the simple liste
        
        return array();
    }

    /**
     * get a module by its id
     * @return jInstallerModule
     */
    static function getModuleById($id) {
    
    }


    /**
     * return the list of plugins
     * @param integer $status  combination of STATUS_*
     * @return array array of jInstallerPlugin
     */
    static function getPluginsList() {
    }

    /**
     * get a plugin by its id
     * @return jInstallerPlugin
     */
    static function getPluginById($id) {
    
    }

    /**
     * get a plugin by its name
     * @return jInstallerPlugin
     */
    static function getPlugin($name) {
    
    }



    /**
     * install a package.
     * a package is a zip or gz archive. Top directories of this archive
     * should be a plugin or a module not an application. So in this directories
     * it should contains a module.xml, or a plugin.xml.
     * @param string $packageFileName  the file path of the package
     * @return array an array of jInstallerModule or jInstallerPlugin objects,
     * corresponding to 
     */
    static function installPackage ($packageFileName) {
        // it should
        // * extract the package in the temp directory
        // * verify that modules/plugins are not already installed
        // in the application
        // * if ok, it should copy modules and plugins in the right directories
        // into the application
        // * create instance of jInstallerModule/jInstallerPlugin corresponding of
        // each module & plugins
        // call install()
        // if the install fails, the new directories of modules and plugins
        // should be deleted.
    }


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