<?php
/**
 * @package     jelix
 * @subpackage  jacl2
 *
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
use Jelix\Installer\Module\API\ConfigurationHelpers;

class jacl2ModuleConfigurator extends \Jelix\Installer\Module\Configurator
{
    public function getDefaultParameters()
    {
        return array(
            'eps' => array(),
        );
    }

    public function configure(ConfigurationHelpers $helpers)
    {
        $this->parameters['eps'] = $helpers->cli()->askEntryPoints(
            'Select entry points on which to setup the acl2 plugin to check acl at each request.',
            $helpers->getEntryPointsList(),
            true,
            $this->parameters['eps']
        );
        foreach ($this->getParameter('eps') as $epId) {
            $this->configureEntryPoint($epId, $helpers);
        }
    }

    protected function configureEntryPoint($epId, ConfigurationHelpers $helpers)
    {
        $entryPoint = $helpers->getEntryPointsById($epId);
        /** @var \Jelix\IniFile\IniModifierArray $conf */
        $conf = $entryPoint->getConfigIni();
        if ($conf->getValue('jacl2', 'coordplugins') == null && $entryPoint->getType() != 'cmdline') {
            $conf->setValue('jacl2', '1', 'coordplugins');
            if ($entryPoint->getType() != 'classic') {
                $onerror = 1;
            } else {
                $onerror = 2;
            }
            $conf->setValue('on_error', $onerror, 'coordplugin_jacl2');
            $conf->setValue('error_message', 'jacl2~errors.action.right.needed', 'coordplugin_jacl2');
            $conf->setValue('on_error_action', 'jelix~error:badright', 'coordplugin_jacl2');
            $conf->save();
        }
    }
}
