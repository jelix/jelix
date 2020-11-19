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

class jacl2_main_apicacheTest extends jacl2APITest {

    public function setUp ()
    {
        self::$driver = 'dbcache';
        parent::setUp();
    }

    public function testIsMemberOfGroup(){
        parent::testIsMemberOfGroup();
    }

    /**
     * @depends testIsMemberOfGroup
     */
    public function testCheckRight(){
        parent::testCheckRight();
        $this->assertEquals(array (
                'super.cms.list' => true,
                'super.cms.update' => true,
                'admin.access' => false,
                'super.cms.create' => false,
                'super.cms.read' => false,
                'super.cms.delete' => false
            ),
            jCache::get('acl2db/laurent/rights', 'acl2db'));

        $this->assertEquals(array (
            154=>true, 122=>true
            ),
            jCache::get('acl2db/laurent/rightsres/super.cms.list', 'acl2db'));
        $this->assertEquals(array (
            154=>true, 122=>true
            ),
            jCache::get('acl2db/laurent/rightsres/super.cms.update', 'acl2db'));
        $this->assertEquals(array (
            154=>true, 122=>false
            ),
            jCache::get('acl2db/laurent/rightsres/super.cms.delete', 'acl2db'));

        $this->assertFalse(jCache::get('acl2dbanon/rights', 'acl2db'));

    }

    /**
    * @depends testCheckRightByUser
    */
    public function testAddRight(){
        // because of the right adding in the parent method,
        // cache has been clear
        parent::testAddRight();
        
        $this->assertEquals(array (
                'super.cms.list' => true,
                'super.cms.update' => true,
                'admin.access' => true,
            ),
            jCache::get('acl2db/laurent/rights', 'acl2db'));

        $this->assertFalse(jCache::get('acl2db/laurent/rightsres/super.cms.list', 'acl2db'));
        $this->assertFalse(jCache::get('acl2db/laurent/rightsres/super.cms.update', 'acl2db'));
        $this->assertFalse(jCache::get('acl2db/laurent/rightsres/super.cms.delete', 'acl2db'));
        $this->assertFalse(jCache::get('acl2dbanon/rights', 'acl2db'));

    }

    /**
    * @depends testAddRight
    */
    public function testCheckCanceledRight(){
        parent::testCheckCanceledRight();
        $this->assertEquals(array (
                'super.cms.list' => true,
                'super.cms.update' => false,
                'admin.access' => true,
                'super.cms.create' => false,
                'super.cms.read' => false,
                'super.cms.delete' => false
            ),
            jCache::get('acl2db/laurent/rights', 'acl2db'));

        $this->assertEquals(array (
            154=>true, 122=>true
            ),
            jCache::get('acl2db/laurent/rightsres/super.cms.list', 'acl2db'));
        $this->assertEquals(array (
            154=>false, 122=>false
            ),
            jCache::get('acl2db/laurent/rightsres/super.cms.update', 'acl2db'));
        $this->assertEquals(array (
            154=>true, 122=>false
            ),
            jCache::get('acl2db/laurent/rightsres/super.cms.delete', 'acl2db'));

        $this->assertFalse(jCache::get('acl2dbanon/rights', 'acl2db'));

    }

    /**
     * @depends testCheckCanceledRight
     */
    public function testGetRightDisconnect(){
        parent::testGetRightDisconnect();
        $this->assertFalse(jCache::get('acl2db/laurent/rights', 'acl2db'));
        $this->assertEquals(array (
                'super.cms.list' => true,
                'admin.access' => false,
            ),
            jCache::get('acl2dbanon/rights', 'acl2db'));

        $this->assertEquals(array (
            154=>true
            ),
            jCache::get('acl2dbanon/rightsres/super.cms.list', 'acl2db'));
        $this->assertFalse(jCache::get('acl2db/laurent/rightsres/super.cms.update', 'acl2db'));
        $this->assertFalse(jCache::get('acl2db/laurent/rightsres/super.cms.delete', 'acl2db'));
    }
}
