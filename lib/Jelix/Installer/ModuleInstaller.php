<?php
/**
* @author      Laurent Jouanneau
* @copyright   2008-2014 Laurent Jouanneau
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
     * @throw jException if an error occurs during the check of the installation
     */
    function preInstall() {

    }

    /**
     * should configure the module, install table into the database etc..
     * If an error occurs during the installation, you are responsible
     * to cancel/revert all things the method did before the error
     * @throw jException  if an error occurs during the install.
     */
    function install() {
        
    }

    /**
     * Redefine this method if you do some additionnal process after the installation of
     * all other modules (dependents modules or the whole application)
     * @throw jException  if an error occurs during the post installation.
     */
    function postInstall() {
        
    }

    /**
     * Called before the uninstallation of all other modules
     * (dependents modules or the whole application).
     * Here, you should check if the module can be uninstalled or not
     * @throw jException if an error occurs during the check of the installation
     * @notimplemented not used for the current version of the installer
     */
    function preUninstall() {
        
    }

    /**
     * should configure the module, install table into the database etc.. 
     * @throw jException  if an error occurs during the install.
     * @notimplemented not used for the current version of the installer
     */
    function uninstall() {
        
    }

    /**
     * 
     * @throw jException  if an error occurs during the install.
     * @notimplemented not used for the current version of the installer
     */
    function postUninstall() {
    
    }

}

