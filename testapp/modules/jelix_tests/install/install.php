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
use Jelix\Core\Profiles;

class jelix_testsModuleInstaller extends \Jelix\Installer\Module\Installer {

    function install(\Jelix\Installer\Module\API\InstallHelpers $helpers) {

        // install tables into mysql
        $helpers->database()->useDbProfile('default');
        $helpers->database()->execSQLScript('install');
        //Create tables if they do not exist yet because of a specific configuration
        //(which is the case of testapp's out of the box config)
        $helpers->database()->execSQLScript('sql/install_jsession.schema', 'jelix');
        $helpers->database()->execSQLScript('sql/install_jcache.schema', 'jelix');

        // install tables into pgsql
        try {
            $dbprofile = Profiles::get('jdb', 'testapp_pgsql', true);

        } catch (Exception $e) {
            // no profile for pgsql, don't install tables in pgsql
        }

        if ($dbprofile) {
            $helpers->database()->useDbProfile('testapp_pgsql');
            $helpers->database()->execSQLScript('install');
            $helpers->database()->execSQLScript('sql/install_jsession.schema', 'jelix');
            $helpers->database()->execSQLScript('install_jacl2.schema', 'jacl2db');
        }
        $dbprofile = null;

        // install tables into Sqlite
        try {
            $dbprofile = Profiles::get('jdb', 'testapp_sqlite3', true);
        } catch (Exception $e) {
            // no profile for sqlite3, don't install tables in sqlite
        }
        if ($dbprofile) {
            $helpers->database()->useDbProfile('testapp_sqlite3');
            $helpers->database()->execSQLScript('install');
            $helpers->database()->execSQLScript('sql/install_jsession.schema', 'jelix');
            $helpers->database()->execSQLScript('install_jacl2.schema', 'jacl2db');
        }
    }
}
