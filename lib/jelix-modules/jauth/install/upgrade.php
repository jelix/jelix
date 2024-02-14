<?php

/**
 * @package     jelix
 * @subpackage  jauth
 *
 * @author     Laurent Jouanneau
 * @copyright  2023 Laurent Jouanneau
 *
 * @see       https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jauthModuleUpgrader extends \Jelix\Installer\Module\Installer
{
    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        // it's a good thing to recreate the key at each upgrade, to increase the security
        // and to be sure that the value of persistant_encryption_key has been generated with the following code.
        $cryptokey = \Defuse\Crypto\Key::createNewRandomKey();
        $key = $cryptokey->saveToAsciiSafeString();
        $helpers->getLiveConfigIni()->setValue('persistant_encryption_key', $key, 'coordplugin_auth');
    }
}
