<?php
/**
* @package  jelix
* @subpackage testapp
* @author   Jouanneau Laurent
* @contributor
* @copyright 2008 Jouanneau laurent
* @link     http://www.jelix.org
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

class appInstaller extends jInstallerApp {

    function install(){
         $this->reporter->message("Test ok");
        //$this->execSQLScript('install');
    }

    function uninstall(){
        //$this->execSQLScript('uninstall');
    }
}

?>