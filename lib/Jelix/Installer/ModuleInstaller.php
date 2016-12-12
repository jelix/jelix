<?php
/**
* @author      Laurent Jouanneau
* @copyright   2008-2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer;


/**
* a class to install a module. 
* @since 1.2
*/
class ModuleInstaller  extends AbstractInstaller implements InstallerInterface {

    /**
     * Called before the installation of all other modules
     * (dependents modules or the whole application).
     * Here, you should check if the module can be installed or not
     * @throws Exception if the module cannot be installed
     */
    function preInstall() {

    }

    /**
     * should configure the module, install table into the database etc..
     * If an error occurs during the installation, you are responsible
     * to cancel/revert all things the method did before the error
     * @throws Exception  if an error occurs during the installation.
     */
    function install() {
        
    }

    /**
     * Redefine this method if you do some additionnal process after the installation of
     * all other modules (dependents modules or the whole application)
     * @throws Exception  if an error occurs during the post installation.
     */
    function postInstall() {
        
    }

    /**
     * Called before the uninstallation of all other modules
     * (dependents modules or the whole application).
     * Here, you should check if the module can be uninstalled or not
     * @throws Exception if the module cannot be uninstalled
     */
    function preUninstall() {
        
    }

    /**
     * should remove static files. Probably remove some data if the user is agree etc...
     * @throws Exception  if an error occurs during the install.
     */
    function uninstall() {
        
    }

    /**
     * 
     * @throws Exception  if an error occurs during the install.
     */
    function postUninstall() {
    
    }

}

