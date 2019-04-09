<?php

/**
 * @package    jelix-modules
 * @subpackage jelix-module
 *
 * @author     Laurent Jouanneau
 * @copyright  2018 Laurent Jouanneau
 *
 * @see       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
require_once __DIR__.'/InstallTrait.php';

class jelixModuleUpgrader extends \Jelix\Installer\Module\Installer
{
    use \Jelix\JelixModule\InstallTrait;

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $this->setupWWWFiles($helpers);
    }
}
