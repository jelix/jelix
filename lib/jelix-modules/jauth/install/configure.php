<?php
/**
 * @package     jelix
 * @subpackage  jauth
 * @author      Laurent Jouanneau
 * @contributor Julien Issler
 * @copyright   2009-2018 Laurent Jouanneau
 * @copyright   2011 Julien Issler
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class jauthModuleConfigurator extends jInstallerModuleConfigurator {

    public function getDefaultParameters()
    {
        return array(
            'eps'=>array()
        );
    }

    public function askParameters()
    {
        // FIXME: if eps is empty, detecter sur quel point d'entrée c'est setté?

        $this->parameters['eps'] = $this->askEntryPoints(
            'Select entry points on which to setup authentication plugins.',
            'classic',
            true
        );
    }


    public function configure() {
        foreach($this->getParameter('eps') as $epId) {
            $this->configureEntryPoint($epId);
        }
    }

    protected $authConfigList = array();

    public function configureEntryPoint($epId) {
        $entryPoint = $this->getEntryPointsById($epId);

        $configIni = $entryPoint->getConfigIni();

        $authconfig = $configIni->getValue('auth','coordplugins');
        $authconfigMaster = $this->getConfigIni()->getValue('auth','coordplugins');

        $forWS = (in_array($entryPoint->getType(), array('json', 'jsonrpc', 'soap', 'xmlrpc')));

        if (!$authconfig || ($forWS && $authconfig == $authconfigMaster)) {

            if ($forWS) {
                $pluginIni = 'authsw.coord.ini.php';
            } else {
                $pluginIni = 'auth.coord.ini.php';
            }

            $authconfig = dirname($entryPoint->getConfigFileName()) . '/' . $pluginIni;

            if (!isset($this->authConfigList[$authconfig])) {
                $this->authConfigList[$authconfig] = true;
                // no configuration, let's install the plugin for the entry point
                $entryPoint->getConfigIni()->setValue('auth', $authconfig, 'coordplugins');

                $configFilePath = $this->getConfigurationMode()?
                    jApp::varConfigPath($authconfig):
                    jApp::appConfigPath($authconfig);

                if (!file_exists($configFilePath)) {
                    $this->copyFile('var/config/' . $pluginIni, $configFilePath);
                }
            }
        }
    }
}