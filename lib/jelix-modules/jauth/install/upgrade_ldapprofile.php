<?php
/**
* @package     jelix
* @subpackage  jauth module
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jauthModuleUpgrader_ldapprofile extends jInstallerModule {

    public $targetVersions = array('1.7.0-beta.2');
    public $date = '2016-06-22 09:14';
$
    function install() {

        $authConfig = $this->getCoordPluginConf($this->getConfigIni(), 'auth');
        if (!$authConfig) {
            return;
        }
        list($conf, $section) = $authconfig;
        if ($section === 0) {
            $section_ldap = 'Ldap';
        }
        else {
            $section_ldap = 'auth_ldap';
        }

        $driver = $conf->getValue('driver', $section);
        if ($driver != 'ldap') {
            return;
        }

        $tag = 'authconfldap-'.\Jelix\FileUtilities\Path::shortestPath(jApp::appPath(), $conf->getFileName());
        if (!$this->firstExec($tag)) {
            return;
        }

        $profileIni = jApp::varConfigPath('profiles.ini.php');
        $suffix = '';
        while ($profileIni->isSection('authldap:'.$conf.$suffix)) {
            if ($suffix) {
                $suffix ++;
            }
            else {
                $suffix = 0;
            }
        }
        $sectionProfile = 'authldap:'.$conf.$suffix;
        $conf->setValue('profile', $conf.$suffix, $section_ldap);
        foreach(array('hostname', 'port', 'ldapUser', 'ldapPassword', 'protocolVersion')
                as $prop) {
            $val = $conf->getValue($prop, $section_ldap);
            $profileIni->setValue($prop, $val, $sectionProfile);
            $conf->removeValue($prop, $section_ldap);
        }

        $profileIni->save();
        $conf->save();
    }
}
