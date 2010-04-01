<?php

/**
* @package     jelix
* @subpackage  jacl2db module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(dirname(__FILE__).'/_installclass.php');

class jacl2dbModuleUpgrader_namedgroup extends jacl2dbModuleInstallerBase {


    function install() {
        $this->declareDbProfile('jacl2_profile', $this->dbProfile, false);
        $cn = $this->dbConnection();
        //try {
            $cn->exec("ALTER TABLE jacl2_group ADD COLUMN code varchar(30) default NULL");
        /*}
        catch(Exception $e) {
            
        }*/
echo " UPGRADE jacl2db\n";
    }

}