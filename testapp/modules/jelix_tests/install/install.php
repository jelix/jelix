<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor Brice Tence
* @copyright   2009 Laurent Jouanneau
* @copyright   2010 Brice Tence
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jelix_testsModuleInstaller extends \Jelix\Installer\ModuleInstaller {

    function installEntrypoint(\Jelix\Installer\EntryPoint $entryPoint) {

        if ($this->firstDbExec('mysql')) {
            $this->useDbProfile('default');
            $this->execSQLScript('install');
            //Create tables if they do not exist yet because of a specific configuration
            //(which is the case of testapp's out of the box config)
            $entryPoint->execSQLScript('sql/install_jsession.schema', 'jelix');
            $entryPoint->execSQLScript('sql/install_jcache.schema', 'jelix');
        }

        if ($this->firstDbExec('pgsql')) {
            try {
                $dbprofile = jProfiles::get('jdb', 'testapp_pgsql', true);
                $this->useDbProfile('testapp_pgsql');
            } catch (Exception $e) {
                // no profile for pgsql, don't install tables in pgsql
                return;
            }

            $this->execSQLScript('install');
            $entryPoint->execSQLScript('sql/install_jsession.schema', 'jelix');
            $entryPoint->execSQLScript('install_jacl2.schema', 'jacl2db');
        }
    }
}
