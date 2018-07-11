<?php
/**
* @package     jelix
* @subpackage  jauthdb module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2011 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * parameters for this installer
 *    - defaultuser      add a default user, admin
 */
class jauthdbModuleInstaller extends jInstallerModule2 {

    function installEntrypoint(\Jelix\Installer\EntryPoint $entryPoint) {
        //if ($this->entryPoint->getType() == 'cmdline')
        //    return;
        $config = $entryPoint->getConfigIni();
        $authConfig = $this->getCoordPluginConf($config, 'auth');
        if (!$authConfig) {
            return;
        }
        list($conf, $section) = $authConfig;
        if ($section === 0) {
            $section_db = 'Db';
        }
        else {
            $section_db = 'auth_db';
        }

        $tag = 'authdb-'.\Jelix\FileUtilities\Path::shortestPath(jApp::appPath(), $conf->getFileName());

        if ($this->firstExec($tag)) {
            // a config for the auth plugin exists, so we can install
            // the module, else we ignore it

            if (isset($entryPoint->getConfigObj()->coordplugin_auth['driver'])) {
                $driver = $entryPoint->getConfigObj()->coordplugin_auth['driver'];
            }
            else {
                $driver = $conf->getValue('driver');
            }

            if ($driver == '') {
                $driver = 'Db';
                $conf->setValue('driver', $section_db);
                $conf->setValue('dao','jauthdb~jelixuser', $section_db);
                $conf->save();
            }
            else if ($driver != 'Db') {
                return;
            }

            $this->useDbProfile($conf->getValue('profile', $section_db));

            // FIXME: should use the given dao to create the table
            $daoName = $conf->getValue('dao', $section_db);
            if ($daoName == 'jauthdb~jelixuser' && $this->firstDbExec()) {

                $this->execSQLScript('install_jauth.schema');
                if ($this->getParameter('defaultuser')) {
                    $cn = $this->dbConnection();
                    $rs = $cn->query("SELECT usr_login FROM ".$cn->prefixTable('jlx_user')." WHERE usr_login = 'admin'");
                    if (!$rs->fetch()) {
                        require_once(JELIX_LIB_PATH.'auth/jAuth.class.php');
                        require_once(JELIX_LIB_PATH.'plugins/auth/db/db.auth.php');

                        $arConfig = $conf->getValues();
                        $arConfig['Db'] = $conf->getValues($section_db);
                        $authConfig = jAuth::loadConfig($arConfig);

                        $driver = new dbAuthDriver($authConfig['Db']);
                        $passwordHash = $driver->cryptPassword('admin');
                        $cn->exec("INSERT INTO ".$cn->prefixTable('jlx_user')." (usr_login, usr_password, usr_email ) VALUES
                                ('admin', ".$cn->quote($passwordHash)." , 'admin@localhost.localdomain')");
                    }
                }
            }
        }
    }
}
