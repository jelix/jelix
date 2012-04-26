<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007-2011 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

abstract class UTjacl2_main_api extends jUnitTestCaseDb {

    protected $dbProfile = 'jacl2_profile';

    protected $config;
    protected $oldAuthPlugin;

    public function setUpRun (){
        $conf = parse_ini_file(jApp::configPath().'auth_class.coord.ini.php',true);

        $coord = jApp::coord();
        require_once( JELIX_LIB_PATH.'plugins/coord/auth/auth.coord.php');
        if (isset($coord->plugins['auth']))
            $this->oldAuthPlugin = $coord->plugins['auth'];
        $coord->plugins['auth'] = new AuthCoordPlugin($conf);

        $this->config = & $coord->plugins['auth']->config;
        $_SESSION[$this->config['session_name']] = new jAuthDummyUser();

        // prepare data
        
        $this->emptyTable('jacl2_rights');
        $this->emptyTable('jacl2_subject');
        $this->emptyTable('jacl2_user_group');

        $groups= array(array('id_aclgrp'=>'group1', 'name'=>'Groupe 1', 'grouptype'=>0, 'ownerlogin'=>null),
                       array('id_aclgrp'=>'group2', 'name'=>'Groupe 2', 'grouptype'=>0, 'ownerlogin'=>null));

        $this->insertRecordsIntoTable('jacl2_group', array('id_aclgrp','name','grouptype','ownerlogin'), $groups, true);

        $usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>'group1'),
        );
        $this->insertRecordsIntoTable('jacl2_user_group', array('login','id_aclgrp'), $usergroups, true);
        
    }

    public function setUp (){
        jAuth::login('laurent','foo', false);
        jAcl2DbUserGroup::clearCache();
    }

    public function tearDownRun (){
        if ($this->oldAuthPlugin)
            jApp::coord()->plugins['auth'] = $this->oldAuthPlugin;
        else
            unset(jApp::coord()->plugins['auth']);
        unset($_SESSION[$this->config['session_name']]);
        $this->config = null;
    }

    public function testIsMemberOfGroup(){
        $this->assertTrue(jAcl2DbUserGroup::isMemberOfGroup ('group1'));
        $this->assertFalse(jAcl2DbUserGroup::isMemberOfGroup ('group2'));
    }

    public function testCheckRight(){
        jAcl2DbManager::addSubject('super.cms.list', 'cms~rights.super.cms');
        jAcl2DbManager::addSubject('super.cms.update', 'cms~rights.super.cms');
        jAcl2DbManager::addSubject('super.cms.delete', 'cms~rights.super.cms');
        jAcl2DbManager::addSubject('admin.access', 'admin~rights.access');
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

        jAcl2DbManager::addRight('group1', 'admin.access');

        $this->assertTrue(jAcl2::check('admin.access'));

    }


    public function testCheckCanceledRight(){
        $usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>'group2'),
        );
        $this->insertRecordsIntoTable('jacl2_user_group', array('login','id_aclgrp'), $usergroups);
        jAcl2::clearCache();
        jAcl2DbUserGroup::clearCache();

        // it should cancel the right super.cms.update (which is set on group1)
        jAcl2DbManager::removeRight('group2', 'super.cms.update', '', true);

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
        jAcl2::clearCache();
        jAcl2DbUserGroup::clearCache();
    }
}
