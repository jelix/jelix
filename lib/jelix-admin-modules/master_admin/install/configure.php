<?php
/**
 * @package     jelix
 * @subpackage  master_admin
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class master_adminModuleConfigurator extends jInstallerModuleConfigurator {

    public function configure() {
        $this->declareGlobalWebAssets('master_admin', array('css'=>array('design/master_admin.css')), 'common', false);
    }

}