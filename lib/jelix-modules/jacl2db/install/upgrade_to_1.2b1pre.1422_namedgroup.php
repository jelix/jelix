<?php

/**
* @package     jelix
* @subpackage  jacl2db module
* @author      Laurent Jouanneau
* @contributor Olivier Demah
* @copyright   2010 Laurent Jouanneau, Olivier Demah
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(dirname(__FILE__).'/_installclass.php');

class jacl2dbModuleUpgrader_namedgroup extends jacl2dbModuleInstallerBase {

    public function setEntryPoint($ep, $config, $dbProfile) {
        parent::setEntryPoint($ep, $config, $dbProfile);
        return md5($this->dbProfile);
    }

    function install() {
        $this->declareDbProfile('jacl2_profile', $this->dbProfile, false);
        $cn = $this->dbConnection();
        try {
            $cn->exec("ALTER TABLE ".$cn->prefixTable('jacl2_group')." ADD COLUMN code varchar(30) default NULL");
            echo "debug: upgrade jacl2db\n";
        } catch(Exception $e) {
            echo "upgrade jacl2db failed\n";
        }
    }
}