<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class testinstall2ModuleUpgrader_second extends jInstallerModule {

    public $testUseCommonId = true;

    public function setEntryPoint($ep, $config, $dbProfile) {
        parent::setEntryPoint($ep, $config, $dbProfile);
        if ($this->testUseCommonId)
            return "0";
        else
            return md5($ep->file);
    }


    function install() {

    }
}