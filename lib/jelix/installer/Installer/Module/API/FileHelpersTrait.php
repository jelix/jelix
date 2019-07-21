<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
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
}
