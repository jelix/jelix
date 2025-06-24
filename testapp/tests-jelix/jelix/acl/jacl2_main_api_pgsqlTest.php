<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007-2025 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

use Jelix\Core\Profiles;

require_once(__DIR__.'/jacl2.lib.php');

class jacl2_main_api_pgsqlTest extends jacl2APITest {

    public function setUp() : void {
        jDao::releaseAll();
        Profiles::clear();
        try {
            Profiles::get('jdb','testapp_pgsql', true);
            Profiles::createVirtualProfile('jdb','jacl2_profile', 'testapp_pgsql');
        }
        catch(Exception $e) {
            $this->markTestSkipped('jacl2_main_api_pgsqlTest cannot be run: '.$e->getMessage());
            return;
        }
        parent::setUp();
    }

    public function tearDown() : void {
        parent::tearDown();
        Profiles::getConnectorFromPool('jdb','testapp_pgsql')->close();
        jDao::releaseAll();
        Profiles::clear();
        jAcl2DbUserGroup::clearCache();
    }
}
