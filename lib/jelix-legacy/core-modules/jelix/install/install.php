<?php
/**
* @package    jelix-modules
* @subpackage jelix-module
* @author      Laurent Jouanneau
* @copyright   2009-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/InstallTrait.php');


class jelixModuleInstaller extends \Jelix\Installer\Module\Installer {

    use \Jelix\JelixModule\InstallTrait;

    function install(\Jelix\Installer\Module\API\InstallHelpers $helpers) {

        // --- copy jelix-wwww files
        $this->setupWWWFiles($helpers);

        // ---  install table for session storage if needed
        $sessionStorage = $helpers->getConfigIni()->getValue("storage", "sessions");
        $sessionDao = $helpers->getConfigIni()->getValue("dao_selector", "sessions");
        //$sessionProfile = $this->getLocalConfigIni->getValue("dao_db_profile", "sessions");

        $database = $helpers->database();
        if ($sessionStorage == "dao" &&
            $sessionDao == "jelix~jsession") {
            $database->execSQLScript('sql/install_jsession.schema');
        }

        // --- install table for jCache if needed
        $ini = $helpers->getProfilesIni();
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
                $database->useDbProfile($dbProfile);
                $database->execSQLScript('sql/install_jcache.schema');
                $dbProfileDone[$dbProfile] = true;
            }
        }
    }

}