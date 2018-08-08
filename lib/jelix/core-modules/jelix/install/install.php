<?php
/**
* @package    jelix-modules
* @subpackage jelix-module
* @author      Laurent Jouanneau
* @copyright   2009-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jelixModuleInstaller extends jInstallerModule2 {

    function install() {

        // ---  install table for session storage if needed
        $sessionStorage = $this->getLocalConfigIni()->getValue("storage", "sessions");
        $sessionDao = $this->getLocalConfigIni()->getValue("dao_selector", "sessions");
        //$sessionProfile = $this->getLocalConfigIni->getValue("dao_db_profile", "sessions");

        if ($sessionStorage == "dao" &&
            $sessionDao == "jelix~jsession") {
            $this->execSQLScript('sql/install_jsession.schema');
        }

        // --- install table for jCache if needed
        $cachefile = jApp::varConfigPath('profiles.ini.php');

        if (file_exists($cachefile)) {
            $ini = new \Jelix\IniFile\IniModifier($cachefile);
            $dbProfileDone = [];

            foreach ($ini->getSectionList() as $section) {
                if (substr($section,0,7) != 'jcache:')
                    continue;
                $driver = $ini->getValue('driver', $section);
                $dao = $ini->getValue('dao', $section);
                $dbProfile = $ini->getValue('dbprofile', $section);

                if ($driver == 'db' &&
                    $dao == 'jelix~jcache' &&
                    !isset($dbProfileDone[$dbProfile])
                ) {
                    $this->useDbProfile($dbProfile);
                    $this->execSQLScript('sql/install_jcache.schema');
                    $dbProfileDone[$dbProfile] = true;
                }
            }
        }
    }
}