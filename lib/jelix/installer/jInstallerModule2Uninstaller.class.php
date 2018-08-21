<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2008-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * A class that does processing to uninstall a module from an instance of
 * the application. A module should have a class that inherits from it
 * in order to remove things from the application.
 *
 * @package     jelix
 * @subpackage  installer
 * @since 1.7
 */
class jInstallerModule2Uninstaller  extends jInstallerModule2Abstract implements jIInstallerComponent2Uninstaller {

    /**
     * @inheritdoc
     */
    function preUninstall() {

    }

    /**
     * @inheritdoc
     */
    function uninstall() {

    }

    /**
     * @inheritdoc
     */
    function postUninstall() {

    }

    /**
     * remove the whole content of a directory from the application
     *
     * @param string $targetPath the path of the directory to remove
     *                  the path may content Jelix shortcuts parts like www:, app:...
     */
    final protected function removeDirectoryContent($targetPath) {
        $path = jFile::parseJelixPath($targetPath);
        jFile::removeDir($path, true);
    }

    /**
     * delete a file from the the application
     * @param string $targetPath the path of the file
     *             the path may content Jelix shortcuts parts like www:, app:...
     */
    final protected function removeFile($targetPath) {
        $path = jFile::parseJelixPath($targetPath);
        unlink($path);
    }

    /**
     * remove a db profile
     *
     * @param string $name  the name of the section/alias
     */
    protected function removeDbProfile($name) {

        $profiles = $this->globalSetup->getProfilesIni();
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
        jProfiles::clear();
        return;
    }

    /**
     * remove web assets from the main configuration
     *
     * @param string $name the name of webassets
     * @param string $collection the name of the webassets collection
     */
    public function removeGlobalWebAssets($name, $collection)
    {
        $config = $this->globalSetup->getConfigIni();
        $this->globalSetup->removeWebAssetsFromConfig($config['main'], $name, $collection);
    }
}

