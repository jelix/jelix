<?php
/**
 * @package    jelix-modules
 * @subpackage jelix-module
 *
 * @author      Laurent Jouanneau
 * @copyright   2009-2019 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jelixModuleInstaller extends \Jelix\Installer\Module\Installer
{
    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {

        // --- copy jelix-wwww files
        $helpers->setupModuleWebFiles(
            $this->getParameter('wwwfiles'),
            $helpers->getConfigIni()->getValue('jelixWWWPath', 'urlengine'),
            LIB_PATH.'jelix-www'
        );

        // ---  install table for session storage if needed
        $sessionStorage = $helpers->getConfigIni()->getValue('storage', 'sessions');
        $sessionDao = $helpers->getConfigIni()->getValue('dao_selector', 'sessions');
        //$sessionProfile = $this->getLocalConfigIni->getValue("dao_db_profile", "sessions");

        $database = $helpers->database();
        if ($sessionStorage == 'dao' &&
            $sessionDao == 'jelix~jsession') {
            $database->execSQLScript('sql/install_jsession.schema');
        }

        // --- install table for jCache if needed
        $ini = $helpers->getProfilesIni();
        $dbProfileDone = array();

        foreach ($ini->getSectionList() as $section) {
            if (substr($section, 0, 7) != 'jcache:') {
                continue;
            }
            $driver = $ini->getValue('driver', $section);
            $dao = $ini->getValue('dao', $section);
            $dbProfile = $ini->getValue('dbprofile', $section);

            if ($driver == 'db' &&
                $dao == 'jelix~jcache' &&
                !isset($dbProfileDone[$dbProfile])
            ) {
                $database->useDbProfile($dbProfile);
                $database->execSQLScript('sql/install_jcache.schema');
                $dbProfileDone[$dbProfile] = true;
            }
        }
    }
}
