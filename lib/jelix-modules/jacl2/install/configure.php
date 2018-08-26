<?php
/**
 * @package     jelix
 * @subpackage  jacl2
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class jacl2ModuleConfigurator extends \Jelix\Installer\Module\Configurator {

    public function getDefaultParameters()
    {
        return array(
            'eps'=>array()
        );
    }


    public function askParameters()
    {
        $this->parameters['eps'] = $this->askEntryPoints(
            'Select entry points on which to setup the acl2 plugin to check acl at each request.',
            '',
            true,
            $this->parameters['eps']
        );
    }

    public function configure() {

        foreach($this->getParameter('eps') as $epId) {
            $this->configureEntryPoint($epId);
        }
    }

    protected function configureEntryPoint($epId) {
        $entryPoint = $this->getEntryPointsById($epId);
        $conf = $entryPoint->getConfigIni();
        if (null == $conf->getValue('jacl2', 'coordplugins')) {
            $conf->setValue('jacl2', '1', 'coordplugins');
            if ($entryPoint->getType() != 'classic') {
                $onerror = 1;
            }
            else {
                $onerror = 2;
            }
            $conf->setValue('on_error', $onerror, 'coordplugin_jacl2');
            $conf->setValue('error_message', "jacl2~errors.action.right.needed", 'coordplugin_jacl2');
            $conf->setValue('on_error_action', "jelix~error:badright", 'coordplugin_jacl2');
        }
    }

}