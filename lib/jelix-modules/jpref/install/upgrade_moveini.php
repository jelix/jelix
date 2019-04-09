<?php
/**
 * @package     jelix-modules
 * @subpackage  jpref module
 *
 * @author      Laurent Jouanneau
 * @contributor
 *
 * @copyright   2016 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jprefModuleUpgrader_moveini extends \Jelix\Installer\Module\Installer
{
    protected $targetVersions = array('1.1');
    protected $date = '2016-11-24';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $path = jApp::varConfigPath('preferences.ini.php');
        $newpath = jApp::appSystemPath('preferences.ini.php');
        if (file_exists($path) && !file_exists($newpath)) {
            rename($path, $newpath);
        }
    }
}
