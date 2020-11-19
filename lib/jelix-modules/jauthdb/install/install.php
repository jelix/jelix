<?php
/**
 * @package     jelix
 * @subpackage  jauthdb module
 *
 * @author      Laurent Jouanneau
 * @contributor
 *
 * @copyright   2009-2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
use Jelix\Installer\Module\API\InstallHelpers;

/**
 * parameters for this installer
 *    - defaultuser      add a default user, admin.
 */
class jauthdbModuleInstaller extends \Jelix\Installer\Module\Installer
{
    protected $dbTablesInstalled = false;

    public function install(InstallHelpers $helpers)
    {
        $confList = array();
        foreach ($helpers->getEntryPointsList() as $entryPoint) {
            $authConfig = $entryPoint->getCoordPluginConfig('auth');
            if (!$authConfig) {
                continue;
            }
            /** @var \Jelix\IniFile\IniModifier $conf */
            list($conf, $section) = $authConfig;
            if ($section === 0) {
                $section_db = 'Db';
            } else {
                $section_db = 'auth_db';
            }
            $path = Jelix\FileUtilities\Path::shortestPath(jApp::appPath(), $conf->getFileName());
            if (!isset($confList[$path])) {
                $confList[$path] = true;
                $this->setupAuth($helpers, $conf, $section_db, $entryPoint->getConfigObj());
            }
        }
    }

    protected function setupAuth(InstallHelpers $helpers, Jelix\IniFile\IniModifier $conf, $section_db, $epConfig)
    {

        // a config for the auth plugin exists, so we can install
        // the module, else we ignore it

        if (isset($epConfig->coordplugin_auth['driver'])) {
            $driver = $epConfig->coordplugin_auth['driver'];
        } else {
            $driver = $conf->getValue('driver');
        }

        if ($driver == '') {
            $driver = 'Db';
            $conf->setValue('driver', $section_db);
            $conf->setValue('dao', 'jauthdb~jelixuser', $section_db);
            $conf->save();
        } elseif ($driver != 'Db') {
            return;
        }

        $helpers->database()->useDbProfile($conf->getValue('profile', $section_db));

        // FIXME: should use the given dao to create the table
        $daoName = $conf->getValue('dao', $section_db);
        if ($daoName == 'jauthdb~jelixuser' && !$this->dbTablesInstalled) {
            $this->dbTablesInstalled = true;
            $helpers->database()->execSQLScript('install_jauth.schema');
            if ($this->getParameter('defaultuser')) {
                $cn = $helpers->database()->dbConnection();
                $rs = $cn->query('SELECT usr_login FROM '.$cn->prefixTable('jlx_user')." WHERE usr_login = 'admin'");
                if (!$rs->fetch()) {
                    require_once JELIX_LIB_PATH.'auth/jAuth.class.php';
                    require_once JELIX_LIB_PATH.'plugins/auth/db/db.auth.php';

                    $arConfig = $conf->getValues();
                    $arConfig['Db'] = $conf->getValues($section_db);
                    $authConfig = jAuth::loadConfig($arConfig);

                    $driver = new dbAuthDriver($authConfig['Db']);
                    $passwordHash = $driver->cryptPassword('admin');
                    $cn->exec('INSERT INTO '.$cn->prefixTable('jlx_user')." (usr_login, usr_password, usr_email ) VALUES
                            ('admin', ".$cn->quote($passwordHash)." , 'admin@localhost.localdomain')");
                }
            }
        }
    }
}
