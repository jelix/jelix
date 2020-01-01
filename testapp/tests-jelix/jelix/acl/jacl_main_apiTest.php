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


class jacl_main_apiTest extends \Jelix\UnitTests\UnitTestCaseDb {

    protected static $coordAuthPlugin = null;
    protected $oldAuthPlugin;

    public function setUp () : void {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        if (!self::$coordAuthPlugin) {
            require_once( JELIX_LIB_PATH.'plugins/coord/auth/auth.coord.php');
            $confContent = parse_ini_file(jApp::appSystemPath('auth_class.coord.ini.php'),true, INI_SCANNER_TYPED);
            $config = jAuth::loadConfig($confContent);
            self::$coordAuthPlugin = new AuthCoordPlugin($config);
            $this->dbProfile = 'jacl_profile';
            $this->emptyTable('jacl_rights');
            $this->emptyTable('jacl_subject');
    
            $groups= array(array('id_aclgrp'=>1, 'name'=>'group1', 'grouptype'=>0, 'ownerlogin'=>null),
                           array('id_aclgrp'=>2, 'name'=>'group2', 'grouptype'=>0, 'ownerlogin'=>null));
    
            $this->insertRecordsIntoTable('jacl_group', array('id_aclgrp','name','grouptype','ownerlogin'), $groups, true);
    
            $usergroups=array(
                array('login'=>'laurent', 'id_aclgrp'=>1),
            );
            $this->insertRecordsIntoTable('jacl_user_group', array('login','id_aclgrp'), $usergroups, true);
    
    
            $rvg= array(
                array('id_aclvalgrp'=>1, 'label_key'=>'jxacl~db.valgrp.truefalse', 'type_aclvalgrp'=>1),
                array('id_aclvalgrp'=>2, 'label_key'=>'jxacl~db.valgrp.crudl',     'type_aclvalgrp'=>0),
                array('id_aclvalgrp'=>3, 'label_key'=>'jxacl~db.valgrp.groups',    'type_aclvalgrp'=>0),
            );
            $this->insertRecordsIntoTable('jacl_right_values_group', array('id_aclvalgrp','label_key','type_aclvalgrp'), $rvg, true);
    
            $rv= array(
                array('value'=>'FALSE', 'label_key'=>'jxacl~db.valgrp.truefalse.false',  'id_aclvalgrp'=>1),
                array('value'=>'TRUE',  'label_key'=>'jxacl~db.valgrp.truefalse.true',   'id_aclvalgrp'=>1),
    
                array('value'=>'LIST',  'label_key'=>'jxacl~db.valgrp.crudl.list',       'id_aclvalgrp'=>2),
                array('value'=>'CREATE','label_key'=>'jxacl~db.valgrp.crudl.create',     'id_aclvalgrp'=>2),
                array('value'=>'READ',  'label_key'=>'jxacl~db.valgrp.crudl.read',       'id_aclvalgrp'=>2),
                array('value'=>'UPDATE','label_key'=>'jxacl~db.valgrp.crudl.update',     'id_aclvalgrp'=>2),
                array('value'=>'DELETE','label_key'=>'jxacl~db.valgrp.crudl.delete',     'id_aclvalgrp'=>2),
    
                array('value'=>'LIST',   'label_key'=>'jxacl~db.valgrp.groups.list',   'id_aclvalgrp'=>3),
                array('value'=>'CREATE', 'label_key'=>'jxacl~db.valgrp.groups.create', 'id_aclvalgrp'=>3),
                array('value'=>'RENAME', 'label_key'=>'jxacl~db.valgrp.groups.rename', 'id_aclvalgrp'=>3),
                array('value'=>'DELETE', 'label_key'=>'jxacl~db.valgrp.groups.delete', 'id_aclvalgrp'=>3),
            );
    
            $this->insertRecordsIntoTable('jacl_right_values', array('value','label_key','id_aclvalgrp'), $rv, true);
        
        }
        $coord = jApp::coord();
        if (isset($coord->plugins['auth']))
            $this->oldAuthPlugin = $coord->plugins['auth'];
        $_SESSION[self::$coordAuthPlugin->config['session_name']] = new jAuthDummyUser();
        jAuth::login('laurent','foo', false);
    }


    public function tearDown() : void {

        if ($this->oldAuthPlugin)
            jApp::coord()->plugins['auth'] = $this->oldAuthPlugin;
        else
            unset(jApp::coord()->plugins['auth']);
        unset($_SESSION[self::$coordAuthPlugin->config['session_name']]);
    }

    public function testIsMemberOfGroup(){
        $this->assertTrue(jAclDbUserGroup::isMemberOfGroup (1));
        $this->assertFalse(jAclDbUserGroup::isMemberOfGroup (2));
    }

    public function testGetRight(){
        jAclDbManager::addSubject('super.cms',2 , 'cms~rights.super.cms');
        jAclDbManager::addSubject('admin.access',1 , 'admin~rights.access');
        jAclDbManager::addRight(1, 'super.cms', 'LIST' );
        jAclDbManager::addRight(1, 'super.cms', 'UPDATE' );
        jAclDbManager::addRight(1, 'super.cms', 'DELETE' , 154);

        $this->assertEquals(array('LIST','UPDATE'), jAcl::getRight('super.cms')); // droit généraux sur le sujet super.cms
        $this->assertEquals(array(),                jAcl::getRight('admin.access'));
        $this->assertEquals(array('LIST','UPDATE', 'DELETE'), jAcl::getRight('super.cms',154)); // droit sur une ressource
        $this->assertEquals(array('LIST','UPDATE'), jAcl::getRight('super.cms',122)); // ressource non repertoriée

        jAclDbManager::addRight(1, 'admin.access', 'TRUE' );

        $this->assertEquals(array('TRUE'), jAcl::getRight('admin.access'));
    }

    public function testGetRightDisconnect(){
        jAuth::logout();
        jAcl::clearCache();
        $this->assertEquals(array(), jAcl::getRight('super.cms'));
        $this->assertEquals(array(), jAcl::getRight('admin.access'));
        jAcl::clearCache();
    }

    public function testCheck(){
        //jAcl::check($subject, $value, $resource=null)

        $this->assertTrue(jAcl::check('super.cms', 'LIST'));
        $this->assertTrue(jAcl::check('super.cms', 'UPDATE'));
        $this->assertFalse(jAcl::check('super.cms', 'CREATE'));
        $this->assertFalse(jAcl::check('super.cms', 'READ'));
        $this->assertFalse(jAcl::check('super.cms', 'DELETE'));

        $this->assertTrue(jAcl::check('admin.access', 'TRUE'));
        $this->assertFalse(jAcl::check('admin.access', 'FALSE'));

        $this->assertTrue(jAcl::check('super.cms', 'LIST',154));
        $this->assertTrue(jAcl::check('super.cms', 'UPDATE',154));
        $this->assertFalse(jAcl::check('super.cms', 'CREATE',154));
        $this->assertFalse(jAcl::check('super.cms', 'READ',154));
        $this->assertTrue(jAcl::check('super.cms', 'DELETE',154));

        // avec une ressource non repertoriée
        $this->assertTrue(jAcl::check('super.cms', 'LIST',22));
        $this->assertTrue(jAcl::check('super.cms', 'UPDATE',22));
        $this->assertFalse(jAcl::check('super.cms', 'CREATE',22));
        $this->assertFalse(jAcl::check('super.cms', 'READ',22));
        $this->assertFalse(jAcl::check('super.cms', 'DELETE',22));

        $this->assertFalse(jAcl::check('foo', 'bar'));
        $this->assertFalse(jAcl::check('foo', 'bar','baz'));
    }
}
