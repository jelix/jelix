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

class jelix_testsModuleInstaller extends jInstallerModule {

    function install() {

      $this->execSQLScript('install');

      try {
        $dbprofile = jDb::getProfile('testapp_pgsql', true);
      }
      catch(Exception $e) {
        // no profile for pgsql, don't install tables in pgsql
        return;
      }

      $this->execSQLScript('install', 'testapp_pgsql');

    }
}