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

require_once(LIB_PATH.'jelix-modules/jacl2/classes/jAcl2.class.php');

abstract class jacl2APITest extends \Jelix\UnitTests\UnitTestCaseDb {

    protected static $driver = 'db';
    protected static $coordAuthPlugin = null;
    protected $oldAuthPlugin;

    public function setUp () : void {
        $this->dbProfile = 'jacl2_profile';
        self::initClassicRequest(TESTAPP_URL.'index.php');

        if (!self::$coordAuthPlugin) {
            jApp::config()->acl2['driver'] = self::$driver;
            jAcl2::unloadDriver();
            jAcl2::clearCache();

            require_once( JELIX_LIB_PATH.'plugins/coord/auth/auth.coord.php');
            $confContent = parse_ini_file(jApp::appSystemPath('auth_class.coord.ini.php'),true, INI_SCANNER_TYPED);
            $config = jAuth::loadConfig($confContent);
            self::$coordAuthPlugin = new AuthCoordPlugin($config);

            // prepare data
            $this->emptyTable('jacl2_rights');
            $this->emptyTable('jacl2_subject');
            $this->emptyTable('jacl2_user_group');

            $groups= array(array('id_aclgrp'=>'group1', 'name'=>'Groupe 1', 'grouptype'=>0, 'ownerlogin'=>null),
                           array('id_aclgrp'=>'gROUp-2', 'name'=>'Groupe 2', 'grouptype'=>0, 'ownerlogin'=>null));

            $this->insertRecordsIntoTable('jacl2_group', array('id_aclgrp','name','grouptype','ownerlogin'), $groups, true);

            $usergroups=array(
                array('login'=>'laurent', 'id_aclgrp'=>'group1'),
                array('login'=>'lau rent', 'id_aclgrp'=>'group1'),
            );
            $this->insertRecordsIntoTable('jacl2_user_group', array('login','id_aclgrp'), $usergroups, true);
        }

        $coord = jApp::coord();
        if (isset($coord->plugins['auth'])) {
            $this->oldAuthPlugin = $coord->plugins['auth'];
        }
        $coord->plugins['auth'] = self::$coordAuthPlugin;
        $_SESSION[self::$coordAuthPlugin->config['session_name']] = new jAuthDummyUser();
        jAuth::login('laurent','foo', false);
        jAcl2DbUserGroup::clearCache();
    }

    public function tearDown() : void {
        if ($this->oldAuthPlugin)
            jApp::coord()->plugins['auth'] = $this->oldAuthPlugin;
        else
            unset(jApp::coord()->plugins['auth']);
        unset($_SESSION[self::$coordAuthPlugin->config['session_name']]);
    }

    static function tearDownAfterClass() : void {
        self::$coordAuthPlugin = null;
    }

    public function testIsMemberOfGroup(){
        $this->assertTrue(jAcl2DbUserGroup::isMemberOfGroup ('group1'));
        $this->assertFalse(jAcl2DbUserGroup::isMemberOfGroup ('gROUp-2'));
    }

    /**
     * @depends testIsMemberOfGroup
     */
    public function testCheckRight(){
        jAcl2DbManager::createRight('super.cms.list', 'cms~rights.super.cms');
        jAcl2DbManager::createRight('super.cms.update', 'cms~rights.super.cms');
        jAcl2DbManager::createRight('super.cms.delete', 'cms~rights.super.cms');
        jAcl2DbManager::createRight('admin.access', 'admin~rights.access');
        jAcl2DbManager::addRight('group1', 'super.cms.list' );
        jAcl2DbManager::addRight('group1', 'super.cms.update' );
        jAcl2DbManager::addRight('group1', 'super.cms.delete', 154);

        $this->assertTrue(jAcl2::check('super.cms.list'));
        $this->assertTrue(jAcl2::check('super.cms.update'));
        $this->assertFalse(jAcl2::check('super.cms.create')); // doesn't exist
        $this->assertFalse(jAcl2::check('super.cms.read'));// doesn't exist
        $this->assertFalse(jAcl2::check('super.cms.delete'));// doesn't exist

        $this->assertFalse(jAcl2::check('admin.access'));
        $this->assertTrue(jAcl2::check('super.cms.list',154)); // droit sur une ressource
        $this->assertTrue(jAcl2::check('super.cms.update',154)); // droit sur une ressource
        $this->assertTrue(jAcl2::check('super.cms.delete',154)); // droit sur une ressource
        $this->assertTrue(jAcl2::check('super.cms.list',122)); // ressource non repertoriée
        $this->assertTrue(jAcl2::check('super.cms.update',122)); // ressource non repertoriée
        $this->assertFalse(jAcl2::check('super.cms.delete',122)); // ressource non repertoriée

    }

