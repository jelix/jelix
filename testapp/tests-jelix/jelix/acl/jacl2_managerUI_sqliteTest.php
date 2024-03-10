<?php
/**
 * @author      Laurent Jouanneau
 *
 * @copyright   2022-2023 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
require_once (__DIR__.'/jacl2_managerUITest.php');

class jacl2_managerUI_sqliteTest extends jacl2_managerUITest
{
    protected $numberAs = "integer";
    public function setUp(): void
    {
        jDao::releaseAll();
        jProfiles::clear();
        $this->preSetUpAcl();
        try {
            jProfiles::get('jdb', 'testapp_sqlite3', true);
            jProfiles::createVirtualProfile('jdb', 'jacl2_profile', 'testapp_sqlite3');
        } catch (Exception $e) {
            $this->markTestSkipped('jacl2_managerUI_sqliteTest cannot be run: ' . $e->getMessage());
            return;
        }
        $this->setUpAcl();
    }

    public function tearDown() : void
    {
        $this->teardownAcl();
        jDao::releaseAll();
        jDb::getConnection('testapp_sqlite3')->close();
        jProfiles::clear();
        jAcl2DbUserGroup::clearCache();
    }
}