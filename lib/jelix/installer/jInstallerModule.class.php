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
* a class to install a module. 
* @package     jelix
* @subpackage  installer
* @experimental
* @since 1.1
*/
class jInstallerModule extends jInstallerBase {

    protected $application;

    /**
     * The module should be present in the application.
     * @param string $name the name of the module
     * @param jInstallerApp $application
     */
    function __construct($name, $application) {
        // read the module.xml
        // and set the $path property
    }

    /**
     * @return boolean true if the module is installed
     */
    function isInstalled() {
        
    }

    /**
     * install the module, by checking dependencies.
     * @throw jException  if an error occurs during the install.
     */
    function install() {

        // * check that all dependencies are ok : the needed modules and plugins
        // should be present in the application, even if this modules or plugins
        // are not install
        // * start the install of all needed modules and plugins before installing
        // the module. Check before isInstalled() of the module/plugin
        // If an exception occured during the install of this dependencies
        // we should call uninstall of previous modules/plugins well installed and which
        // install() has returned true. throw the exception
        // * if ok, install the module, by calling the _install.php script
        // * if error, uninstall dependencies which have just been installed,
        //   undo things which have made during the install of the module, and
        //   throw an exception
    }
    
    /**
     * uninstall the module, by checking dependencies.
     * @throw jException  if an error occurs during the install.
     */
    function uninstall() {
        // * check that all dependencies are ok : the needed modules and plugins
        // should be present in the application
        // * start the uninstall of all needed modules and plugins before installing
        // the module. 
        // * if ok, uninstall the module, by calling the _uninstall.php script      
    }
    
    function activate() {
    }
    
    function deactivate() {
        
    }
}

