<?php
/**
* @package     jelix
* @subpackage  jacl2db module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(dirname(__FILE__).'/_installclass.php');

/**
 * parameters for this installer
 *    - defaultgroups    add default groups admin, users, anonymous
 *    - defaultuser      add a default user, admin and add default groups
 */
class jacl2dbModuleInstaller extends jacl2dbModuleInstallerBase {

    function install() {
        if ($this->entryPoint->type == 'cmdline')
            return;

        $aclconfig = $this->config->getValue('jacl2','coordplugins');
        $aclconfigMaster = $this->config->getValue('jacl2','coordplugins',null, true);
        $forWS = (in_array($this->entryPoint->type, array('json', 'jsonrpc', 'soap', 'xmlrpc')));

        $ownConfig = false;
        if (!$aclconfig || ($forWS && $aclconfigMaster == $aclconfig)) {

            $pluginIni = 'jacl2.coord.ini.php';
            $configDir = dirname($this->entryPoint->configFile).'/';

            // no configuration, let's install the plugin for the entry point
            $this->config->setValue('jacl2', $configDir.$pluginIni,'coordplugins');
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

        $this->declareDbProfile('jacl2_profile', $this->dbProfile, false);
        $driver = $this->config->getValue('driver','acl2');
        if ($driver != 'db')
            $this->config->setValue('driver','db','acl2');
        $this->execSQLScript('install_jacl2.schema', 'jacl2_profile');

        $this->execSQLScript('data.sql','jacl2_profile');

        if ($this->getParameter('defaultuser') || $this->getParameter('defaultgroups')) {
            // declare some groups
            $this->execSQLScript('groups.sql', 'jacl2_profile');
            $cn = $this->dbConnection();
            // mysql ignore the value 0 and replace it by the next value of auto increment
            // so let's change it to 0
            try {
                $cn->exec("UPDATE ".$cn->prefixTable('jacl2_group')." SET id_aclgrp = 0 WHERE  name = 'anonymous'
                        AND code = 'anonymous' AND grouptype=1 and ownerlogin is null");
            } catch(Exception $e) {}

            if ($cn->dbms == 'pgsql') {
                $cn->exec("SELECT setval('".$cn->prefixTable('jacl2_group_id_aclgrp_seq')."', 2, true)");
            }
        }

        if ($this->getParameter('defaultuser')) {
            $this->execSQLScript('user.sql', 'jacl2_profile');
        }
    }
}
