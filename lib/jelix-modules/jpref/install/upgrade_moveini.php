<?php
/**
 * @package     jelix-modules
 * @subpackage  jpref module
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2016 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 */
class jprefModuleUpgrader_moveini extends jInstallerModule2 {

    public $targetVersions = array('1.1');
    public $date = '2016-11-24';

    function installEntryPoint(\Jelix\Installer\EntryPoint $entryPoint) {
        $path = jApp::varConfigPath('preferences.ini.php');
        $newpath = jApp::appConfigPath('preferences.ini.php');
        if (file_exists($path) && !file_exists($newpath)) {
            rename($path, $newpath);
        }
    }
}
