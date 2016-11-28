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

        $conf = $this->getConfigIni()->getValue('auth', 'coordplugins');
        if ($conf == '1') {
            return;
        }

        $conff = jApp::varConfigPath($conf);
        if (!file_exists($conff)) {
            return;
        }

        $ini = new \Jelix\IniFile\IniModifier($conff);
        $driver = $ini->getValue('driver');
        if ($driver != 'ldap') {
            return;
        }

        if (!$this->firstExec('authconf-'.$conf)) {
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
        $section = 'authldap:'.$conf.$suffix;
        $ini->setValue('profile', $conf.$suffix, 'ldap');
        foreach(array('hostname', 'port', 'ldapUser', 'ldapPassword', 'protocolVersion')
                as $prop) {
            $val = $ini->getValue($prop, 'ldap');
            $profileIni->setValue($prop, $val, $section);
            $ini->removeValue($prop, 'ldap');
        }

        $profileIni->save();
        $ini->save();
    }
}
