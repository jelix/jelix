<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jacl2_main_api.lib.php');

class UTjacl2Pgsql extends UTjacl2_main_api {

    protected $backupProfile;
    protected $noPgsql = false;
    public function setUpRun (){
        jProfiles::clear();
        jDao::releaseAll();
        
        $this->backupProfile = jProfiles::get('jdb','jacl2_profile', true);
        try {
            $pgsql = jProfiles::get('jdb','testapp_pgsql', true);
            jProfiles::createVirtualProfile('jdb','jacl2_profile', 'testapp_pgsql');
        }
        catch(Exception $e) {
            $this->noPgsql = true;
        }
        parent::setUpRun();
    }

    function skip() {
        $this->skipIf($this->noPgsql, "Pgsql is not configured. %s");
    }

    public function tearDownRun (){
        parent::tearDownRun();
        jProfiles::clear();
        jDao::releaseAll();
        jAcl2DbUserGroup::clearCache();
        //jProfiles::createVirtualProfile('jdb','jacl2_profile', $this->backupProfile);
    }
}
