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
     * Called before the installation of any modules
     *
     * Here, you should check if the module can be installed or not
     * @throws Exception if the module cannot be installed
     */
    function preInstall();

    /**
     * Should configure the module
     *
     * If an error occurs during the installation, you are responsible
     * to cancel/revert all things the method did before the error
     * @throws Exception  if an error occurs during the installation.
     */
    function install();

    /**
     * Redefine this method if you do some additional process after
     * the installation of all modules
     *
     * @throws Exception  if an error occurs during the post installation.
     */
    function postInstall();

}

