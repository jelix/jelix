<?php
/**
 * @package     jelix
 * @subpackage  jauth
 *
 * @author      Laurent Jouanneau
 * @contributor Julien Issler
 *
 * @copyright   2009-2018 Laurent Jouanneau
 * @copyright   2011 Julien Issler
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
use Jelix\Installer\Module\API\ConfigurationHelpers;
use Jelix\Installer\Module\API\LocalConfigurationHelpers;

class jauthModuleConfigurator extends \Jelix\Installer\Module\Configurator
{
    public function getDefaultParameters()
    {
        return array(
            'eps' => array(),
        );
    }

    public function configure(ConfigurationHelpers $helpers)
    {
        $this->removeDeprecatedKeysFromPluginConf($helpers);

        $this->parameters['eps'] = $helpers->cli()->askEntryPoints(
            'Select entry points on which to setup authentication plugins.',
            $helpers->getEntryPointsByType('classic'),
            true,
            $this->parameters['eps']
        );

        foreach ($this->getParameter('eps') as $epId) {
            $this->configureEntryPoint($epId, $helpers);
        }
    }

    public function localConfigure(LocalConfigurationHelpers $helpers)
    {
        $this->removeDeprecatedKeysFromPluginConf($helpers);
        $this->removeDeprecatedKeysFromLocalConfig($helpers);

        foreach ($helpers->getEntryPointsList() as $entryPoint) {
            $this->updateLdapEpConf($helpers, $entryPoint);
        }
    }

    protected $authConfigList = array();

    protected function configureEntryPoint($epId, ConfigurationHelpers $helpers)
    {
        $entryPoint = $helpers->getEntryPointsById($epId);

        $configIni = $entryPoint->getConfigIni();

        $authconfig = $configIni->getValue('auth', 'coordplugins');
        $authconfigMaster = $helpers->getConfigIni()->getValue('auth', 'coordplugins');

        $forWS = (in_array($entryPoint->getType(), array('json', 'jsonrpc', 'soap', 'xmlrpc')));

        if (!$authconfig || ($forWS && $authconfig == $authconfigMaster)) {
            if ($forWS) {
                $pluginIni = 'authsw.coord.ini.php';
            } else {
                $pluginIni = 'auth.coord.ini.php';
            }

            $authconfig = dirname($entryPoint->getConfigFileName()).'/'.$pluginIni;

            if (!isset($this->authConfigList[$authconfig])) {
                $this->authConfigList[$authconfig] = true;
                // no configuration, let's install the plugin for the entry point
                $entryPoint->getConfigIni()->setValue('auth', $authconfig, 'coordplugins');

                $helpers->copyFile('var/config/'.$pluginIni, 'config:'.$authconfig, false);
            }
        }
    }

    protected $ldapConfFiles = array();

    /**
     * migrate ldap configuration : move access parameters (login, pass...)
     * to the profiles.ini.php file.
     */
    protected function updateLdapEpConf(LocalConfigurationHelpers $helpers, Jelix\Installer\EntryPointConfigurator $entryPoint)
    {
        $authConfig = $entryPoint->getCoordPluginConfig('auth');
        if (!$authConfig) {
            return;
        }
        /** @var \Jelix\IniFile\IniModifierInterface $conf */
        list($conf, $section) = $authConfig;

        // check that the authentication is using ldap
        $driver = $conf->getValue('driver', $section);
        if ($driver != 'ldap' && $driver != 'Ldap') {
            return;
        }

        $tag = \Jelix\FileUtilities\Path::shortestPath(jApp::appPath(), $conf->getFileName());
        if (isset($this->ldapConfFiles[$tag])) {
            return;
        }
        $this->ldapConfFiles[$tag] = true;

        if ($section === 0) {
            // the configuration is in a separate file, not in the main configuration file
            $section_ldap = 'Ldap';
            if (!$conf->isSection($section_ldap)) {
                $section_ldap = 'ldap';
                if (!$conf->isSection($section_ldap)) {
                    return;
                }
            }
        } else {
            // the configuration is in the main configuration file
            $section_ldap = 'auth_ldap';
        }

        $profileIni = $helpers->getProfilesIni();
        $suffix = '';
        while ($profileIni->isSection('authldap:ldap'.$suffix)) {
            if ($suffix) {
                ++$suffix;
            } else {
                $suffix = 1;
            }
        }
        $sectionProfile = 'authldap:ldap'.$suffix;
        $conf->setValue('profile', 'ldap'.$suffix, $section_ldap);
        foreach (array('hostname', 'port', 'ldapUser', 'ldapPassword', 'protocolVersion')
                as $prop) {
            $val = $conf->getValue($prop, $section_ldap);
            $profileIni->setValue($prop, $val, $sectionProfile);
            $conf->removeValue($prop, $section_ldap);
        }
        $conf->save();
    }

    /**
     * @param ConfigurationHelpers|LocalConfigurationHelpers $helpers
     *
     * @throws Exception
     */
    protected function removeDeprecatedKeysFromPluginConf($helpers)
    {
        // remove deprecated key from all auth.coord.ini.php
        foreach ($helpers->getEntryPointsList() as $entryPoint) {
            $authconfig = $entryPoint->getCoordPluginConfig('auth');
            if ($authconfig) {
                /** @var \Jelix\IniFile\IniModifierInterface $conf */
                list($conf, $section) = $authconfig;
                $conf->removeValue('persistant_crypt_key', $section);
                $conf->save();
            }
        }
    }

    protected function removeDeprecatedKeysFromLocalConfig(LocalConfigurationHelpers $helpers)
    {
        $localConfigIni = $helpers->getConfigIni();

        // remove deprecated key from localconfig.ini.php
        $key = $localConfigIni->getValue('persistant_encryption_key', 'coordplugin_auth');
        if ($key !== null) {
            $localConfigIni->removeValue('persistant_encryption_key', 'coordplugin_auth');
        }
        $key = $localConfigIni->getValue('persistant_crypt_key', 'coordplugin_auth');
        if ($key !== null) {
            $localConfigIni->removeValue('persistant_crypt_key', 'coordplugin_auth');
        }
    }
}
