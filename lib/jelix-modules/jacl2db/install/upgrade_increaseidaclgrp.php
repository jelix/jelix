<?php

/**
 * @package     jelix
 * @subpackage  jacl2db module
 *
 * @author      Laurent Jouanneau
 * @copyright   2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jacl2dbModuleUpgrader_increaseidaclgrp extends \Jelix\Installer\Module\Installer
{
    public $targetVersions = array('1.6.36-rc.1', '1.7.11-rc.1');
    public $date = '2022-01-17 15:30';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {

        $db = $helpers->database()->dbConnection();

        if ($db->dbms == 'pgsql' || $db->dbms == 'mysql') {
            $db->execSQLScript('sql/upgrade_increaseidaclgrp');
        }
    }
}
