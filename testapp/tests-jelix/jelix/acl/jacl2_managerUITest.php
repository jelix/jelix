<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 *
 * @copyright   2017 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
require_once LIB_PATH.'jelix-modules/jacl2/classes/jAcl2.class.php';

/**
 * @internal
 * @coversNothing
 */
class jacl2_managerUITest extends \Jelix\UnitTests\UnitTestCaseDb
{
    protected static $driver = 'db';
    protected static $coordAuthPlugin = null;
    protected $oldAuthPlugin;

    public function setUp() : void
    {
        $this->dbProfile = 'jacl2_profile';
        self::initClassicRequest(TESTAPP_URL.'index.php');

        jApp::config()->acl2['driver'] = self::$driver;
        jAcl2::unloadDriver();
        jAcl2::clearCache();

        require_once JELIX_LIB_PATH.'plugins/coord/auth/auth.coord.php';
        $confContent = parse_ini_file(jApp::appSystemPath('auth_class.coord.ini.php'), true, INI_SCANNER_TYPED);
        $config = jAuth::loadConfig($confContent);
        self::$coordAuthPlugin = new AuthCoordPlugin($config);

        // prepare data
        $this->emptyTable('jacl2_rights');

        $groups = array(
            array('id_aclgrp'=>'admins', 'name'=>'Admins', 'grouptype'=>0, 'ownerlogin'=>null),
            array('id_aclgrp'=> 'users',  'name'=>'Users',   'grouptype'=>0, 'ownerlogin'=>null),
            array('id_aclgrp'=> '__priv_theadmin', 'name'=>'theadmin', 'grouptype'=>2, 'ownerlogin'=>'theadmin'),
            array('id_aclgrp'=> '__priv_oneuser',  'name'=>'oneuser',  'grouptype'=>2, 'ownerlogin'=>'oneuser'),
            array('id_aclgrp'=> '__priv_specificadmin',  'name'=>'specificadmin',  'grouptype'=>2, 'ownerlogin'=>'specificadmin'),
        );
        $this->insertRecordsIntoTable(
            'jacl2_group',
            array('id_aclgrp', 'name', 'grouptype', 'ownerlogin'),
            $groups,
            true
        );

        $usergroups = array(
            array('login'=>'theadmin', 'id_aclgrp'=>'admins'),
            array('login'=> 'theadmin', 'id_aclgrp'=>'__priv_theadmin'),
            array('login'=> 'oneuser', 'id_aclgrp'=>'users'),
            array('login'=> 'oneuser', 'id_aclgrp'=>'__priv_oneuser'),
            array('login'=> 'specificadmin', 'id_aclgrp'=>'users'), // will have admin rights in his private group
            array('login'=> 'specificadmin', 'id_aclgrp'=>'__priv_specificadmin'),
        );
        $this->insertRecordsIntoTable(
            'jacl2_user_group',
            array('login', 'id_aclgrp'),
            $usergroups,
            true
        );

        $roles = array(
            // dummy roles
            array('id_aclsbj'=>'super.cms.list',   'label_key'=>'cms~rights.super.cms', 'id_aclsbjgrp'=>null),
            array('id_aclsbj'=> 'super.cms.update', 'label_key'=>'cms~rights.super.cms', 'id_aclsbjgrp'=>null),
            array('id_aclsbj'=> 'super.cms.delete', 'label_key'=>'cms~rights.super.cms', 'id_aclsbjgrp'=>null),
            // reserved admin roles
            array('id_aclsbj'=>'acl.user.view',  'label_key'=>'', 'id_aclsbjgrp'=>null),
            array('id_aclsbj'=> 'acl.user.modify',  'label_key'=>'', 'id_aclsbjgrp'=>null),
            array('id_aclsbj'=> 'acl.group.view', 'label_key'=>'', 'id_aclsbjgrp'=>null),
            array('id_aclsbj'=> 'acl.group.modify', 'label_key'=>'', 'id_aclsbjgrp'=>null),
            array('id_aclsbj'=> 'acl.group.delete', 'label_key'=>'', 'id_aclsbjgrp'=>null),
        );
        $this->insertRecordsIntoTable(
            'jacl2_subject',
            array('id_aclsbj', 'label_key', 'id_aclsbjgrp'),
            $roles,
            true
        );

        $rights = array(
            array('id_aclgrp'=>'admins', 'id_aclsbj'=>'acl.user.view',  'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> 'admins', 'id_aclsbj'=>'acl.user.modify',  'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> 'admins', 'id_aclsbj'=>'acl.group.modify', 'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> 'admins', 'id_aclsbj'=>'super.cms.list',   'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> 'admins', 'id_aclsbj'=>'super.cms.update', 'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> 'users',  'id_aclsbj'=>'super.cms.list',   'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> 'users',  'id_aclsbj'=>'super.cms.update', 'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> '__priv_oneuser', 'id_aclsbj'=>'super.cms.delete', 'id_aclres'=>'123', 'canceled'=>0),
            array('id_aclgrp'=> '__priv_oneuser', 'id_aclsbj'=>'super.cms.delete', 'id_aclres'=>'456', 'canceled'=>1),
            array('id_aclgrp'=> '__priv_theadmin', 'id_aclsbj'=>'acl.group.delete', 'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> '__priv_theadmin', 'id_aclsbj'=>'acl.group.view', 'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> '__priv_specificadmin', 'id_aclsbj'=>'acl.group.view', 'id_aclres'=>'-', 'canceled'=>1),
            // rights for specificadmin are set in some tests
        );
        $this->insertRecordsIntoTable(
            'jacl2_rights',
            array('id_aclsbj', 'id_aclgrp', 'id_aclres', 'canceled'),
            $rights,
            true
        );

        $coord = jApp::coord();
        if (isset($coord->plugins['auth'])) {
            $this->oldAuthPlugin = $coord->plugins['auth'];
        }
        $coord->plugins['auth'] = self::$coordAuthPlugin;
        $_SESSION[self::$coordAuthPlugin->config['session_name']] = new jAuthDummyUser();
        jAcl2DbUserGroup::clearCache();
    }

    public function tearDown() : void
    {
        if ($this->oldAuthPlugin) {
            jApp::coord()->plugins['auth'] = $this->oldAuthPlugin;
        } else {
            unset(jApp::coord()->plugins['auth'], $_SESSION[self::$coordAuthPlugin->config['session_name']]);
        }
    }


    public static function tearDownAfterClass() : void
    {
        self::$coordAuthPlugin = null;
    }

    public function testGetGroupRights()
    {
        jAuth::login('theadmin', 'foo', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = $mgr->getGroupRights();

        $verif = '<array>
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
                'acl.group.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'acl.group.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.user.view' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.user.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.group.view' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'super.cms.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'super.cms.list' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => 'y',
                ),
                'super.cms.update' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => 'y',
                ),
            ),
            $rights['rights']
        );
        $this->assertEquals(
            array(),
            $rights['sbjgroups_localized']
        );
        $this->assertEquals(
            array(
                'acl.group.delete' => array(
                    'grp'   => null,
                    'label' => 'acl.group.delete',
                ),
                'acl.group.modify' => array(
                    'grp'   => null,
                    'label' => 'acl.group.modify',
                ),
                'acl.user.view' => array(
                    'grp'   => null,
                    'label' => 'acl.user.view',
                ),
                'acl.user.modify' => array(
                    'grp'   => null,
                    'label' => 'acl.user.modify',
                ),
                'acl.group.view' => array(
                    'grp'   => null,
                    'label' => 'acl.group.view',
                ),
                'super.cms.delete' => array(
                    'grp'   => null,
                    'label' => 'super.cms.delete',
                ),
                'super.cms.list' => array(
                    'grp'   => null,
                    'label' => 'super.cms.list',
                ),
                'super.cms.update' => array(
                    'grp'   => null,
                    'label' => 'super.cms.update',
                ),
            ),
            $rights['subjects']
        );
        $this->assertEquals(
            array(
                'acl.group.delete' => array(),
                'acl.group.modify' => array(),
                'acl.user.view'    => array(),
                'acl.user.modify'  => array(),
                'acl.group.view'   => array(),
                'super.cms.delete' => array(),
                'super.cms.list'   => array(),
                'super.cms.update' => array(),
            ),
            $rights['rightsWithResources']
        );
    }

    public function testGetGroupRightsWithResources()
    {
        jAuth::login('theadmin', 'foo', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = $mgr->getGroupRightsWithResources('admins');
        $this->assertEquals(
            array(),
            $rights['rightsWithResources']
        );
        $this->assertEquals(
            array(),
            $rights['subjects_localized']
        );
        $this->assertFalse($rights['hasRightsOnResources']);

        $rights = $mgr->getGroupRightsWithResources('users');
        $this->assertEquals(
            array(),
            $rights['rightsWithResources']
        );
        $this->assertEquals(
            array(),
            $rights['subjects_localized']
        );
        $this->assertFalse($rights['hasRightsOnResources']);

        $rights = $mgr->getGroupRightsWithResources('__priv_oneuser');
        $verif = '<array>
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
        $this->assertEquals(
            array(
                'super.cms.delete' => 'super.cms.delete',
            ),
            $rights['subjects_localized']
        );
        $this->assertTrue($rights['hasRightsOnResources']);

        $rights = $mgr->getGroupRightsWithResources('__priv_theadmin');
        $this->assertEquals(
            array(),
            $rights['rightsWithResources']
        );
        $this->assertEquals(
            array(),
            $rights['subjects_localized']
        );
        $this->assertFalse($rights['hasRightsOnResources']);

        $rights = $mgr->getGroupRightsWithResources('__priv_specificadmin');
        $this->assertEquals(
            array(),
            $rights['rightsWithResources']
        );
        $this->assertEquals(
            array(),
            $rights['subjects_localized']
        );
        $this->assertFalse($rights['hasRightsOnResources']);
    }

    public function testSaveNormalGroupRights()
    {
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'admins' => array(
                'acl.user.view'    => 'y',
                'acl.user.modify'  => 'y',
                'acl.group.modify' => 'y',
                'super.cms.list'   => 'y',
                'super.cms.update' => 'n', // change
            ),
            'users' => array(
                'super.cms.list'   => 'y',
                'super.cms.update' => false, // change
                'super.cms.delete' => 'y', // change
            ),
        );
        $mgr->saveGroupRights($rights, 'oneuser');
        $newRights = $mgr->getGroupRights();
        $this->assertEquals(
            array(
                'acl.group.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'acl.group.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.user.view' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.user.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.group.view' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'super.cms.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => 'y',
                ),
                'super.cms.list' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => 'y',
                ),
                'super.cms.update' => array(
                    '__anonymous' => false,
                    'admins'      => 'n',
                    'users'       => '',
                ),
            ),
            $newRights['rights']
        );
        $this->assertEquals(
            array(
                'acl.group.delete' => array(),
                'acl.group.modify' => array(),
                'acl.user.view'    => array(),
                'acl.user.modify'  => array(),
                'acl.group.view'   => array(),
                'super.cms.delete' => array(),
                'super.cms.list'   => array(),
                'super.cms.update' => array(),
            ),
            $newRights['rightsWithResources']
        );
    }

    public function testSaveEmptyGroupRights()
    {
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'admins' => array(
                'acl.user.view'    => 'y',
                'acl.user.modify'  => 'y',
                'acl.group.modify' => 'y',
                'super.cms.list'   => 'y',
                'super.cms.update' => 'y',
            ),
            'users' => array(),
        );
        $mgr->saveGroupRights($rights);
        $newRights = $mgr->getGroupRights();
        $this->assertEquals(
            array(
                'acl.group.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'acl.group.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.user.view' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.user.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.group.view' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'super.cms.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'super.cms.list' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'super.cms.update' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
            ),
            $newRights['rights']
        );
        $this->assertEquals(
            array(
                'acl.group.delete' => array(),
                'acl.group.modify' => array(),
                'acl.user.view'    => array(),
                'acl.user.modify'  => array(),
                'acl.group.view'   => array(),
                'super.cms.delete' => array(),
                'super.cms.list'   => array(),
                'super.cms.update' => array(),
            ),
            $newRights['rightsWithResources']
        );
    }

    /**
     */
    public function testTryRestorePartialAdminRightsToAUser()
    {
        // nobody have  acl.group.delete and acl.group.view
        $rights = array(
            array('id_aclgrp' => 'admins', 'id_aclsbj' => 'acl.group.modify', 'id_aclres' => '-', 'canceled' => 0),
            array('id_aclgrp' => 'admins', 'id_aclsbj' => 'acl.user.view', 'id_aclres' => '-', 'canceled' => 0),
            array('id_aclgrp' => 'admins', 'id_aclsbj' => 'acl.user.modify', 'id_aclres' => '-', 'canceled' => 0),
            array('id_aclgrp' => 'admins', 'id_aclsbj' => 'super.cms.list', 'id_aclres' => '-', 'canceled' => 0),
            array('id_aclgrp' => 'admins', 'id_aclsbj' => 'super.cms.update', 'id_aclres' => '-', 'canceled' => 0),
            array('id_aclgrp' => 'users', 'id_aclsbj' => 'super.cms.list', 'id_aclres' => '-', 'canceled' => 0),
            array('id_aclgrp' => 'users', 'id_aclsbj' => 'super.cms.update', 'id_aclres' => '-', 'canceled' => 0),
            array('id_aclgrp' => '__priv_oneuser', 'id_aclsbj' => 'super.cms.delete', 'id_aclres' => '123', 'canceled' => 0),
            array('id_aclgrp' => '__priv_oneuser', 'id_aclsbj' => 'super.cms.delete', 'id_aclres' => '456', 'canceled' => 1),
        );
        $this->insertRecordsIntoTable(
            'jacl2_rights',
            array('id_aclsbj', 'id_aclgrp', 'id_aclres', 'canceled'),
            $rights,
            true
        );

        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'acl.group.delete' => 'y',
            //'acl.group.view' =>'y',
        );

        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->saveUserRights('theadmin', $rights, 'theadmin');
    }

    public function testTryRestoreAdminRightsToAUser()
    {
        // nobody have  acl.group.delete and acl.group.view
        $rights = array(
            array('id_aclgrp'=>'admins', 'id_aclsbj'=>'acl.group.modify', 'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> 'admins', 'id_aclsbj'=>'acl.user.view',  'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> 'admins', 'id_aclsbj'=>'acl.user.modify',  'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> 'admins', 'id_aclsbj'=>'super.cms.list',   'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> 'admins', 'id_aclsbj'=>'super.cms.update', 'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> 'users',  'id_aclsbj'=>'super.cms.list',   'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> 'users',  'id_aclsbj'=>'super.cms.update', 'id_aclres'=>'-', 'canceled'=>0),
            array('id_aclgrp'=> '__priv_oneuser', 'id_aclsbj'=>'super.cms.delete', 'id_aclres'=>'123', 'canceled'=>0),
            array('id_aclgrp'=> '__priv_oneuser', 'id_aclsbj'=>'super.cms.delete', 'id_aclres'=>'456', 'canceled'=>1),
        );
        $this->insertRecordsIntoTable(
            'jacl2_rights',
            array('id_aclsbj', 'id_aclgrp', 'id_aclres', 'canceled'),
            $rights,
            true
        );

        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'acl.group.create' => '',
            'acl.group.delete' => 'y',
            'acl.group.modify' => '',
            'acl.group.view'   => 'y',
            'acl.user.modify'  => '',
            'acl.user.view'    => '',
        );
        $mgr->saveUserRights('theadmin', $rights, 'theadmin');

        $newRights = $mgr->getGroupRights();
        $this->assertEquals(
            array(
                'acl.group.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'acl.group.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.user.view' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.user.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.group.view' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'super.cms.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'super.cms.list' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => 'y',
                ),
                'super.cms.update' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => 'y',
                ),
            ),
            $newRights['rights']
        );
        $this->assertEquals(
            array(
                'acl.group.delete' => array(),
                'acl.group.modify' => array(),
                'acl.user.view'    => array(),
                'acl.user.modify'  => array(),
                'acl.group.view'   => array(),
                'super.cms.delete' => array(),
                'super.cms.list'   => array(),
                'super.cms.update' => array(),
            ),
            $newRights['rightsWithResources']
        );

        $rightsResult = $mgr->getUserRights('theadmin');

        $rights = array(
            'acl.group.delete' => array(
                '__priv_theadmin' => 'y',
                'admins'          => '',
                'users'           => '',
            ),
            'acl.group.modify' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.user.view' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.user.modify' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.group.view' => array(
                '__priv_theadmin' => 'y',
                'admins'          => '',
                'users'           => '',
            ),
            'super.cms.delete' => array(
                '__priv_theadmin' => false,
                'admins'          => '',
                'users'           => '',
            ),
            'super.cms.list' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => 'y',
            ),
            'super.cms.update' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => 'y',
            ),
        );
        $this->assertEquals($rights, $rightsResult['rights']);
        $this->assertFalse($rightsResult['hasRightsOnResources']);
    }

    public function testRemoveGroupRightsWithResources()
    {
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();

        $rights = array( // <id_aclsbj> => (true (remove), 'on'(remove) or '' (not touch)
            'super.cms.delete' => '', // no change
        );
        $mgr->removeGroupRightsWithResources('__priv_oneuser', $rights);

        $rights = $mgr->getGroupRightsWithResources('__priv_oneuser');
        $verif = '<array>
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
        $this->assertEquals(
            array(
                'super.cms.delete' => 'super.cms.delete',
            ),
            $rights['subjects_localized']
        );
        $this->assertTrue($rights['hasRightsOnResources']);

        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
            'super.cms.delete' => 'on', // change
        );
        $mgr->removeGroupRightsWithResources('__priv_oneuser', $rights);

        $rights = $mgr->getGroupRightsWithResources('__priv_oneuser');
        $this->assertEquals(array(), $rights['rightsWithResources']);
        $this->assertEquals(array(), $rights['subjects_localized']);
        $this->assertFalse($rights['hasRightsOnResources']);
    }

    /**
     */
    public function testRemoveAllRights()
    {
        // it should fail because of some admin rights set on admins
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'admins' => array(),
            'users'  => array(),
        );
        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->saveGroupRights($rights);
    }

    /**
     * it should fail
     */
    public function testNonAdminTryingToRemoveRightAdminOfAnAloneAdminGroup()
    {
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'admins' => array(
                'acl.user.view'    => 'y',
                'acl.user.modify'  => 'y',
                'acl.group.modify' => '', // change
                'super.cms.list'   => 'y',
                'super.cms.update' => 'y',
            ),
            'users' => array(
                'super.cms.list'   => 'y',
                'super.cms.update' => 'y',
            ),
        );
        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->saveGroupRights($rights);
    }

    /**
     * it should fail
     */
    public function testNonAdminTryingToRemovePrivateRightAdminOfAnAloneUserAdmin()
    {
        // it should fail
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
            'acl.group.delete' => '', // change, anybody else have this right
        );
        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->saveUserRights('theadmin', $rights);
    }

    public function testNonAdminTryingToRemoveRightAdminOfOneOfAdminGroup()
    {
        // add a right admin on users (we want to be sure it is set before
        // saveGroupRights, removing a right from a group and setting it to
        // another group is an other test)
        $this->insertRecordsIntoTable(
            'jacl2_rights',
            array('id_aclsbj', 'id_aclgrp', 'id_aclres', 'canceled'),
            array(array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.user.modify',  'id_aclres'=>'-', 'canceled'=>0)),
            false
        );

        // now let's remove the same right from admins
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'admins' => array(
                'acl.user.view'    => 'y',
                'acl.group.modify' => 'y',
                'acl.user.modify'  => '', // change
                'super.cms.list'   => 'y',
                'super.cms.update' => 'y',
            ),
            'users' => array(
                'super.cms.list'   => 'y',
                'super.cms.update' => 'y',
                'acl.user.modify'  => 'y',
            ),
        );
        $mgr->saveGroupRights($rights);

        $newRights = $mgr->getGroupRights();
        $this->assertEquals(
            array(
                'acl.group.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'acl.group.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.user.view' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.user.modify' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => 'y',
                ),
                'acl.group.view' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'super.cms.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'super.cms.list' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => 'y',
                ),
                'super.cms.update' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => 'y',
                ),
            ),
            $newRights['rights']
        );
        $this->assertEquals(
            array(
                'acl.group.delete' => array(),
                'acl.group.modify' => array(),
                'acl.user.view'    => array(),
                'acl.user.modify'  => array(),
                'acl.group.view'   => array(),
                'super.cms.delete' => array(),
                'super.cms.list'   => array(),
                'super.cms.update' => array(),
            ),
            $newRights['rightsWithResources']
        );
    }

    public function testNonAdminTryingToRemovePrivateRightAdminOfOneOfUserAdmin()
    {
        // add a right admin on users
        $this->insertRecordsIntoTable(
            'jacl2_rights',
            array('id_aclsbj', 'id_aclgrp', 'id_aclres', 'canceled'),
            array(array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.group.delete',  'id_aclres'=>'-', 'canceled'=>0)),
            false
        );

        // now remove the same right from a user
        jAuth::login('oneuser', 'foo', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
            'acl.group.view'   => 'y',
            'acl.group.delete' => '', // change

        );
        $mgr->saveUserRights('theadmin', $rights);

        $newRights = $mgr->getUserRights('theadmin');
        $rights = array(
            'acl.group.delete' => array(
                '__priv_theadmin' => false,
                'admins'          => '',
                'users'           => 'y',
            ),
            'acl.group.modify' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.user.view' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.user.modify' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.group.view' => array(
                '__priv_theadmin' => 'y',
                'admins'          => '',
                'users'           => '',
            ),
            'super.cms.delete' => array(
                '__priv_theadmin' => false,
                'admins'          => '',
                'users'           => '',
            ),
            'super.cms.list' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => 'y',
            ),
            'super.cms.update' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => 'y',
            ),
        );
        $this->assertEquals($rights, $newRights['rights']);
    }

    /**
     * it should fail
     */
    public function testAdminTryingToRemoveRightAdminsFromHisAdminGroup()
    {
        // it should fail
        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'admins' => array(
                'acl.group.modify' => 'y',
                'acl.user.modify'  => '', // change
                'super.cms.list'   => 'y',
                'super.cms.update' => 'y',
            ),
            'users' => array(
                'super.cms.list'   => 'y',
                'super.cms.update' => 'y',
                'acl.user.modify'  => 'y',
            ),
        );
        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->saveGroupRights($rights, 'theadmin');
    }

    /**
     * it should fail
     */
    public function testAdminTryingToRemoveHisPrivateRightAdmins()
    {
        // it should fail
        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
            'acl.user.modify'  => 'n', // override admins group right
            'acl.group.delete' => 'y',
            'acl.group.view'   => 'y',
        );
        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->saveUserRights('theadmin', $rights);
    }

    public function testAdminTryingToRemoveRightAdminOfOtherAdmin()
    {
        // add a right admin on users
        $this->insertRecordsIntoTable(
            'jacl2_rights',
            array('id_aclsbj', 'id_aclgrp', 'id_aclres', 'canceled'),
            array(array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.group.delete',  'id_aclres'=>'-', 'canceled'=>0)),
            false
        );

        // now remove the same right from a user
        jAuth::login('theadmin', 'foo', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
            'acl.group.view'   => 'y',
            'acl.group.delete' => '', // change

        );
        $mgr->saveUserRights('theadmin', $rights);

        $newRights = $mgr->getUserRights('theadmin');
        $rights = array(
            'acl.group.delete' => array(
                '__priv_theadmin' => false,
                'admins'          => '',
                'users'           => 'y',
            ),
            'acl.group.modify' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.user.view' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.user.modify' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.group.view' => array(
                '__priv_theadmin' => 'y',
                'admins'          => '',
                'users'           => '',
            ),
            'super.cms.delete' => array(
                '__priv_theadmin' => false,
                'admins'          => '',
                'users'           => '',
            ),
            'super.cms.list' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => 'y',
            ),
            'super.cms.update' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => 'y',
            ),
        );
        $this->assertEquals($rights, $newRights['rights']);
    }

    public function testAdminTryingToRemovePrivateRightAdminOfOtherAdmin()
    {
        // it should be ok
        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
            'acl.group.view' => '', // change
        );
        $mgr->saveUserRights('specificadmin', $rights);

        $newRights = $mgr->getUserRights('specificadmin');
        $rights = array(
            'acl.group.delete' => array(
                '__priv_specificadmin' => false,
                'admins'               => '',
                'users'                => '',
            ),
            'acl.group.modify' => array(
                '__priv_specificadmin' => false,
                'admins'               => 'y',
                'users'                => '',
            ),
            'acl.user.view' => array(
                '__priv_specificadmin' => false,
                'admins'               => 'y',
                'users'                => '',
            ),
            'acl.user.modify' => array(
                '__priv_specificadmin' => false,
                'admins'               => 'y',
                'users'                => '',
            ),
            'acl.group.view' => array(
                '__priv_specificadmin' => false,
                'admins'               => '',
                'users'                => '',
            ),
            'super.cms.delete' => array(
                '__priv_specificadmin' => false,
                'admins'               => '',
                'users'                => '',
            ),
            'super.cms.list' => array(
                '__priv_specificadmin' => false,
                'admins'               => 'y',
                'users'                => 'y',
            ),
            'super.cms.update' => array(
                '__priv_specificadmin' => false,
                'admins'               => 'y',
                'users'                => 'y',
            ),
        );
        $this->assertEquals($rights, $newRights['rights']);
    }

    public function testNonAdminTryingToRemoveRightAdminAndToAddRightAdmin()
    {
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'admins' => array(
                'acl.user.view'    => 'y',
                'acl.group.modify' => 'y',
                'acl.user.modify'  => '', // change
                'super.cms.list'   => 'y',
                'super.cms.update' => 'y',
            ),
            'users' => array(
                'super.cms.list'   => 'y',
                'super.cms.update' => 'y',
                'acl.user.modify'  => 'y',
            ),
        );
        $mgr->saveGroupRights($rights);

        $newRights = $mgr->getGroupRights();
        $this->assertEquals(
            array(
                'acl.group.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'acl.group.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.user.view' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.user.modify' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => 'y',
                ),
                'acl.group.view' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'super.cms.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'super.cms.list' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => 'y',
                ),
                'super.cms.update' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => 'y',
                ),
            ),
            $newRights['rights']
        );
        $this->assertEquals(
            array(
                'acl.group.delete' => array(),
                'acl.group.modify' => array(),
                'acl.user.view'    => array(),
                'acl.user.modify'  => array(),
                'acl.group.view'   => array(),
                'super.cms.delete' => array(),
                'super.cms.list'   => array(),
                'super.cms.update' => array(),
            ),
            $newRights['rightsWithResources']
        );
    }

    /**
     * it should fail
     */
    public function testAdminTryingToDeleteTheSingleAdminGroup()
    {
        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->removeGroup('admins');
    }

    /**
     * it should fail
     */
    public function testAdminTryingToDeleteItsOwnAdminGroup()
    {
        // create a new admin group with a user in it
        $this->insertRecordsIntoTable(
            'jacl2_group',
            array('id_aclgrp', 'name', 'grouptype', 'ownerlogin'),
            array(
                array('id_aclgrp'=>'admins2', 'name'=>'Admins2', 'grouptype'=>0, 'ownerlogin'=>null),
                array('id_aclgrp'=> '__priv_theadmin2', 'name'=>'theadmin2', 'grouptype'=>2, 'ownerlogin'=>'theadmin2'),
            ),
            false
        );
        $this->insertRecordsIntoTable(
            'jacl2_rights',
            array('id_aclsbj', 'id_aclgrp', 'id_aclres', 'canceled'),
            array(
                array('id_aclsbj'=>'acl.user.modify', 'id_aclgrp'=>'admins2', 'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclsbj'=> 'acl.group.modify', 'id_aclgrp'=>'admins2', 'id_aclres'=>'-', 'canceled'=>0),
            ),
            false
        );
        $this->insertRecordsIntoTable(
            'jacl2_user_group',
            array('login', 'id_aclgrp'),
            array(
                array('login'=>'theadmin2', 'id_aclgrp'=>'admins2'),
                array('login'=> 'theadmin2', 'id_aclgrp'=>'__priv_theadmin2'),
            ),
            false
        );

        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $this->expectException(jAcl2DbAdminUIException::class);
        $this->expectExceptionCode(3);
        $mgr->removeGroup('admins', 'theadmin');
    }

    public function testAdminTryingToDeleteOneOfAdminGroup()
    {
        // it should be ok
        // create a new admin group with a user in it
        $this->insertRecordsIntoTable(
            'jacl2_group',
            array('id_aclgrp', 'name', 'grouptype', 'ownerlogin'),
            array(
                array('id_aclgrp'=>'admins2', 'name'=>'Admins2', 'grouptype'=>0, 'ownerlogin'=>null),
                array('id_aclgrp'=> '__priv_theadmin2', 'name'=>'theadmin2', 'grouptype'=>2, 'ownerlogin'=>'theadmin2'),
            ),
            false
        );
        $this->insertRecordsIntoTable(
            'jacl2_rights',
            array('id_aclsbj', 'id_aclgrp', 'id_aclres', 'canceled'),
            array(
                array('id_aclsbj'=>'acl.user.modify', 'id_aclgrp'=>'admins2', 'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclsbj'=> 'acl.group.modify', 'id_aclgrp'=>'admins2', 'id_aclres'=>'-', 'canceled'=>0),
            ),
            false
        );
        $this->insertRecordsIntoTable(
            'jacl2_user_group',
            array('login', 'id_aclgrp'),
            array(
                array('login'=>'theadmin2', 'id_aclgrp'=>'admins2'),
                array('login'=> 'theadmin2', 'id_aclgrp'=>'__priv_theadmin2'),
            ),
            false
        );

        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $mgr->removeGroup('admins2');

        $newRights = $mgr->getGroupRights();
        $this->assertEquals(
            array(
                'acl.group.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'acl.group.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.user.view' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.user.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => '',
                ),
                'acl.group.view' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'super.cms.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => '',
                ),
                'super.cms.list' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => 'y',
                ),
                'super.cms.update' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => 'y',
                ),
            ),
            $newRights['rights']
        );
        $this->assertEquals(
            array(
                'acl.group.delete' => array(),
                'acl.group.modify' => array(),
                'acl.user.view'    => array(),
                'acl.user.modify'  => array(),
                'acl.group.view'   => array(),
                'super.cms.delete' => array(),
                'super.cms.list'   => array(),
                'super.cms.update' => array(),
            ),
            $newRights['rightsWithResources']
        );
    }

    /**
     * it should fail
     */
    public function testAdminTryingToDeleteOneOfAdminGroupButOtherAreEmptyGroups()
    {
        // create a new admin group with a user in it
        $this->insertRecordsIntoTable(
            'jacl2_group',
            array('id_aclgrp', 'name', 'grouptype', 'ownerlogin'),
            array(
                array('id_aclgrp'=>'admins2', 'name'=>'Admins2', 'grouptype'=>0, 'ownerlogin'=>null),
            ),
            false
        );
        $this->insertRecordsIntoTable(
            'jacl2_rights',
            array('id_aclsbj', 'id_aclgrp', 'id_aclres', 'canceled'),
            array(
                array('id_aclsbj'=>'acl.user.modify', 'id_aclgrp'=>'admins2', 'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclsbj'=> 'acl.group.modify', 'id_aclgrp'=>'admins2', 'id_aclres'=>'-', 'canceled'=>0),
            ),
            false
        );

        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->removeGroup('admins');
    }

    public function testAdminTryingToRemoveAUserFromAdminGroup()
    {
        // add one user into admins, so there is two admin user
        $usergroups = array(
            array('login'=>'oneuser', 'id_aclgrp'=>'admins'),
        );
        $this->insertRecordsIntoTable(
            'jacl2_user_group',
            array('login', 'id_aclgrp'),
            $usergroups,
            false
        );

        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $mgr->removeUserFromGroup('theadmin', 'admins');
        $rightsResult = $mgr->getUserRights('theadmin');

        $hisGroup = '<object>
                            <string property="login" value="theadmin" />
                            <string property="id_aclgrp" value="__priv_theadmin" />
                            <string property="name" value="theadmin" />
                            <string property="grouptype" value="2" />
                        </object>';
        $this->assertComplexIdenticalStr($rightsResult['hisgroup'], $hisGroup);

        $this->assertEquals(array(), $rightsResult['groupsuser']);

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
    }

    /**
     * it should fail
     */
    public function testAdminTryingToRemoveAUserFromAdminGroupButItIsTheOnlyOneAdmin()
    {
        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->removeUserFromGroup('theadmin', 'admins');
    }

    /**
     * it should fail.
     */
    public function testAdminTryingToAddHimselfToNonAdminGroup()
    {
        $rights = array(
            'acl.users.modify'  => 'n',
            'acl.groups.modify' => 'n',
        );
        jAuth::login('theadmin', 'pwd', false);
        jAcl2DbManager::setRightsOnGroup('users', $rights);
        $mgr = new jAcl2DbAdminUIManager();
        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->addUserToGroup('theadmin', 'users');
    }

    /**
     * it should fail.
     */
    public function testNonAdminTryingToRemoveAdminRightsByAddingHimselfIntoGroup()
    {
        $usersRights = array(
            'acl.users.modify'  => 'y',
            'acl.groups.modify' => 'y',
        );
        jAcl2DbManager::setRightsOnGroup('users', $usersRights);
        jAuth::login('oneuser', 'pwd', false);

        $NotAdminRights = array(
            'acl.users.modify'  => 'n',
            'acl.groups.modify' => 'n',
        );
        jAcl2DbUserGroup::createGroup('NotAdmin', 'notAdmin');
        jAcl2DbManager::setRightsOnGroup('notAdmin', $NotAdminRights);

        $mgr = new jAcl2DbAdminUIManager();
        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->addUserToGroup('oneuser', 'notAdmin');
    }

    public function testGetUsersList()
    {
        jAuth::login('theadmin', 'foo', false);
        $mgr = new jAcl2DbAdminUIManager();
        $list = $mgr->getUsersList(jAcl2DbAdminUIManager::FILTER_GROUP_ALL_USERS);

        $this->assertEquals(3, $list['usersCount']);
        $verif = '<array>
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
        </array>';
        $this->assertComplexIdenticalStr($list['users'], $verif);
    }

    public function testGetUserRights()
    {
        jAuth::login('theadmin', 'foo', false);
        $mgr = new jAcl2DbAdminUIManager();
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

        $rights = array(
            'acl.group.delete' => array(
                '__priv_theadmin' => 'y',
                'admins'          => '',
                'users'           => '',
            ),
            'acl.group.modify' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.user.view' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.user.modify' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.group.view' => array(
                '__priv_theadmin' => 'y',
                'admins'          => '',
                'users'           => '',
            ),
            'super.cms.delete' => array(
                '__priv_theadmin' => false,
                'admins'          => '',
                'users'           => '',
            ),
            'super.cms.list' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => 'y',
            ),
            'super.cms.update' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => 'y',
            ),
        );
        $this->assertEquals($rights, $rightsResult['rights']);

        $this->assertEquals('theadmin', $rightsResult['user']);
        $this->assertEquals(
            array(
                'acl.group.delete' => array(
                    'grp'   => null,
                    'label' => 'acl.group.delete',
                ),
                'acl.group.modify' => array(
                    'grp'   => null,
                    'label' => 'acl.group.modify',
                ),
                'acl.user.view' => array(
                    'grp'   => null,
                    'label' => 'acl.user.view',
                ),
                'acl.user.modify' => array(
                    'grp'   => null,
                    'label' => 'acl.user.modify',
                ),
                'acl.group.view' => array(
                    'grp'   => null,
                    'label' => 'acl.group.view',
                ),
                'super.cms.delete' => array(
                    'grp'   => null,
                    'label' => 'super.cms.delete',
                ),
                'super.cms.list' => array(
                    'grp'   => null,
                    'label' => 'super.cms.list',
                ),
                'super.cms.update' => array(
                    'grp'   => null,
                    'label' => 'super.cms.update',
                ),
            ),
            $rightsResult['subjects']
        );

        $this->assertEquals(array(), $rightsResult['sbjgroups_localized']);
        $this->assertEquals(
            array(
                'acl.group.delete' => 0,
                'acl.group.modify' => 0,
                'acl.user.view'    => 0,
                'acl.user.modify'  => 0,
                'acl.group.view'   => 0,
                'super.cms.delete' => 0,
                'super.cms.list'   => 0,
                'super.cms.update' => 0,
            ),
            $rightsResult['rightsWithResources']
        );

        $this->assertFalse($rightsResult['hasRightsOnResources']);
    }

    public function testGetUserRightsWithResources()
    {
        jAuth::login('theadmin', 'foo', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rightsResult = $mgr->getUserRessourceRights('oneuser');

        $this->assertEquals('oneuser', $rightsResult['user']);
        $this->assertEquals(array(
            'super.cms.delete' => 'super.cms.delete',
        ), $rightsResult['subjects_localized']);
        $verif = '<array>
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

    public function testSaveNormalUserRights()
    {
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
            'super.cms.list'   => 'y',
            'super.cms.update' => false, // change
            'super.cms.delete' => 'y', // change
        );
        $mgr->saveUserRights('oneuser', $rights);

        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
            'acl.group.modify' => 'y', //change
            'acl.group.delete' => 'y',
            'acl.group.view'   => 'y',
            'super.cms.list'   => 'y',
            'super.cms.update' => 'n', // change
        );
        $mgr->saveUserRights('theadmin', $rights);

        $newRights = $mgr->getUserRights('theadmin');
        $rights = array(
            'acl.group.delete' => array(
                '__priv_theadmin' => 'y',
                'admins'          => '',
                'users'           => '',
            ),
            'acl.group.modify' => array(
                '__priv_theadmin' => 'y',
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.user.view' => array(
                '__priv_theadmin' => '',
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.user.modify' => array(
                '__priv_theadmin' => '',
                'admins'          => 'y',
                'users'           => '',
            ),
            'acl.group.view' => array(
                '__priv_theadmin' => 'y',
                'admins'          => '',
                'users'           => '',
            ),
            'super.cms.delete' => array(
                '__priv_theadmin' => false,
                'admins'          => '',
                'users'           => '',
            ),
            'super.cms.list' => array(
                '__priv_theadmin' => 'y',
                'admins'          => 'y',
                'users'           => 'y',
            ),
            'super.cms.update' => array(
                '__priv_theadmin' => 'n',
                'admins'          => 'y',
                'users'           => 'y',
            ),
        );
        $this->assertEquals($rights, $newRights['rights']);
        $this->assertEquals(
            array(
                'acl.group.delete' => 0,
                'acl.group.modify' => 0,
                'acl.user.view'    => 0,
                'acl.user.modify'  => 0,
                'acl.group.view'   => 0,
                'super.cms.delete' => 0,
                'super.cms.list'   => 0,
                'super.cms.update' => 0,
            ),
            $newRights['rightsWithResources']
        );

        $newRights = $mgr->getUserRights('oneuser');
        $rights = array(
            'acl.group.delete' => array(
                '__priv_oneuser' => false,
                'admins'         => '',
                'users'          => '',
            ),
            'acl.group.modify' => array(
                '__priv_oneuser' => false,
                'admins'         => 'y',
                'users'          => '',
            ),
            'acl.user.view' => array(
                '__priv_oneuser' => false,
                'admins'         => 'y',
                'users'          => '',
            ),
            'acl.user.modify' => array(
                '__priv_oneuser' => false,
                'admins'         => 'y',
                'users'          => '',
            ),
            'acl.group.view' => array(
                '__priv_oneuser' => false,
                'admins'         => '',
                'users'          => '',
            ),
            'super.cms.delete' => array(
                '__priv_oneuser' => 'y',
                'admins'         => '',
                'users'          => '',
            ),
            'super.cms.list' => array(
                '__priv_oneuser' => 'y',
                'admins'         => 'y',
                'users'          => 'y',
            ),
            'super.cms.update' => array(
                '__priv_oneuser' => false,
                'admins'         => 'y',
                'users'          => 'y',
            ),
        );
        $this->assertEquals($rights, $newRights['rights']);
        $this->assertEquals(
            array(
                'acl.group.delete' => 0,
                'acl.group.modify' => 0,
                'acl.user.view'    => 0,
                'acl.user.modify'  => 0,
                'acl.group.view'   => 0,
                'super.cms.delete' => 2,
                'super.cms.list'   => 0,
                'super.cms.update' => 0,
            ),
            $newRights['rightsWithResources']
        );
    }

    public function testRemoveUserRightsWithResources()
    {
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'super.cms.delete' => 'on', // change
        );
        $mgr->removeUserRessourceRights('oneuser', $rights);
        $newRights = $mgr->getUserRights('oneuser');
        $rights = array(
            'acl.group.delete' => array(
                '__priv_oneuser' => false,
                'admins'         => '',
                'users'          => '',
            ),
            'acl.group.modify' => array(
                '__priv_oneuser' => false,
                'admins'         => 'y',
                'users'          => '',
            ),
            'acl.user.view' => array(
                '__priv_oneuser' => false,
                'admins'         => 'y',
                'users'          => '',
            ),
            'acl.user.modify' => array(
                '__priv_oneuser' => false,
                'admins'         => 'y',
                'users'          => '',
            ),
            'acl.group.view' => array(
                '__priv_oneuser' => false,
                'admins'         => '',
                'users'          => '',
            ),
            'super.cms.delete' => array(
                '__priv_oneuser' => false,
                'admins'         => '',
                'users'          => '',
            ),
            'super.cms.list' => array(
                '__priv_oneuser' => false,
                'admins'         => 'y',
                'users'          => 'y',
            ),
            'super.cms.update' => array(
                '__priv_oneuser' => false,
                'admins'         => 'y',
                'users'          => 'y',
            ),
        );
        $this->assertEquals($rights, $newRights['rights']);
        $this->assertEquals(
            array(
                'acl.group.delete' => 0,
                'acl.group.modify' => 0,
                'acl.user.view'    => 0,
                'acl.user.modify'  => 0,
                'acl.group.view'   => 0,
                'super.cms.delete' => 0,
                'super.cms.list'   => 0,
                'super.cms.update' => 0,
            ),
            $newRights['rightsWithResources']
        );
    }
}
