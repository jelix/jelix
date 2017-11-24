<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2017 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
require_once(LIB_PATH.'jelix-modules/jacl2/classes/jAcl2.class.php');
require_once(LIB_PATH.'jelix-admin-modules/jacl2db_admin/classes/AclAdminUIManager.class.php');

class jacl2_managerUITest extends jUnitTestCaseDb {

    protected static $driver = 'db';
    protected static $coordAuthPlugin = null;
    protected $oldAuthPlugin;

    public function setUp(){
        $this->dbProfile = 'jacl2_profile';
        self::initClassicRequest(TESTAPP_URL.'index.php');

        //if (!self::$coordAuthPlugin) {
            jApp::config()->acl2['driver'] = self::$driver;
            jAcl2::unloadDriver();
            jAcl2::clearCache();

            require_once( JELIX_LIB_PATH.'plugins/coord/auth/auth.coord.php');
            $confContent = parse_ini_file(jApp::appConfigPath('auth_class.coord.ini.php'),true);
            $config = jAuth::loadConfig($confContent);
            self::$coordAuthPlugin = new AuthCoordPlugin($config);

            // prepare data
            $this->emptyTable('jacl2_rights');

            $groups = array(
                array('id_aclgrp'=>'admins', 'name'=>'Admins', 'grouptype'=>0, 'ownerlogin'=>null),
                array('id_aclgrp'=>'users',  'name'=>'Users',   'grouptype'=>0, 'ownerlogin'=>null),
                array('id_aclgrp'=>'__priv_theadmin', 'name'=>'theadmin', 'grouptype'=>2, 'ownerlogin'=>'theadmin'),
                array('id_aclgrp'=>'__priv_oneuser',  'name'=>'oneuser',  'grouptype'=>2, 'ownerlogin'=>'oneuser'),
                array('id_aclgrp'=>'__priv_specificadmin',  'name'=>'specificadmin',  'grouptype'=>2, 'ownerlogin'=>'specificadmin'),
            );
            $this->insertRecordsIntoTable('jacl2_group',
                array('id_aclgrp','name','grouptype','ownerlogin'),
                $groups, true
            );

            $usergroups = array(
                array('login'=>'theadmin', 'id_aclgrp'=>'admins'),
                array('login'=>'theadmin', 'id_aclgrp'=>'__priv_theadmin'),
                array('login'=>'oneuser', 'id_aclgrp'=>'users'),
                array('login'=>'oneuser', 'id_aclgrp'=>'__priv_oneuser'),
                array('login'=>'specificadmin', 'id_aclgrp'=>'users'), // will have admin rights in his private group
                array('login'=>'specificadmin', 'id_aclgrp'=>'__priv_specificadmin'),
            );
            $this->insertRecordsIntoTable('jacl2_user_group',
                array('login','id_aclgrp'),
                $usergroups, true
            );

            $roles = array(
                // dummy roles
                array('id_aclsbj'=>'super.cms.list',   'label_key'=>'cms~rights.super.cms', 'id_aclsbjgrp'=>null),
                array('id_aclsbj'=>'super.cms.update', 'label_key'=>'cms~rights.super.cms', 'id_aclsbjgrp'=>null),
                array('id_aclsbj'=>'super.cms.delete', 'label_key'=>'cms~rights.super.cms', 'id_aclsbjgrp'=>null),
                // reserved admin roles
                array('id_aclsbj'=>'acl.user.modify',  'label_key'=>'', 'id_aclsbjgrp'=>null),
                array('id_aclsbj'=>'acl.group.modify', 'label_key'=>'', 'id_aclsbjgrp'=>null),
                array('id_aclsbj'=>'acl.group.delete', 'label_key'=>'', 'id_aclsbjgrp'=>null),
                array('id_aclsbj'=>'acl.users.modify', 'label_key'=>'', 'id_aclsbjgrp'=>null),
            );
            $this->insertRecordsIntoTable('jacl2_subject',
                array('id_aclsbj','label_key', 'id_aclsbjgrp'),
                $roles, true
            );

            $rights = array(
                array('id_aclgrp'=>'admins', 'id_aclsbj'=>'acl.user.modify',  'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'admins', 'id_aclsbj'=>'acl.group.modify', 'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'admins', 'id_aclsbj'=>'super.cms.list',   'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'admins', 'id_aclsbj'=>'super.cms.update', 'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'users',  'id_aclsbj'=>'super.cms.list',   'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'users',  'id_aclsbj'=>'super.cms.update', 'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'__priv_oneuser', 'id_aclsbj'=>'super.cms.delete', 'id_aclres'=>'123', 'canceled'=>0),
                array('id_aclgrp'=>'__priv_oneuser', 'id_aclsbj'=>'super.cms.delete', 'id_aclres'=>'456', 'canceled'=>1),
                array('id_aclgrp'=>'__priv_theadmin', 'id_aclsbj'=>'acl.group.delete', 'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'__priv_theadmin', 'id_aclsbj'=>'acl.users.modify', 'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'__priv_specificadmin', 'id_aclsbj'=>'acl.users.modify', 'id_aclres'=>'-', 'canceled'=>1),
                // rights for specificadmin are set in some tests
            );
            $this->insertRecordsIntoTable('jacl2_rights',
                array('id_aclsbj','id_aclgrp', 'id_aclres', 'canceled'),
                $rights, true
            );
        //}

        $coord = jApp::coord();
        if (isset($coord->plugins['auth']))
            $this->oldAuthPlugin = $coord->plugins['auth'];
        $coord->plugins['auth'] = self::$coordAuthPlugin;
        $_SESSION[self::$coordAuthPlugin->config['session_name']] = new jAuthDummyUser();
        jAcl2DbUserGroup::clearCache();
    }

