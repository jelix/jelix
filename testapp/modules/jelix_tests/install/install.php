<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor Brice Tence
* @copyright   2009-2018 Laurent Jouanneau
* @copyright   2010 Brice Tence
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jelix_testsModuleInstaller extends jInstallerModule2 {

    function install() {

        // install tables into mysql
        $this->useDbProfile('default');
        $this->execSQLScript('install');
        //Create tables if they do not exist yet because of a specific configuration
        //(which is the case of testapp's out of the box config)
        $this->execSQLScript('sql/install_jsession.schema', 'jelix');
        $this->execSQLScript('sql/install_jcache.schema', 'jelix');

        // install tables into pgsql
        try {
            $dbprofile = jProfiles::get('jdb', 'testapp_pgsql', true);
            $this->useDbProfile('testapp_pgsql');
        } catch (Exception $e) {
            // no profile for pgsql, don't install tables in pgsql
            return;
        }

        $this->execSQLScript('install');
        $this->execSQLScript('sql/install_jsession.schema', 'jelix');
        $this->execSQLScript('install_jacl2.schema', 'jacl2db');
    }
}
