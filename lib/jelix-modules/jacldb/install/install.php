<?php
/**
* @package     jelix
* @subpackage  jacldb module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jacldbModuleInstaller extends jInstallerModule {

    
    public function setEntryPoint($ep, $config, $dbProfile) {
        $dbProfilesFile = $config->getValue('dbProfils');
        if ($dbProfilesFile == '')
            $dbProfilesFile = 'dbprofils.ini.php';
        $dbprofiles = parse_ini_file(JELIX_APP_CONFIG_PATH.$dbProfilesFile);
        if (isset($dbprofiles['jacl_profile'])) {
            if (is_string($dbprofiles['jacl_profile']))
                $dbProfile = $dbprofiles['jacl_profile'];
            else
                $dbProfile = 'jacl_profile';
        }
        parent::setEntryPoint($ep, $config, $dbProfile);

        return md5($ep->configFile.'-'.$dbProfile);
    }

    function install() {
        if ($this->entryPoint->type != 'cmdline')
            return;

        $aclconfig = $this->config->getValue('jacl','coordplugins');
        $aclconfigMaster = $this->config->getValue('jacl','coordplugins',null, true);
        $forWS = (in_array($this->entryPoint->type, array('json', 'jsonrpc', 'soap', 'xmlrpc')));

        $ownConfig = false;
        if (!$aclconfig || ($forWS && $aclconfigMaster == $aclconfig)) {

            $pluginIni = 'jacl.coord.ini.php';            
            $configDir = dirname($this->entryPoint->configFile).'/';
            
            // no configuration, let's install the plugin for the entry point
            $this->config->setValue('jacl', $configDir.$pluginIni,'coordplugins');
            $ownConfig = true;
            if (!file_exists(JELIX_APP_CONFIG_PATH.$configDir.$pluginIni)) {
                $this->copyFile('var/config/'.$pluginIni , JELIX_APP_CONFIG_PATH.$configDir.$pluginIni);
            }
            $aclconfig = $configDir.$pluginIni;
        }

        if ($forWS && $ownConfig) {
            $cf = new jIniFileModifier(JELIX_APP_CONFIG_PATH.$aclconfig);
            $cf->setValue('on_error', 1);
            $cf->save();
        }

        $this->declareDbProfile('jacl_profile', null, false);
        $driver = $this->config->getValue('driver','acl');
        if ($driver != 'db')
            $this->config->setValue('driver','db','acl');
        $this->execSQLScript('install_jacl.schema', 'jacl_profile');
        try {
            $this->execSQLScript('install_jacl.data', 'jacl_profile');
        }
        catch (Exception $e) {
        }
    }
}
