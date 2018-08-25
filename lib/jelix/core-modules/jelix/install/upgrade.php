<?php

/**
 * @package    jelix-modules
 * @subpackage jelix-module
 * @author     Laurent Jouanneau
 * @copyright  2018 Laurent Jouanneau
 * @link       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class jelixModuleUpgrader extends jInstallerModule2
{

    function install()
    {
        // update jelix-www content copied into the application
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
    }
}