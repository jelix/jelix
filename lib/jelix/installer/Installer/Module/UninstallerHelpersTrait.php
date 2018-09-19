<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Installer\Module;

/**
 * Trait for uninstaller/configurator classes
 *
 * @since 1.7
 */
trait UninstallerHelpersTrait {

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

    /**
     * remove a db profile
     *
     * @param string $name  the name of the section/alias
     */
    protected function removeDbProfile($name) {

        $profiles = $this->getProfilesIni();
        if ($profiles->getValue($name, 'jdb')) {
            $profiles->removeValue($name, 'jdb');
            return;
        }
        if ($profiles->isSection('jdb:'.$name)) {
            $aliases = $profiles->getValues('jdb');
            foreach($aliases as $alias=>$profile) {
                if ($profile == $name) {
                    // if there are some aliases to the profile
                    // we don't remove it to avoid to break
                    // the application
                    return;
                }
            }
            $profiles->removeValue(null, 'jdb:'.$name);
        }
    }
}
