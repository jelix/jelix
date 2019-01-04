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

class testinstall2ModuleUpgrader_newupgraderfilename extends \Jelix\Installer\Module\Installer {

    protected $targetVersions = array('1.1.2', '1.2.4');

    function install(\Jelix\Installer\Module\API\InstallHelpers $helpers) {

    }
}