<?php

/**
 * @package     jelix
 * @subpackage  jacl2db
 *
 * @author      Laurent Jouanneau
 * @copyright   2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

use Jelix\Installer\Module\Installer;
use Jelix\Installer\Module\API\InstallHelpers;

class jacl2dbModuleUpgrader_increaseidaclloginlength extends Installer
{
    public $targetVersions = array('1.8.15-beta.1');
    public $date = '2024-01-09 16:30';

    public function install(InstallHelpers $helpers)
    {
        $db = $helpers->database()->dbConnection();

        if ($db->dbms == 'pgsql' || $db->dbms == 'mysql') {
            $helpers->database()->execSQLScript('sql/upgrade_increaseidaclloginlength');
        }
    }
}
