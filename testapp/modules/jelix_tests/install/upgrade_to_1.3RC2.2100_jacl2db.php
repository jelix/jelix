<?php
/**
* @package     jelix
* @subpackage  jacl2db module
* @author      Laurent Jouanneau
* @copyright   2011 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
use Jelix\Core\Profiles;

class jelix_testsModuleUpgrader_jacl2db extends \Jelix\Installer\Module\Installer {

    protected $defaultDbProfile = 'testapp_pgsql';

    function install(\Jelix\Installer\Module\API\InstallHelpers $helpers) {
        try {
            $dbprofile = Profiles::get('jdb', 'testapp_pgsql', true);
            $helpers->database()->useDbProfile('testapp_pgsql');
        }
        catch(Exception $e) {
            // no profile for pgsql, don't install tables in pgsql
            return;
        }
        $helpers->database()->execSQLScript('install_jacl2.schema', 'jacl2db');
    }
}