    public function tearDown(){
        if ($this->oldAuthPlugin)
            jApp::coord()->plugins['auth'] = $this->oldAuthPlugin;
        else
            unset(jApp::coord()->plugins['auth']);
        unset($_SESSION[self::$coordAuthPlugin->config['session_name']]);
    }

    static function tearDownAfterClass() {
        self::$coordAuthPlugin = null;
    }

    public function testGetGroupRights() {
        jAuth::login('theadmin','foo', false);
        $mgr = new AclAdminUIManager();
        $rights = $mgr->getGroupRights();

        $verif='<array>
    <object>
        <string property="id_aclgrp" value="__anonymous" />
        <string property="name" value="Anonymous" />
        <integer property="grouptype" value="0" />
        <null property="ownerlogin"/>
    </object>
    <object>
        <string property="id_aclgrp" value="admins" />
        <string property="name" value="Admins" />
        <string property="grouptype" value="0" />
        <null property="ownerlogin"/>
    </object>
    <object>
        <string property="id_aclgrp" value="users" />
        <string property="name" value="Users" />
        <string property="grouptype" value="0" />
        <null property="ownerlogin"/>
    </object>
</array>';
        $this->assertComplexIdenticalStr($rights['groups'], $verif);

        $this->assertEquals(
            array(
                'acl.group.delete' => array (
                    '__anonymous' => false,
                    'admins' => '',
                    'users' => ''
                ),
                'acl.group.modify' => array (
                    '__anonymous' => false,
                    'admins' => 'y',
                    'users' => ''
                ),
                'acl.user.modify' => array (
                    '__anonymous' => false,
                    'admins' => 'y',
                    'users' => ''
                ),
                'acl.users.modify' => array (
                    '__anonymous' => false,
                    'admins' => '',
                    'users' => ''
                ),
                'super.cms.delete' => array (
                    '__anonymous' => false,
                    'admins' => '',
                    'users' => ''
                ),
                'super.cms.list' => array (
                    '__anonymous' => false,
                    'admins' => 'y',
                    'users' => 'y'
                ),
                'super.cms.update' => array (
                    '__anonymous' => false,
                    'admins' => 'y',
                    'users' => 'y'
                ),
            ),
            $rights['rights']
        );
        $this->assertEquals( array(),
            $rights['sbjgroups_localized']
        );
        $this->assertEquals(
            array(
                'acl.group.delete' => array (
                    'grp' => null,
                    'label' => 'acl.group.delete'
                ),
                'acl.group.modify' => array (
                    'grp' => null,
                    'label' => 'acl.group.modify'
                ),
                'acl.user.modify' => array (
                    'grp' => null,
                    'label' => 'acl.user.modify'
                ),
                'acl.users.modify' => array (
                    'grp' => null,
                    'label' => 'acl.users.modify'
                ),
                'super.cms.delete' => array (
                    'grp' => null,
                    'label' => 'super.cms.delete'
                ),
                'super.cms.list' => array (
                    'grp' => null,
                    'label' => 'super.cms.list'
                ),
                'super.cms.update' => array (
                    'grp' => null,
                    'label' => 'super.cms.update'
                ),
            ),
            $rights['subjects']
        );
        $this->assertEquals(
            array(
                'acl.group.delete' => array (),
                'acl.group.modify' => array (),
                'acl.user.modify' => array (),
                'acl.users.modify' => array (),
                'super.cms.delete' => array (),
                'super.cms.list' => array (),
                'super.cms.update' => array (),
            ),
            $rights['rightsWithResources']
        );
    }

