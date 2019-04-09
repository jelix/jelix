<?php
/**
 * @package    jelix-modules
 * @subpackage jelix-module
 *
 * @author      Laurent Jouanneau
 * @copyright   2009-2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\JelixModule;

trait InstallTrait
{
    protected function setupWWWFiles(\Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $wwwFilesMode = $this->getParameter('wwwfiles');
        $jelixWWWPath = $helpers->getConfigIni()->getValue('jelixWWWPath', 'urlengine');
        $targetPath = \jApp::wwwPath($jelixWWWPath);
        $jelixWWWDirExists = $jelixWWWLinkExists = false;
        if (file_exists($targetPath)) {
            if (is_dir($targetPath)) {
                $jelixWWWDirExists = true;
            } elseif (is_link($targetPath)) {
                $jelixWWWLinkExists = true;
            }
        }
        if ($wwwFilesMode == 'copy' || $wwwFilesMode == '') {
            if ($jelixWWWLinkExists) {
                unlink($targetPath);
            }
            $helpers->copyDirectoryContent(LIB_PATH.'jelix-www', $targetPath, true);
        } elseif ($wwwFilesMode == 'filelink') {
            if ($jelixWWWDirExists) {
                \jFile::removeDir($targetPath, true);
            }
            symlink(LIB_PATH.'jelix-www', $targetPath);
        } else {
            if ($jelixWWWLinkExists) {
                unlink($targetPath);
            }
            if ($jelixWWWDirExists) {
                \jFile::removeDir($targetPath, true);
            }
        }
    }
}
