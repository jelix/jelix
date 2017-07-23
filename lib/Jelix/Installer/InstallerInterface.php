<?php
/**
* @author      Laurent Jouanneau
* @copyright   2009-2017 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer;


/**
* @since 1.2
*/
interface InstallerInterface {

    /**
     * Called before the installation of any modules,
     * for each entrypoints
     *
     * Here, you should check if the module can be installed or not
     * for the given entry point.
     * @throws Exception if the module cannot be installed
     */
    function preInstallEntryPoint(EntryPoint $entryPoint);

    /**
     * Should configure the module for the given entrypoint
     *
     * If an error occurs during the installation, you are responsible
     * to cancel/revert all things the method did before the error
     * @throws Exception  if an error occurs during the installation.
     * @param EntryPoint $entryPoint
     */
    function installEntryPoint(EntryPoint $entryPoint);

    /**
     * Redefine this method if you do some additional process after
     * the installation of all modules for the given entrypoint for
     *
     * @throws Exception  if an error occurs during the post installation.
     */
    function postInstallEntryPoint(EntryPoint $entryPoint);

    /**
     * Called before the uninstallation of all other modules for the given entry point
     *
     * Here, you should check if the module can be uninstalled or not
     * @throws Exception if the module cannot be uninstalled
     */
    function preUninstallEntryPoint(EntryPoint $entryPoint);

    /**
     * should unconfigure the module for the given entry point
     *
     * called for each entry point
     *
     * @throws Exception  if an error occurs during the uninstall.
     * @param EntryPoint $entryPoint
     */
    function uninstallEntrypoint(EntryPoint $entryPoint);

    /**
     * Redefine this method if you do some additional process after
     * the uninstallation of all modules for the given entrypoint
     *
     * @throws Exception  if an error occurs during the post installation.
     */
    function postUninstallEntryPoint(EntryPoint $entryPoint);


}

