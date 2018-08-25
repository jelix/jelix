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

        // --- copy jelix-wwww files
        $wwwFilesMode = $this->getParameter('wwwfiles');
        $jelixWWWPath = $this->getConfigIni()->getValue('jelixWWWPath', 'urlengine');
        $targetPath = jApp::wwwPath($jelixWWWPath);
        $jelixWWWDirExists = $jelixWWWLinkExists = false;
        if (file_exists($targetPath)) {
            if (is_dir($targetPath)) {
                $jelixWWWDirExists = true;
            }
            else if (is_link($targetPath)) {
                $jelixWWWLinkExists = true;
            }
        }
        if ($wwwFilesMode == 'copy' || $wwwFilesMode == '' ) {
            if ($jelixWWWLinkExists) {
                unlink($targetPath);
            }
            $this->copyDirectoryContent('../../../../jelix-www/', $targetPath, true);
        }
        else if ($wwwFilesMode == 'link') {
            if ($jelixWWWDirExists) {
                jFile::removeDir($targetPath, true);
            }
            symlink(LIB_PATH.'jelix-www', $targetPath);
        }
        else {
            if ($jelixWWWLinkExists) {
                unlink($targetPath);
            }
            if ($jelixWWWDirExists) {
                jFile::removeDir($targetPath, true);
            }
        }


        // ---  install table for session storage if needed
        $sessionStorage = $this->getLocalConfigIni()->getValue("storage", "sessions");
        $sessionDao = $this->getLocalConfigIni()->getValue("dao_selector", "sessions");
        //$sessionProfile = $this->getLocalConfigIni->getValue("dao_db_profile", "sessions");

        if ($sessionStorage == "dao" &&
            $sessionDao == "jelix~jsession") {
            $this->execSQLScript('sql/install_jsession.schema');
        }

        // --- install table for jCache if needed
        $ini = $this->getProfilesIni();
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