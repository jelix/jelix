<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2011 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class testinstall2ModuleUpgrader_newupgraderfilenamedate extends jInstallerModule2 {

    public $date = '2011-01-13';

    public $targetVersions = array('1.1.3', '1.2.2');

    function installEntrypoint(\Jelix\Installer\EntryPoint $entryPoint) {

    }
}