    public function testGetGroupRightsWithResources() {
        jAuth::login('theadmin','foo', false);
        $mgr = new AclAdminUIManager();
        $rights = $mgr->getGroupRightsWithResources('admins');
        $this->assertEquals( array(),
            $rights['rightsWithResources']
        );
        $this->assertEquals( array(),
            $rights['subjects_localized']
        );
        $this->assertFalse($rights['hasRightsOnResources']);

        $rights = $mgr->getGroupRightsWithResources('users');
        $this->assertEquals( array(),
            $rights['rightsWithResources']
        );
        $this->assertEquals( array(),
            $rights['subjects_localized']
        );
        $this->assertFalse($rights['hasRightsOnResources']);

        $rights = $mgr->getGroupRightsWithResources('__priv_oneuser');
        $verif='<array>
            <array key="super.cms.delete">
                <object >
                    <string property="id_aclsbj" value="super.cms.delete" />
                    <string property="id_aclgrp" value="__priv_oneuser" />
                    <string property="id_aclres" value="123" />
                    <string property="canceled" value="0"/>
                </object>
                <object >
                    <string property="id_aclsbj" value="super.cms.delete" />
                    <string property="id_aclgrp" value="__priv_oneuser" />
                    <string property="id_aclres" value="456" />
                    <string property="canceled" value="1"/>
                </object>
            </array>
        </array>';
        $this->assertComplexIdenticalStr($rights['rightsWithResources'], $verif);
        $this->assertEquals( array(
            'super.cms.delete' => 'super.cms.delete'
            ),
            $rights['subjects_localized']
        );
        $this->assertTrue($rights['hasRightsOnResources']);

        $rights = $mgr->getGroupRightsWithResources('__priv_theadmin');
        $this->assertEquals( array(),
            $rights['rightsWithResources']
        );
        $this->assertEquals( array(),
            $rights['subjects_localized']
        );
        $this->assertFalse($rights['hasRightsOnResources']);

        $rights = $mgr->getGroupRightsWithResources('__priv_specificadmin');
        $this->assertEquals( array(),
            $rights['rightsWithResources']
        );
        $this->assertEquals( array(),
            $rights['subjects_localized']
        );
        $this->assertFalse($rights['hasRightsOnResources']);
    }


