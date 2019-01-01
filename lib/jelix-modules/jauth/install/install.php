<?php
/**
* @package     jelix
* @subpackage  jauth module
* @author      Laurent Jouanneau
* @copyright   2009-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jauthModuleInstaller extends \Jelix\Installer\Module\Installer {

    function install(\Jelix\Installer\Module\API\InstallHelpers $helpers) {
        $cryptokey = \Defuse\Crypto\Key::createNewRandomKey();
        $key = $cryptokey->saveToAsciiSafeString();
        $helpers->getLiveConfigIni()->setValue('persistant_encryption_key', $key, 'coordplugin_auth');
    }
}
