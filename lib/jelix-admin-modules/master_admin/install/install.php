<?php
/**
* @package     jelix
* @subpackage  jauthdb_admin
* @author      Laurent Jouanneau
* @contributor
* @copyright   2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class master_adminModuleInstaller extends jInstallerModule {

    /**
     * @param jInstallerEntryPoint $ep
     */
    public function setEntryPoint($ep, $config, $dbProfile) {
        parent::setEntryPoint($ep, $config, $dbProfile);
        return md5('-' . $ep->file);
    }

    function install() {

    }
}