    public function testSaveNormalGroupRights() {
        jAuth::login('oneuser','pwd', false);
        $mgr = new AclAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'admins' => array(
                'acl.user.modify' =>'y',
                'acl.group.modify' =>'y',
                'super.cms.list' =>'y',
                'super.cms.update' =>'n', // change
            ),
            'users' => array(
                'super.cms.list' => 'y',
                'super.cms.update' => false, // change
                'super.cms.delete' => 'y', // change
            )
        );
        $mgr->saveGroupRights($rights);
        $newRights = $mgr->getGroupRights();
        $this->assertEquals(
            array(
                'acl.group.delete' => array (
                    '__anonymous' => false,
                    'admins' => '',
                    'users' => ''
                ),
                'acl.group.modify' => array (
                    '__anonymous' => false,
                    'admins' => 'y',
                    'users' => ''
                ),
                'acl.user.modify' => array (
                    '__anonymous' => false,
                    'admins' => 'y',
                    'users' => ''
                ),
                'acl.users.modify' => array (
                    '__anonymous' => false,
                    'admins' => '',
                    'users' => ''
                ),
                'super.cms.delete' => array (
                    '__anonymous' => false,
                    'admins' => '',
                    'users' => 'y'
                ),
                'super.cms.list' => array (
                    '__anonymous' => false,
                    'admins' => 'y',
                    'users' => 'y'
                ),
                'super.cms.update' => array (
                    '__anonymous' => false,
                    'admins' => 'n',
                    'users' => ''
                ),
            ),
            $newRights['rights']
        );
        $this->assertEquals(
            array(
                'acl.group.delete' => array (),
                'acl.group.modify' => array (),
                'acl.user.modify' => array (),
                'acl.users.modify' => array (),
                'super.cms.delete' => array (),
                'super.cms.list' => array (),
                'super.cms.update' => array (),
            ),
            $newRights['rightsWithResources']
        );
    }


    public function testSaveEmptyGroupRights() {
        jAuth::login('oneuser','pwd', false);
        $mgr = new AclAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'admins' => array(
                'acl.user.modify' =>'y',
                'acl.group.modify' =>'y',
                'super.cms.list' =>'y',
                'super.cms.update' =>'y',
            ),
            'users' => array()
        );
        $mgr->saveGroupRights($rights);
        $newRights = $mgr->getGroupRights();
        $this->assertEquals(
            array(
                'acl.group.delete' => array (
                    '__anonymous' => false,
                    'admins' => '',
                    'users' => ''
                ),
                'acl.group.modify' => array (
                    '__anonymous' => false,
                    'admins' => 'y',
                    'users' => ''
                ),
                'acl.user.modify' => array (
                    '__anonymous' => false,
                    'admins' => 'y',
                    'users' => ''
                ),
                'acl.users.modify' => array (
                    '__anonymous' => false,
                    'admins' => '',
                    'users' => ''
                ),
                'super.cms.delete' => array (
                    '__anonymous' => false,
                    'admins' => '',
                    'users' => ''
                ),
                'super.cms.list' => array (
                    '__anonymous' => false,
                    'admins' => 'y',
                    'users' => ''
                ),
                'super.cms.update' => array (
                    '__anonymous' => false,
                    'admins' => 'y',
                    'users' => ''
                ),
            ),
            $newRights['rights']
        );
        $this->assertEquals(
            array(
                'acl.group.delete' => array (),
                'acl.group.modify' => array (),
                'acl.user.modify' => array (),
                'acl.users.modify' => array (),
                'super.cms.delete' => array (),
                'super.cms.list' => array (),
                'super.cms.update' => array (),
            ),
            $newRights['rightsWithResources']
        );

    }

    public function testRemoveGroupRightsWithResources() {
        jAuth::login('oneuser','pwd', false);
        $mgr = new AclAdminUIManager();

        $rights = array( // <id_aclsbj> => (true (remove), 'on'(remove) or '' (not touch)
                'super.cms.delete' =>'', // no change
        );
        $mgr->removeGroupRightsWithResources('__priv_oneuser', $rights);

        $rights = $mgr->getGroupRightsWithResources('__priv_oneuser');
        $verif='<array>
            <array key="super.cms.delete">
                <object >
                    <string property="id_aclsbj" value="super.cms.delete" />
                    <string property="id_aclgrp" value="__priv_oneuser" />
                    <string property="id_aclres" value="123" />
                    <string property="canceled" value="0"/>
                </object>
                <object >
                    <string property="id_aclsbj" value="super.cms.delete" />
                    <string property="id_aclgrp" value="__priv_oneuser" />
                    <string property="id_aclres" value="456" />
                    <string property="canceled" value="1"/>
                </object>
            </array>
        </array>';
        $this->assertComplexIdenticalStr($rights['rightsWithResources'], $verif);
        $this->assertEquals( array(
            'super.cms.delete' => 'super.cms.delete'
        ),
            $rights['subjects_localized']
        );
        $this->assertTrue($rights['hasRightsOnResources']);


        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
            'super.cms.delete' =>'on', // change
        );
        $mgr->removeGroupRightsWithResources('__priv_oneuser', $rights);

        $rights = $mgr->getGroupRightsWithResources('__priv_oneuser');
        $this->assertEquals( array(), $rights['rightsWithResources']);
        $this->assertEquals( array(), $rights['subjects_localized']);
        $this->assertFalse($rights['hasRightsOnResources']);


    }


    /**
     * @expectedException AclAdminUIException
     */
    /*public function testRemoveAllRights() {
        // it should fail because of some admin rights set on admins
        jAuth::login('oneuser','pwd', false);
        $mgr = new AclAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'admins' => array(),
            'users' => array()
        );
        $mgr->saveGroupRights($rights);
    }*/

    /**
     * it should fail
     * @expectedException AclAdminUIException
     */
    /*public function testNonAdminTryingToRemoveRightAdminOfAnAloneAdmin() {
        jAuth::login('oneuser','pwd', false);
        $mgr = new AclAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'admins' => array(
                'acl.user.modify' =>'y',
                'acl.group.modify' =>'', // change
                'super.cms.list' =>'y',
                'super.cms.update' =>'y',
            ),
            'users' => array(
                'super.cms.list' => 'y',
                'super.cms.update' => 'y',
            )
        );
        $mgr->saveGroupRights($rights);
    }

    public function testNonAdminTryingToRemovePrivateRightAdminOfAnAloneAdmin() {
        // it should fail
    }

    public function testNonAdminTryingToRemoveRightAdminOfOneOfAdmin() {
        // it should be ok
    }

    public function testNonAdminTryingToRemovePrivateRightAdminOfOneOfAdmin() {
        // it should be ok
    }

    public function testAdminTryingToRemoveHisRightAdmins() {
        // it should fail
    }

    public function testAdminTryingToRemoveHisPrivateRightAdmins() {
        // it should fail
    }

    public function testAdminTryingToRemoveRightAdminOfOtherAdmin() {
        // it should be ok
    }

    public function testAdminTryingToRemovePrivateRightAdminOfOtherAdmin() {
        // it should be ok
    }

    public function testNonAdminTryingToRemoveRightAdminAndToAddRightAdmin() {
        // it should be ok
    }*/

    /**
     *
     */
    public function testGetUsersList() {
        jAuth::login('theadmin','foo', false);
        $mgr = new AclAdminUIManager();
        $list = $mgr->getUsersList(AclAdminUIManager::FILTER_GROUP_ALL_USERS);

        $this->assertEquals(3, $list['usersCount']);
        $verif='<array>
                <object >
                    <string property="login" value="theadmin" />
                    <string property="id_aclgrp" value="__priv_theadmin" />
                    <array property="groups">
                        <object>
                            <string property="login" value="theadmin" />
                            <string property="id_aclgrp" value="admins" />
                            <string property="name" value="Admins" />
                            <string property="grouptype" value="0" />
                        </object>
                    </array>
                </object>
                <object >
                    <string property="login" value="oneuser" />
                    <string property="id_aclgrp" value="__priv_oneuser" />
                    <array property="groups">
                        <object>
                            <string property="login" value="oneuser" />
                            <string property="id_aclgrp" value="users" />
                            <string property="name" value="Users" />
                            <string property="grouptype" value="0" />
                        </object>
                    </array>
                </object>
                <object >
                    <string property="login" value="specificadmin" />
                    <string property="id_aclgrp" value="__priv_specificadmin" />
                    <array property="groups">
                        <object>
                            <string property="login" value="specificadmin" />
                            <string property="id_aclgrp" value="users" />
                            <string property="name" value="Users" />
                            <string property="grouptype" value="0" />
                        </object>
                    </array>
                </object>
        </array>';
        $this->assertComplexIdenticalStr($list['users'], $verif);
    }


    public function testGetUserRights() {
        jAuth::login('theadmin','foo', false);
        $mgr = new AclAdminUIManager();
        $rightsResult = $mgr->getUserRights('theadmin');

        $hisGroup = '<object>
                            <string property="login" value="theadmin" />
                            <string property="id_aclgrp" value="__priv_theadmin" />
                            <string property="name" value="theadmin" />
                            <string property="grouptype" value="2" />
                        </object>';
        $this->assertComplexIdenticalStr($rightsResult['hisgroup'], $hisGroup);

        $usergroups = '<array>
                        <object key="admins">
                            <string property="login" value="theadmin" />
                            <string property="id_aclgrp" value="admins" />
                            <string property="name" value="Admins" />
                            <string property="grouptype" value="0" />
                        </object>
                    </array>
        ';
        $this->assertComplexIdenticalStr($rightsResult['groupsuser'], $usergroups);

        $groups = '<array>
            <object>
                <string property="id_aclgrp" value="admins" />
                <string property="name" value="Admins" />
                <string property="grouptype" value="0" />
            </object>
            <object>
                <string property="id_aclgrp" value="users" />
                <string property="name" value="Users" />
                <string property="grouptype" value="0" />
            </object>
        </array>';
        $this->assertComplexIdenticalStr($rightsResult['groups'], $groups);

        $rights =  array(
            'acl.group.delete' => array (
                '__priv_theadmin' => 'y',
                'admins' => '',
                'users' => ''
            ),
            'acl.group.modify' => array (
                '__priv_theadmin' => false,
                'admins' => 'y',
                'users' => ''
            ),
            'acl.user.modify' => array (
                '__priv_theadmin' => false,
                'admins' => 'y',
                'users' => ''
            ),
            'acl.users.modify' => array (
                '__priv_theadmin' => 'y',
                'admins' => '',
                'users' => ''
            ),
            'super.cms.delete' => array (
                '__priv_theadmin' => false,
                'admins' => '',
                'users' => ''
            ),
            'super.cms.list' => array (
                '__priv_theadmin' => false,
                'admins' => 'y',
                'users' => 'y'
            ),
            'super.cms.update' => array (
                '__priv_theadmin' => false,
                'admins' => 'y',
                'users' => 'y'
            ),
        );
        $this->assertEquals($rights, $rightsResult['rights']);

        $this->assertEquals('theadmin', $rightsResult['user']);
        $this->assertEquals(
            array(
                'acl.group.delete' => array (
                    'grp' => null,
                    'label' => 'acl.group.delete'
                ),
                'acl.group.modify' => array (
                    'grp' => null,
                    'label' => 'acl.group.modify'
                ),
                'acl.user.modify' => array (
                    'grp' => null,
                    'label' => 'acl.user.modify'
                ),
                'acl.users.modify' => array (
                    'grp' => null,
                    'label' => 'acl.users.modify'
                ),
                'super.cms.delete' => array (
                    'grp' => null,
                    'label' => 'super.cms.delete'
                ),
                'super.cms.list' => array (
                    'grp' => null,
                    'label' => 'super.cms.list'
                ),
                'super.cms.update' => array (
                    'grp' => null,
                    'label' => 'super.cms.update'
                ),
            ),
            $rightsResult['subjects']
        );

        $this->assertEquals(array(), $rightsResult['sbjgroups_localized']);
        $this->assertEquals(
            array (
                'acl.group.delete' => 0,
                'acl.group.modify' => 0,
                'acl.user.modify' => 0,
                'acl.users.modify' => 0,
                'super.cms.delete' => 0,
                'super.cms.list' => 0,
                'super.cms.update' => 0,
            ),
            $rightsResult['rightsWithResources']
        );

        $this->assertFalse($rightsResult['hasRightsOnResources']);
    }

    public function testGetUserRightsWithResources() {
        jAuth::login('theadmin','foo', false);
        $mgr = new AclAdminUIManager();
        $rightsResult = $mgr->getUserRessourceRights('oneuser');

        $this->assertEquals('oneuser', $rightsResult['user']);
        $this->assertEquals(array(
            'super.cms.delete' => 'super.cms.delete',
        ), $rightsResult['subjects_localized']);
        $verif='<array>
            <array key="super.cms.delete">
                <object >
                    <string property="id_aclsbj" value="super.cms.delete" />
                    <string property="id_aclgrp" value="__priv_oneuser" />
                    <string property="id_aclres" value="123" />
                    <string property="canceled" value="0"/>
                </object>
                <object >
                    <string property="id_aclsbj" value="super.cms.delete" />
                    <string property="id_aclgrp" value="__priv_oneuser" />
                    <string property="id_aclres" value="456" />
                    <string property="canceled" value="1"/>
                </object>
            </array>
        </array>';
        $this->assertComplexIdenticalStr($rightsResult['rightsWithResources'], $verif);
        $this->assertTrue($rightsResult['hasRightsOnResources']);

        $rightsResult = $mgr->getUserRessourceRights('theadmin');
        $this->assertEquals('theadmin', $rightsResult['user']);
        $this->assertEquals(array(), $rightsResult['subjects_localized']);
        $this->assertEquals(array(), $rightsResult['rightsWithResources']);
        $this->assertFalse($rightsResult['hasRightsOnResources']);
    }

    public function testSaveNormalUserRights() {
        jAuth::login('oneuser','pwd', false);
        $mgr = new AclAdminUIManager();
        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
            'super.cms.list' => 'y',
            'super.cms.update' => false, // change
            'super.cms.delete' => 'y', // change
        );
        $mgr->saveUserRights('oneuser', $rights);

        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
            'acl.user.modify' =>'y',
            'acl.group.modify' =>'y',
            'acl.group.delete' =>'y',
            'super.cms.list' =>'y',
            'super.cms.update' =>'n', // change
        );
        $mgr->saveUserRights('theadmin', $rights);


        $newRights = $mgr->getUserRights('theadmin');
        $rights =  array(
            'acl.group.delete' => array (
                '__priv_theadmin' => 'y',
                'admins' => '',
                'users' => ''
            ),
            'acl.group.modify' => array (
                '__priv_theadmin' => 'y',
                'admins' => 'y',
                'users' => ''
            ),
            'acl.user.modify' => array (
                '__priv_theadmin' => 'y',
                'admins' => 'y',
                'users' => ''
            ),
            'acl.users.modify' => array (
                '__priv_theadmin' => false,
                'admins' => '',
                'users' => ''
            ),
            'super.cms.delete' => array (
                '__priv_theadmin' => false,
                'admins' => '',
                'users' => ''
            ),
            'super.cms.list' => array (
                '__priv_theadmin' => 'y',
                'admins' => 'y',
                'users' => 'y'
            ),
            'super.cms.update' => array (
                '__priv_theadmin' => 'n',
                'admins' => 'y',
                'users' => 'y'
            ),
        );
        $this->assertEquals($rights, $newRights['rights']);
        $this->assertEquals(
            array(
                'acl.group.delete' => 0,
                'acl.group.modify' => 0,
                'acl.user.modify' => 0,
                'acl.users.modify' => 0,
                'super.cms.delete' => 0,
                'super.cms.list' => 0,
                'super.cms.update' => 0
            ),
            $newRights['rightsWithResources']
        );


        $newRights = $mgr->getUserRights('oneuser');
        $rights =  array(
            'acl.group.delete' => array (
                '__priv_oneuser' => false,
                'admins' => '',
                'users' => ''
            ),
            'acl.group.modify' => array (
                '__priv_oneuser' => false,
                'admins' => 'y',
                'users' => ''
            ),
            'acl.user.modify' => array (
                '__priv_oneuser' => false,
                'admins' => 'y',
                'users' => ''
            ),
            'acl.users.modify' => array (
                '__priv_oneuser' => false,
                'admins' => '',
                'users' => ''
            ),
            'super.cms.delete' => array (
                '__priv_oneuser' => 'y',
                'admins' => '',
                'users' => ''
            ),
            'super.cms.list' => array (
                '__priv_oneuser' => 'y',
                'admins' => 'y',
                'users' => 'y'
            ),
            'super.cms.update' => array (
                '__priv_oneuser' => false,
                'admins' => 'y',
                'users' => 'y'
            ),
        );
        $this->assertEquals($rights, $newRights['rights']);
        $this->assertEquals(
            array(
                'acl.group.delete' => 0,
                'acl.group.modify' => 0,
                'acl.user.modify' => 0,
                'acl.users.modify' => 0,
                'super.cms.delete' => 2,
                'super.cms.list' => 0,
                'super.cms.update' => 0
            ),
            $newRights['rightsWithResources']
        );
    }

    public function testRemoveUserRightsWithResources() {
        jAuth::login('oneuser','pwd', false);
        $mgr = new AclAdminUIManager();
        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'super.cms.delete' => 'on', // change
        );
        $mgr->removeUserRessourceRights('oneuser', $rights);
        $newRights = $mgr->getUserRights('oneuser');
        $rights =  array(
            'acl.group.delete' => array (
                '__priv_oneuser' => false,
                'admins' => '',
                'users' => ''
            ),
            'acl.group.modify' => array (
                '__priv_oneuser' => false,
                'admins' => 'y',
                'users' => ''
            ),
            'acl.user.modify' => array (
                '__priv_oneuser' => false,
                'admins' => 'y',
                'users' => ''
            ),
            'acl.users.modify' => array (
                '__priv_oneuser' => false,
                'admins' => '',
                'users' => ''
            ),
            'super.cms.delete' => array (
                '__priv_oneuser' => false,
                'admins' => '',
                'users' => ''
            ),
            'super.cms.list' => array (
                '__priv_oneuser' => false,
                'admins' => 'y',
                'users' => 'y'
            ),
            'super.cms.update' => array (
                '__priv_oneuser' => false,
                'admins' => 'y',
                'users' => 'y'
            ),
        );
        $this->assertEquals($rights, $newRights['rights']);
        $this->assertEquals(
            array(
                'acl.group.delete' => 0,
                'acl.group.modify' => 0,
                'acl.user.modify' => 0,
                'acl.users.modify' => 0,
                'super.cms.delete' => 0,
                'super.cms.list' => 0,
                'super.cms.update' => 0
            ),
            $newRights['rightsWithResources']
        );
    }
}

