<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2017 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* @package     jelix
* @subpackage  installer
* @since 1.7
*/
interface jIInstallerComponent2 {


    /**
     * Called before the installation of any modules,
     * for each entrypoints
     *
     * Here, you should check if the module can be installed or not
     * for the given entry point.
     * @throws Exception if the module cannot be installed
     */
    function preInstallEntryPoint(jInstallerEntryPoint2 $entryPoint);

    /**
     * Should configure the module for the given entrypoint
     *
     * If an error occurs during the installation, you are responsible
     * to cancel/revert all things the method did before the error
     * @throws Exception  if an error occurs during the installation.
     * @param jInstallerEntryPoint2 $entryPoint
     */
    function installEntryPoint(jInstallerEntryPoint2 $entryPoint);

    /**
     * Redefine this method if you do some additional process after
     * the installation of all modules for the given entrypoint
     *
     * @throws Exception  if an error occurs during the post installation.
     */
    function postInstallEntryPoint(jInstallerEntryPoint2 $entryPoint);

    /**
     * Called before the uninstallation of all other modules for the given entry point
     *
     * Here, you should check if the module can be uninstalled or not
     * @throws Exception if the module cannot be uninstalled
     */
    function preUninstallEntryPoint(jInstallerEntryPoint2 $entryPoint);

    /**
     * should unconfigure the module for the given entry point
     *
     * called for each entry point
     *
     * @throws Exception  if an error occurs during the uninstall.
     * @param jInstallerEntryPoint2 $entryPoint
     */
    function uninstallEntrypoint(jInstallerEntryPoint2 $entryPoint);

    /**
     * Redefine this method if you do some additional process after
     * the uninstallation of all modules for the given entrypoint
     *
     * @throws Exception  if an error occurs during the post installation.
     */
    function postUninstallEntryPoint(jInstallerEntryPoint2 $entryPoint);

}

