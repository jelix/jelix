<?php
/**
 * @package     jelix
 * @subpackage  installer
 *
 * @author      Laurent Jouanneau
 * @copyright   2009 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * @package     jelix
 * @subpackage  installer
 *
 * @since 1.2
 * @deprecated
 */
interface jIInstallerComponent
{
    /**
     * Called before the installation of all other components
     * (dependents modules or the whole application).
     * Here, you should check if the component can be installed or not.
     *
     * @throws jException if an error occurs during the check of the installation
     */
    public function preInstall();

    /**
     * should configure the component, install table into the database etc..
     * If an error occurs during the installation, you are responsible
     * to cancel/revert all things the method did before the error.
     *
     * @throws jException if an error occurs during the install
     */
    public function install();

    /**
     * Redefine this method if you do some additionnal process after the installation of
     * all other modules (dependents modules or the whole application).
     *
     * @throws jException if an error occurs during the post installation
     */
    public function postInstall();

    /**
     * Called before the uninstallation of all other modules
     * (dependents modules or the whole application).
     * Here, you should check if the component can be uninstalled or not.
     *
     * @throws jException if an error occurs during the check of the installation
     */
    public function preUninstall();

    /**
     * should configure the component, install table into the database etc..
     *
     * @throws jException if an error occurs during the install
     */
    public function uninstall();

    /**
     * @throws jException if an error occurs during the install
     */
    public function postUninstall();
}
