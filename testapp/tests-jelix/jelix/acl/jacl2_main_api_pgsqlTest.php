<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jacl2.lib.php');

class jacl2_main_api_pgsqlTest extends jacl2APITest {

    //protected $backupProfile;

    public function setUp(){
        jProfiles::clear();
        try {
            jProfiles::get('jdb','testapp_pgsql', true);
            jProfiles::createVirtualProfile('jdb','jacl2_profile', 'testapp_pgsql');
        }
        catch(Exception $e) {
            $this->markTestSkipped('jacl2_main_api_pgsqlTest cannot be run: '.$e->getMessage());
            return;
        }

        jDao::releaseAll();

        //$this->backupProfile = jProfiles::get('jdb','jacl2_profile', true);
        parent::setUp();
    }


    public function tearDown (){
        parent::tearDown();
        jProfiles::clear();
        jDao::releaseAll();
        jAcl2DbUserGroup::clearCache();
        //jProfiles::createVirtualProfile('jdb','jacl2_profile', $this->backupProfile);
    }
}
