<?php
/**
* @package     jelix
* @subpackage  master_admin
* @author      Laurent Jouanneau
* @contributor
* @copyright   2010-2017 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class master_adminModuleInstaller extends jInstallerModule {

    function install() {
        $this->declareGlobalWebAssets('master_admin', array('css'=>array('design/master_admin.css')), 'common', false);
    }
}