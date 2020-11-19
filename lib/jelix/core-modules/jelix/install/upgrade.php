<?php

/**
 * @package    jelix-modules
 * @subpackage jelix-module
 *
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 *
 * @see       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jelixModuleUpgrader extends \Jelix\Installer\Module\Installer
{
    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $helpers->setupModuleWebFiles(
            $this->getParameter('wwwfiles'),
            $helpers->getConfigIni()->getValue('jelixWWWPath', 'urlengine'),
            LIB_PATH.'jelix-www'
        );
    }
}
