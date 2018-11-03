<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Installer\Module\API;

/**
 * Trait for installer/configurator classes
 *
 * @since 1.7
 */
trait FileHelpersTrait
{

    /**
     * copy the whole content of a directory existing in the install/ directory
     * of the component, to the given directory
     * @param string $relativeSourcePath relative path to the install/ directory of the component
     * @param string $targetPath the full path where to copy the content
     */
    final protected function copyDirectoryContent($relativeSourcePath, $targetPath, $overwrite = false)
    {
        $targetPath = $this->expandPath($targetPath);
        $this->_copyDirectoryContent($this->globalSetup->getCurrentModulePath() . 'install/' . $relativeSourcePath, $targetPath, $overwrite);
    }

    /**
     * private function which copy the content of a directory to an other
     *
     * @param string $sourcePath
     * @param string $targetPath
     */
    private function _copyDirectoryContent($sourcePath, $targetPath, $overwrite)
    {
        \jFile::createDir($targetPath);
        $dir = new \DirectoryIterator($sourcePath);
        foreach ($dir as $dirContent) {
            if ($dirContent->isFile()) {
                $p = $targetPath . substr($dirContent->getPathName(), strlen($dirContent->getPath()));
                if ($overwrite || !file_exists($p))
                    copy($dirContent->getPathName(), $p);
            } else {
                if (!$dirContent->isDot() && $dirContent->isDir()) {
                    $newTarget = $targetPath . substr($dirContent->getPathName(), strlen($dirContent->getPath()));
                    $this->_copyDirectoryContent($dirContent->getPathName(), $newTarget, $overwrite);
                }
            }
        }
    }


    /**
     * copy a file from the install/ directory to an other
     * @param string $relativeSourcePath relative path to the install/ directory of the file to copy
     * @param string $targetPath the full path where to copy the file
     */
    final protected function copyFile($relativeSourcePath, $targetPath, $overwrite = false)
    {
        $targetPath = $this->expandPath($targetPath);
        if (!$overwrite && file_exists($targetPath))
            return;
        $dir = dirname($targetPath);
        \jFile::createDir($dir);
        copy($this->globalSetup->getCurrentModulePath() . 'install/' . $relativeSourcePath, $targetPath);
    }


    /**
     * remove the whole content of a directory from the application
     *
     * @param string $targetPath the path of the directory to remove
     *                  the path may content Jelix shortcuts parts like www:, app:...
     */
    final protected function removeDirectoryContent($targetPath) {
        $path = \jFile::parseJelixPath($targetPath);
        \jFile::removeDir($path, true);
    }

    /**
     * delete a file from the the application
     * @param string $targetPath the path of the file
     *             the path may content Jelix shortcuts parts like www:, app:...
     */
    final protected function removeFile($targetPath) {
        $path = \jFile::parseJelixPath($targetPath);
        unlink($path);
    }

    protected function expandPath($path)
    {
        if (strpos($path, 'www:') === 0)
            $path = str_replace('www:', \jApp::wwwPath(), $path);
        elseif (strpos($path, 'jelixwww:') === 0) {
            $p = $this->getConfigIni()->getValue('jelixWWWPath', 'urlengine');
            if (substr($p, -1) != '/') {
                $p .= '/';
            }
            $path = str_replace('jelixwww:', \jApp::wwwPath($p), $path);
        } elseif (strpos($path, 'varconfig:') === 0) {
            $path = str_replace('varconfig:', \jApp::varConfigPath(), $path);
        } elseif (strpos($path, 'appconfig:') === 0) {
            $path = str_replace('appconfig:', \jApp::appConfigPath(), $path);
        } elseif (strpos($path, 'epconfig:') === 0) {
            throw new \Exception("'epconfig:' alias is no more supported in path");
        } elseif (strpos($path, 'config:') === 0) {
            throw new \Exception("'config:' alias is no more supported in path");
        }
        return $path;
    }


}