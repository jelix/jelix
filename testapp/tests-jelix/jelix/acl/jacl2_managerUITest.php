<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Adrien Lagroy de Croutte
 *
 * @copyright   2017-2021 Laurent Jouanneau, 2020 Adrien Lagroy de Croutte
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class jacl2_managerUITest extends \Jelix\UnitTests\UnitTestCaseDb
{
    protected static $driver = 'db';
    protected static $coordAuthPlugin = null;
    protected $oldAuthPlugin;

    protected $numberAs = "string";

    public function setUp() : void
    {
        jDao::releaseAll();
        jProfiles::clear();
        $this->preSetUpAcl();
        $this->setUpAcl();
    }

    protected function preSetUpAcl() : void
    {
        $this->dbProfile = 'jacl2_profile';
        self::initClassicRequest(TESTAPP_URL . 'index.php');
    }

    protected function setUpAcl() : void
    {
        jApp::config()->acl2['driver'] = self::$driver;
        jAcl2::unloadDriver();
        jAcl2::clearCache();

        require_once JELIX_LIB_PATH.'plugins/coord/auth/auth.coord.php';
        $confContent = parse_ini_file(jApp::appSystemPath('auth_class.coord.ini.php'), true, INI_SCANNER_TYPED);
        $config = jAuth::loadConfig($confContent);
        self::$coordAuthPlugin = new AuthCoordPlugin($config);

        // prepare data
        $this->emptyTable('jacl2_rights');
        $this->emptyTable('jacl2_subject');
        $this->emptyTable('jacl2_user_group');
        $this->emptyTable('jacl2_group');

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

        $rights = array(
            // dummy rights
            array('id_aclsbj'=>'super.cms.list',   'label_key'=>'cms~rights.super.cms', 'id_aclsbjgrp'=>null),
            array('id_aclsbj'=> 'super.cms.update', 'label_key'=>'cms~rights.super.cms', 'id_aclsbjgrp'=>null),
            array('id_aclsbj'=> 'super.cms.delete', 'label_key'=>'cms~rights.super.cms', 'id_aclsbjgrp'=>null),
            // reserved admin rights
            array('id_aclsbj'=>'acl.user.view',  'label_key'=>'', 'id_aclsbjgrp'=>null),
            array('id_aclsbj'=> 'acl.user.modify',  'label_key'=>'', 'id_aclsbjgrp'=>null),
            array('id_aclsbj'=> 'acl.group.view', 'label_key'=>'', 'id_aclsbjgrp'=>null),
            array('id_aclsbj'=> 'acl.group.modify', 'label_key'=>'', 'id_aclsbjgrp'=>null),
            array('id_aclsbj'=> 'acl.group.delete', 'label_key'=>'', 'id_aclsbjgrp'=>null),
        );
        $this->insertRecordsIntoTable(
            'jacl2_subject',
            array('id_aclsbj', 'label_key', 'id_aclsbjgrp'),
            $rights,
            true
        );

        $rightsSettings = array(
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
            $rightsSettings,
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
        jDao::releaseAll();
        jProfiles::clear();
        jAcl2DbUserGroup::clearCache();
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
        <'.$this->numberAs.' property="grouptype" value="0" />
        <null property="ownerlogin"/>
    </object>
    <object>
        <string property="id_aclgrp" value="users" />
        <string property="name" value="Users" />
        <'.$this->numberAs.' property="grouptype" value="0" />
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
            $rights['rightsGroupsLabels']
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
            $rights['rightsProperties']
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
            $rights['rightsLabels']
        );
        $this->assertFalse($rights['hasRightsOnResources']);

        $rights = $mgr->getGroupRightsWithResources('users');
        $this->assertEquals(
            array(),
            $rights['rightsWithResources']
        );
        $this->assertEquals(
            array(),
            $rights['rightsLabels']
        );
        $this->assertFalse($rights['hasRightsOnResources']);

        $rights = $mgr->getGroupRightsWithResources('__priv_oneuser');
        $verif = '<array>
            <array key="super.cms.delete">
                <object >
                    <string property="id_aclsbj" value="super.cms.delete" />
                    <string property="id_aclgrp" value="__priv_oneuser" />
                    <string property="id_aclres" value="123" />
                    <'.$this->numberAs.' property="canceled" value="0"/>
                </object>
                <object >
                    <string property="id_aclsbj" value="super.cms.delete" />
                    <string property="id_aclgrp" value="__priv_oneuser" />
                    <string property="id_aclres" value="456" />
                    <'.$this->numberAs.' property="canceled" value="1"/>
                </object>
            </array>
        </array>';
        $this->assertComplexIdenticalStr($rights['rightsWithResources'], $verif);
        $this->assertEquals(
            array(
                'super.cms.delete' => 'super.cms.delete',
            ),
            $rights['rightsLabels']
        );
        $this->assertTrue($rights['hasRightsOnResources']);

        $rights = $mgr->getGroupRightsWithResources('__priv_theadmin');
        $this->assertEquals(
            array(),
            $rights['rightsWithResources']
        );
        $this->assertEquals(
            array(),
            $rights['rightsLabels']
        );
        $this->assertFalse($rights['hasRightsOnResources']);

        $rights = $mgr->getGroupRightsWithResources('__priv_specificadmin');
        $this->assertEquals(
            array(),
            $rights['rightsWithResources']
        );
        $this->assertEquals(
            array(),
            $rights['rightsLabels']
        );
        $this->assertFalse($rights['hasRightsOnResources']);
    }

    public function testSaveNormalGroupRights()
    {
        jAuth::login('theadmin', 'pwd', false);
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
        $mgr->saveGroupRights($rights, 'theadmin');
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
                    'admins'      => 'y', // because acl.group.modify is set
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

    public function testSaveNormalOneGroupRights()
    {
        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $mgr->setRightsOnGroup(array(
            'super.cms.list'   => 'y',
            'super.cms.update' => false, // change
            'super.cms.delete' => 'y', // change
        ), 'users', 'theadmin');

        $mgr->setRightsOnGroup(array(
            'acl.user.view'    => 'y',
            'acl.user.modify'  => 'y',
            'acl.group.modify' => 'y',
            'super.cms.list'   => 'y',
            'super.cms.update' => 'n', // change
        ), 'admins', 'theadmin');

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
                    'admins'      => 'y', // because acl.group.modify is set
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
        $mgr->saveGroupRights($rights, 'theadmin');
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
                    'admins'      => 'y', // because acl.group.modify is set
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

    public function testSaveEmptyOneGroupRights()
    {
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();

        $mgr->setRightsOnGroup(array(), 'users', 'theadmin');
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

}
