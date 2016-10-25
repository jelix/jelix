<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2008-2009 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* a class to install a module. 
* @package     jelix
* @subpackage  installer
* @since 1.2
*/
class jInstallerModule  extends jInstallerBase implements jIInstallerComponent {

    /**
     * Called before the installation of all other modules
     * (dependents modules or the whole application).
     * Here, you should check if the module can be installed or not
     * @throw Exception if the module cannot be installed
     */
    function preInstall() {

    }

    /**
     * should configure the module, install table into the database etc..
     * If an error occurs during the installation, you are responsible
     * to cancel/revert all things the method did before the error
     * @throw Exception  if an error occurs during the installation.
     */
    function install() {
        
    }

    /**
     * Redefine this method if you do some additionnal process after the installation of
     * all other modules (dependents modules or the whole application)
     * @throw Exception  if an error occurs during the post installation.
     */
    function postInstall() {
        
    }

    /**
     * Called before the uninstallation of all other modules
     * (dependents modules or the whole application).
     * Here, you should check if the module can be uninstalled or not
     * @throw Exception if the module cannot be uninstalled
     * @notimplemented not used for the current version of the installer
     */
    function preUninstall() {
        
    }

    /**
     * should remove static files. Probably remove some data if the user is agree etc...
     * @throw Exception  if an error occurs during the install.
     * @notimplemented not used for the current version of the installer
     */
    function uninstall() {
        
    }

    /**
     * 
     * @throw Exception  if an error occurs during the install.
     * @notimplemented not used for the current version of the installer
     */
    function postUninstall() {
    
    }

}

