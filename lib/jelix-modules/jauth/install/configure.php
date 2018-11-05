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
use \Jelix\Installer\Module\API\ConfigurationHelpers;

class jauthModuleConfigurator extends \Jelix\Installer\Module\Configurator {

    public function getDefaultParameters()
    {
        return array(
            'eps'=>array()
        );
    }


    public function configure(ConfigurationHelpers $helpers) {

        $this->parameters['eps'] = $helpers->cli()->askEntryPoints(
            'Select entry points on which to setup authentication plugins.',
            'classic',
            true
        );

        foreach($this->getParameter('eps') as $epId) {
            $this->configureEntryPoint($epId, $helpers);
        }
    }

    protected $authConfigList = array();

    public function configureEntryPoint($epId, ConfigurationHelpers $helpers) {
        $entryPoint = $helpers->getEntryPointsById($epId);

        $configIni = $entryPoint->getConfigIni();

        $authconfig = $configIni->getValue('auth','coordplugins');
        $authconfigMaster = $helpers->getConfigIni()->getValue('auth','coordplugins');

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

                $helpers->copyFile('var/config/' . $pluginIni, 'config:'.$authconfig, false);
            }
        }
    }
}