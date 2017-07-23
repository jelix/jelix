<?php
/**
* @package     jelix
* @subpackage  jacl2db_admin
* @author      Laurent Jouanneau
* @contributor
* @copyright   2017 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jacl2db_adminModuleInstaller extends jInstallerModule2 {

    function installEntrypoint(\Jelix\Installer\EntryPoint $entryPoint) {
        $this->declareGlobalWebAssets('jacl2_admin', array('css'=>array('design/jacl2.css')), 'common', false);
    }
}
