<?php
/**
 * @package     jelix
 * @subpackage  jacl2db_admin
 * @author      Laurent Jouanneau
 * @copyright   2017 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

use \Jelix\IniFile\MultiIniModifier;
use \Jelix\IniFile\IniModifier;

class jacl2db_adminModuleUpgrader_webassets extends jInstallerModule2 {

    public $targetVersions = array('1.7.0-beta.2');

    public $date = '2017-02-07 19:18';

    function installEntrypoint(jInstallerEntryPoint2 $entryPoint) {
        $this->declareGlobalWebAssets('jacl2_admin', array('css'=>array('design/jacl2.css')), 'common', false);
    }
}

