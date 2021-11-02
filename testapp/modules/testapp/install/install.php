<?php
/**
* @package     testapp
* @subpackage  testapp module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class testappModuleInstaller extends jInstallerModule {

    function install() {
        if ($this->firstDbExec()) {
            $this->execSQLScript('base');
            $this->execSQLScript('towns');
        }

        $this->createEntryPoint('ep/newep.php', 'ep/config.ini.php', $targetConfigDirName= '', $type='classic');


    }
}