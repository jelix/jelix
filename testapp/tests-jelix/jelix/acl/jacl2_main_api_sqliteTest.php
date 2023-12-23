<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2022 Laurent Jouanneau
* @link        https://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jacl2.lib.php');

class jacl2_main_api_sqliteTest extends jacl2APITest {

    public function setUp() : void {
        jDao::releaseAll();
        jProfiles::clear();
        try {
            jProfiles::get('jdb','testapp_sqlite3', true);
            jProfiles::createVirtualProfile('jdb','jacl2_profile', 'testapp_sqlite3');
        }
        catch(Exception $e) {
            $this->markTestSkipped('jacl2_main_api_sqliteTest cannot be run: '.$e->getMessage());
            return;
        }
        parent::setUp();
    }

    public function tearDown() : void {
        parent::tearDown();
        jDao::releaseAll();
        jProfiles::clear();
        jAcl2DbUserGroup::clearCache();
    }
}