    /**
     * @depends testCheckRight
     */
    public function testCheckRightByUser(){
        jAcl2DbManager::createRight('super.cms.list', 'cms~rights.super.cms');
        jAcl2DbManager::createRight('super.cms.update', 'cms~rights.super.cms');
        jAcl2DbManager::createRight('super.cms.delete', 'cms~rights.super.cms');
        jAcl2DbManager::createRight('admin.access', 'admin~rights.access');
        jAcl2DbManager::addRight('group1', 'super.cms.list' );
        jAcl2DbManager::addRight('group1', 'super.cms.update' );
        jAcl2DbManager::addRight('group1', 'super.cms.delete', 154);

        $this->assertTrue(jAcl2::checkByUser('laurent', 'super.cms.list'));
        $this->assertTrue(jAcl2::checkByUser('laurent', 'super.cms.update'));
        $this->assertFalse(jAcl2::checkByUser('laurent', 'super.cms.create')); // doesn't exist
        $this->assertFalse(jAcl2::checkByUser('laurent', 'super.cms.read'));// doesn't exist
        $this->assertFalse(jAcl2::checkByUser('laurent', 'super.cms.delete'));// doesn't exist

        $this->assertFalse(jAcl2::checkByUser('laurent', 'admin.access'));
        $this->assertTrue(jAcl2::checkByUser('laurent', 'super.cms.list',154)); // droit sur une ressource
        $this->assertTrue(jAcl2::checkByUser('laurent', 'super.cms.update',154)); // droit sur une ressource
        $this->assertTrue(jAcl2::checkByUser('laurent', 'super.cms.delete',154)); // droit sur une ressource
        $this->assertTrue(jAcl2::checkByUser('laurent', 'super.cms.list',122)); // ressource non repertoriée
        $this->assertTrue(jAcl2::checkByUser('laurent', 'super.cms.update',122)); // ressource non repertoriée
        $this->assertFalse(jAcl2::checkByUser('laurent', 'super.cms.delete',122)); // ressource non repertoriée

        $this->assertTrue(jAcl2::checkByUser('lau rent', 'super.cms.list'));
        $this->assertTrue(jAcl2::checkByUser('lau rent', 'super.cms.update'));
        $this->assertFalse(jAcl2::checkByUser('lau rent', 'super.cms.create')); // doesn't exist
        $this->assertFalse(jAcl2::checkByUser('lau rent', 'super.cms.read'));// doesn't exist
        $this->assertFalse(jAcl2::checkByUser('lau rent', 'super.cms.delete'));// doesn't exist

        $this->assertFalse(jAcl2::checkByUser('lau rent', 'admin.access'));
        $this->assertTrue(jAcl2::checkByUser('lau rent', 'super.cms.list',154)); // droit sur une ressource
        $this->assertTrue(jAcl2::checkByUser('lau rent', 'super.cms.update',154)); // droit sur une ressource
        $this->assertTrue(jAcl2::checkByUser('lau rent', 'super.cms.delete',154)); // droit sur une ressource
        $this->assertTrue(jAcl2::checkByUser('lau rent', 'super.cms.list',122)); // ressource non repertoriée
        $this->assertTrue(jAcl2::checkByUser('lau rent', 'super.cms.update',122)); // ressource non repertoriée
        $this->assertFalse(jAcl2::checkByUser('lau rent', 'super.cms.delete',122)); // ressource non repertoriée
    }

    /**
    * @depends testCheckRightByUser
    */
    public function testAddRight(){
        jAcl2DbManager::addRight('group1', 'admin.access');

        $this->assertTrue(jAcl2::check('admin.access'));
        $this->assertTrue(jAcl2::checkByUser('laurent', 'admin.access'));
        $this->assertTrue(jAcl2::checkByUser('lau rent', 'admin.access'));

    }

    /**
    * @depends testAddRight
    */
    public function testCheckCanceledRight(){
        $usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>'gROUp-2'),
        );
        $this->insertRecordsIntoTable('jacl2_user_group', array('login','id_aclgrp'), $usergroups);
        jAcl2::clearCache();
        jAcl2DbUserGroup::clearCache();

        // it should cancel the right super.cms.update (which is set on group1)
        jAcl2DbManager::removeRight('gROUp-2', 'super.cms.update', '-', true);

        $this->assertTrue(jAcl2::check('super.cms.list'));
        $this->assertFalse(jAcl2::check('super.cms.update')); // is canceled
        $this->assertFalse(jAcl2::check('super.cms.create')); // doesn't exist
        $this->assertFalse(jAcl2::check('super.cms.read'));// doesn't exist
        $this->assertFalse(jAcl2::check('super.cms.delete'));// doesn't exist

        $this->assertTrue(jAcl2::check('admin.access'));
        $this->assertTrue(jAcl2::check('super.cms.list',154)); // droit sur une ressource
        $this->assertFalse(jAcl2::check('super.cms.update',154)); // droit sur une ressource
        $this->assertTrue(jAcl2::check('super.cms.delete',154)); // droit sur une ressource
        $this->assertTrue(jAcl2::check('super.cms.list',122)); // ressource non repertoriée
        $this->assertFalse(jAcl2::check('super.cms.update',122)); // ressource non repertoriée
        $this->assertFalse(jAcl2::check('super.cms.delete',122)); // ressource non repertoriée
    }


    /**
     * @depends testCheckCanceledRight
     */
    public function testGetRightDisconnect(){
        jAuth::logout();
        jAcl2::clearCache();
        jAcl2DbUserGroup::clearCache();
        $this->assertFalse(jAcl2::check('super.cms.list'));
        $this->assertFalse(jAcl2::check('admin.access'));
        jAcl2::clearCache();
        jAcl2DbUserGroup::clearCache();

        $groups= array(array('id_aclgrp'=>'__anonymous', 'name'=>'anonymous', 'grouptype'=>0, 'ownerlogin'=>null));
        $this->insertRecordsIntoTable('jacl2_group', array('id_aclgrp','name','grouptype','ownerlogin'), $groups, false);
        jAcl2DbManager::addRight('__anonymous', 'super.cms.list' );
        $this->assertTrue(jAcl2::check('super.cms.list'));
        $this->assertFalse(jAcl2::check('admin.access'));
        $this->assertTrue(jAcl2::check('super.cms.list',154));
    }
}
