<?php
/**
* @package     jelix
* @subpackage  jacl2db module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jacl2dbModuleInstaller extends jInstallerModule {

    function install() {
      $this->execSQLScript('install_jacl2.schema');
      $this->execSQLScript('install_jacl2.data');
    }
}