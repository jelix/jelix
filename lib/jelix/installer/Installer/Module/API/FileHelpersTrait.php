<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018-2019 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Module\API;

use Jelix\FileUtilities\Path;

/**
 * Trait for installer/configurator classes.
 *
 * For methods having a target path as parameter, the path may content these Jelix
 * shortcuts parts: 'www:', 'config:', 'var:', 'temp:', 'log:'.
 *
 * @since 1.7
 */
trait FileHelpersTrait
{
    /**
     * copy the whole content of a directory existing in the install/ directory
     * of the component, to the given directory.
     *
     * @param string $relativeSourcePath relative path to the install/ directory of the component
     * @param string $targetPath         the path where to copy the content.
     *                                   the path may content Jelix shortcuts, see FileHelpersTrait
     * @param mixed  $overwrite
     */
    public function copyDirectoryContent($relativeSourcePath, $targetPath, $overwrite = false)
    {
        $targetPath = $this->expandPath($targetPath);
        if (!Path::isAbsolute($relativeSourcePath)) {
            $relativeSourcePath = $this->globalSetup->getCurrentModulePath().'install/'.$relativeSourcePath;
        }
        \Jelix\FileUtilities\Directory::copy($relativeSourcePath, $targetPath, $overwrite);
    }

    /**
     * copy a file from the install/ directory to an other.
     *
     * @param string $relativeSourcePath relative path to the install/ directory of the file to copy
     * @param string $targetPath         the path where to copy the file.
     *                                   the path may content Jelix shortcuts, see FileHelpersTrait
     * @param mixed  $overwrite
     */
    public function copyFile($relativeSourcePath, $targetPath, $overwrite = false)
    {
        $targetPath = $this->expandPath($targetPath);
        if (!$overwrite && file_exists($targetPath)) {
            return;
        }
        $dir = dirname($targetPath);
        \jFile::createDir($dir);
        copy($this->globalSetup->getCurrentModulePath().'install/'.$relativeSourcePath, $targetPath);
    }

    /**
     * remove the whole content of a directory from the application.
     *
     * @param string $targetPath the path of the directory to remove
     *                           the path may content Jelix shortcuts, see FileHelpersTrait
     */
    public function removeDirectoryContent($targetPath)
    {
        $path = $this->expandPath($targetPath);
        \jFile::removeDir($path, true);
    }

    /**
     * delete a file from the the application.
     *
     * @param string $targetPath the path of the file
     *                           the path may content Jelix shortcuts, see FileHelpersTrait
     */
    public function removeFile($targetPath)
    {
        $path = $this->expandPath($targetPath);
        unlink($path);
    }

    protected function expandPath($path)
    {
        if (preg_match('/^([a-z]+)\\:/', $path, $m)) {
            switch ($m[1]) {
                case 'www':
                    $path = str_replace('www:', \jApp::wwwPath(), $path);

                    break;
                case 'varconfig':
                    $path = str_replace('varconfig:', \jApp::varConfigPath(), $path);

                    break;
                case 'appconfig':
                    $path = str_replace('appconfig:', \jApp::appSystemPath(), $path);

                    break;
                case 'appsystem':
                    $path = str_replace('appsystem:', \jApp::appSystemPath(), $path);

                    break;
                case 'config':
                    if ($this->globalSetup->forLocalConfiguration()) {
                        $path = str_replace('config:', \jApp::varConfigPath(), $path);
                    } else {
                        $path = str_replace('config:', \jApp::appSystemPath(), $path);
                    }

                    break;
                case 'var':
                    $path = str_replace('var:', \jApp::varPath(), $path);

                    break;
                case 'temp':
                    $path = str_replace('temp:', \jApp::tempPath(), $path);

                    break;
                case 'log':
                    $path = str_replace('log:', \jApp::logPath(), $path);

                    break;
                default:
                    throw new \InvalidArgumentException($m[1].' is an invalid shortcut');
            }
        }

        return $path;
    }

    /**
     * Install web files of a module.
     *
     * It supports different way to install : copying files, creating a symbolic link
     *   or do nothing (aka you should create an alias nto the vhost of the web server)
     *
     * @param string $wwwFilesMode     should be 'copy' or '' (files will be copied),
     *                                 'symlink' (a sym link is created) or any other value (do nothing/remove copied files)
     * @param string $wwwDirectoryName the path inside the www path
     * @param string $sourcePath       the path of the directory
     *
     * @throws \jException
     */
    public function setupModuleWebFiles($wwwFilesMode, $wwwDirectoryName, $sourcePath)
    {
        $targetPath = \jApp::wwwPath($wwwDirectoryName);
        $WWWDirExists = $WWWLinkExists = false;
        if (file_exists($targetPath)) {
            if (is_dir($targetPath)) {
                $WWWDirExists = true;
            } elseif (is_link($targetPath)) {
                $WWWDirExists = true;
            }
        }
        if ($wwwFilesMode == 'copy' || $wwwFilesMode == '') {
            if ($WWWLinkExists) {
                unlink($targetPath);
            }
            $this->copyDirectoryContent($sourcePath, $targetPath, true);
        } elseif ($wwwFilesMode == 'symlink' || $wwwFilesMode == 'filelink') {
            if ($WWWDirExists) {
                \jFile::removeDir($targetPath, true);
            }
            symlink($sourcePath, rtrim($targetPath, '/'));
        } elseif ($wwwFilesMode != 'nosetup') {
            if ($WWWLinkExists) {
                unlink($targetPath);
            }
            if ($WWWDirExists) {
                \jFile::removeDir($targetPath, true);
            }
        }
    }
}
