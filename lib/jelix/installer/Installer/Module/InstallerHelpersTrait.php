<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Installer\Module;

/**
 * Trait for installer/configurator classes
 *
 * @since 1.7
 */
trait InstallerHelpersTrait
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
        $this->_copyDirectoryContent($this->path . 'install/' . $relativeSourcePath, $targetPath, $overwrite);
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
        copy($this->path . 'install/' . $relativeSourcePath, $targetPath);
    }

    /**
     * declare a new db profile. if the content of the section is not given,
     * it will declare an alias to the default profile
     *
     * @param string $name the name of the new section/alias
     * @param null|string|array $sectionContent the content of the new section, or null
     *     to create an alias.
     * @param boolean $force true:erase the existing profile
     * @return boolean true if the ini file has been changed
     */
    protected final function declareDbProfile($name, $sectionContent = null, $force = true)
    {
        $profiles = $this->getProfilesIni();
        if ($sectionContent == null) {
            if (!$profiles->isSection('jdb:' . $name)) {
                // no section
                if ($profiles->getValue($name, 'jdb') && !$force) {
                    // already a name
                    return false;
                }
            } else if ($force) {
                // existing section, and no content provided : we erase the section
                // and add an alias
                $profiles->removeValue('', 'jdb:' . $name);
            } else {
                return false;
            }
            $default = $profiles->getValue('default', 'jdb');
            if ($default) {
                $profiles->setValue($name, $default, 'jdb');
            } else // default is a section
                $profiles->setValue($name, 'default', 'jdb');
        } else {
            if ($profiles->getValue($name, 'jdb') !== null) {
                if (!$force)
                    return false;
                $profiles->removeValue($name, 'jdb');
            }
            if (is_array($sectionContent)) {
                foreach ($sectionContent as $k => $v) {
                    if ($force || !$profiles->getValue($k, 'jdb:' . $name)) {
                        $profiles->setValue($k, $v, 'jdb:' . $name);
                    }
                }
            } else {
                $profile = $profiles->getValue($sectionContent, 'jdb');
                if ($profile !== null) {
                    $profiles->setValue($name, $profile, 'jdb');
                } else
                    $profiles->setValue($name, $sectionContent, 'jdb');
            }
        }
        $profiles->save();
        \jProfiles::clear();
        return true;
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