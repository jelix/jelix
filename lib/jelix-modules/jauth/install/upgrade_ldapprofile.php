<?php
/**
* @package     jelix
* @subpackage  jauth module
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jauthModuleUpgrader_ldapprofile extends \Jelix\Installer\Module\Installer {

    protected $targetVersions = array('1.7.0-beta.2');
    protected $date = '2016-06-22 09:14';

    function install()
    {
        foreach($this->getEntryPointsList() as $entryPoint) {
            $this->updateLdapEpConf($entryPoint);
        }
    }

    protected $ldapConfFiles = array();

    function updateLdapEpConf(\Jelix\Installer\EntryPoint $entryPoint) {

        $authConfig = $this->getCoordPluginConf($entryPoint->getAppConfigIni(), 'auth');
        if (!$authConfig) {
            return;
        }
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
        }
        else {
            // the configuration is in the main configuration file
            $section_ldap = 'auth_ldap';
        }

        $profileIni = $this->getProfilesIni();
        $suffix = '';
        while ($profileIni->isSection('authldap:ldap'.$suffix)) {
            if ($suffix) {
                $suffix ++;
            }
            else {
                $suffix = 1;
            }
        }
        $sectionProfile = 'authldap:ldap'.$suffix;
        $conf->setValue('profile', 'ldap'.$suffix, $section_ldap);
        foreach(array('hostname', 'port', 'ldapUser', 'ldapPassword', 'protocolVersion')
                as $prop) {
            $val = $conf->getValue($prop, $section_ldap);
            $profileIni->setValue($prop, $val, $sectionProfile);
            $conf->removeValue($prop, $section_ldap);
        }
        $conf->save();
    }
}
