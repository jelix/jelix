<?php
/**
 * @package     jelix
 * @subpackage  jacl
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class jaclModuleConfigurator extends \Jelix\Installer\Module\Configurator {

    public function configure(\Jelix\Installer\Module\API\ConfigurationHelpers $helpers) {
        foreach($helpers->getEntryPointsList() as $entrypoint) {
            $this->setEpConf($entrypoint);
        }
    }

    protected function setEpConf(\Jelix\Installer\EntryPointConfigurator $entryPoint) {
        /** @var \Jelix\IniFile\IniModifierArray $conf */
        $conf = $entryPoint->getConfigIni();
        if (null == $conf->getValue('jacl', 'coordplugins')) {
            $conf->setValue('jacl', '1', 'coordplugins');
            if ($entryPoint->getType() != 'classic')
                $onerror = 1;
            else
                $onerror = 2;
            $conf->setValue('on_error', $onerror, 'coordplugin_jacl');
            $conf->setValue('error_message', "jacl~errors.action.right.needed", 'coordplugin_jacl');
            $conf->setValue('on_error_action', "jelix~error:badright", 'coordplugin_jacl');
        }
    }

}