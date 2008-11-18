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
class jInstaller {

    /**
     * @return jInstallerApp
     */
    static function getApplication() {
        
        
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
     * install the given modules and plugins
     *
     * @param array $list  list of jInstallerModule/jInstallerPlugin objects
     * @throw jException if the install has failed
     */
    static function install($list) {
        // call the install() method of each object.
    }

    /**
     * uninstall the given modules and plugins, by checking dependencies.
     *
     * @param array $list  list of jInstallerModule/jInstallerPlugin objects
     */
    static function uninstall($list) {
        // call the uninstall() method of each object.
    }

    const STATUS_INSTALLED = 1;
    const STATUS_UNINSTALLED = 2;
    const STATUS_ACTIVATED = 4;
    const STATUS_DEACTIVATED = 8;
    const STATUS_ALL = 0;

    /**
     * return the list of modules
     * @param integer $status  combination of STATUS_*
     * @return array array of jInstallerModule
     */
    static function getModulesList($status = 0) {

    }

    /**
     * get a module by its id
     * @return jInstallerModule
     */
    static function getModuleById($id) {
    
    }

    /**
     * get a module by its name
     * @return jInstallerModule
     */
    static function getModule($name) {
    
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

}