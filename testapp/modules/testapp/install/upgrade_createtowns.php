<?php
/**
* @package     testapp
* @subpackage  testapp module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class testappModuleUpgrader_createtowns extends jInstallerModule {

    public $targetVersions = array('1.2.10pre.1912', '1.3.4pre.2262', '1.4.1pre.2451', '1.5a1.2538');
    public $date = '2012-09-26 09:02';

    function install() {
        if ($this->firstDbExec()) {
            $this->execSQLScript('towns');
        }
    }
}