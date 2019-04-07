<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Module;

use Jelix\Installer\Module\API\InstallHelpers;
use Jelix\Installer\Module\API\PreInstallHelpers;

/**
 * A class that does processing to uninstall a module from an instance of
 * the application. A module should have a class that inherits from it
 * in order to remove things from the application.
 *
 * @since 1.7
 */
class Uninstaller extends InstallerAbstract implements UninstallerInterface
{
    /**
     * {@inheritdoc}
     */
    public function preUninstall(PreInstallHelpers $helpers)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(InstallHelpers $helpers)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postUninstall(InstallHelpers $helpers)
    {
    }
}
