<?php
/**
 * @package     jelix
 * @subpackage  jauth module
 *
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jauthModuleUpgrader_movepersistantkey extends \Jelix\Installer\Module\Installer
{
    protected $targetVersions = array('1.3.1', '1.7.0-beta.2');
    protected $date = '2018-05-28 22:20';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {

        // setup new key on liveconfig.ini.php
        $liveConfigIni = $helpers->getLiveConfigIni();
        $key = $liveConfigIni->getValue('persistant_encryption_key', 'coordplugin_auth');
        if ($key === null) {
            $cryptokey = \Defuse\Crypto\Key::createNewRandomKey();
            $key = $cryptokey->saveToAsciiSafeString();

            $liveConfigIni->setValue('persistant_encryption_key', $key, 'coordplugin_auth');
        }
    }
}
