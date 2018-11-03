<?php
/**
* @author      Laurent Jouanneau
* @copyright   2018 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer\Module;

use Jelix\Installer\Module\API\InstallHelpers;
use Jelix\Installer\Module\API\PreInstallHelpers;

/**
 * interface for classes that uninstall a module
 * @since 1.7
 */
interface UninstallerInterface {

    /**
     * Called before the uninstallation of all other modules
     *
     * Here, you should check if the module can be uninstalled or not
     * @throws \Exception if the module cannot be uninstalled
     */
    function preUninstall(PreInstallHelpers $helpers);

    /**
     * should uninstall the module
     *
     * @throws \Exception  if an error occurs during the uninstall.
     */
    function uninstall(InstallHelpers $helpers);

    /**
     * Redefine this method if you do some additional process after
     * the uninstallation of all modules
     *
     * @throws \Exception  if an error occurs during the post installation.
     */
    function postUninstall(InstallHelpers $helpers);

}

