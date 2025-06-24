<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Adrien Lagroy de Croutte
 *
 * @copyright   2017-2025 Laurent Jouanneau, 2020 Adrien Lagroy de Croutte
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
use Jelix\Core\Profiles;

class jacl2_managerUITest extends \Jelix\UnitTests\UnitTestCaseDb
{
    protected static $driver = 'db';
    protected static $coordAuthPlugin = null;
    protected $oldAuthPlugin;

    protected $numberAs = "string";

    public function setUp() : void
    {
        jDao::releaseAll();
        Profiles::clear();
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

    protected function teardownAcl()
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

    public function tearDown() : void
    {
        $this->teardownAcl();
        jDao::releaseAll();
        Profiles::clear();
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

        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
            'super.cms.delete' => 'on', // change
        );
        $mgr->removeGroupRightsWithResources('__priv_oneuser', $rights);

        $rights = $mgr->getGroupRightsWithResources('__priv_oneuser');
        $this->assertEquals(array(), $rights['rightsWithResources']);
        $this->assertEquals(array(), $rights['rightsLabels']);
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
        $mgr->saveGroupRights($rights, 'theadmin');
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
        $mgr->saveGroupRights($rights, 'theadmin');
    }

    /**
     * it should fail
     */
    public function testNonAdminTryingToRemoveRightAdminOfAnAloneAdminGroup2()
    {
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'acl.user.view'    => 'y',
            'acl.user.modify'  => 'y',
            'acl.group.modify' => '', // change
            'super.cms.list'   => 'y',
            'super.cms.update' => 'y',
        );

        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->setRightsOnGroup($rights, 'admins', 'theadmin');
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
        $mgr->saveUserRights('theadmin', $rights, 'theadmin');
    }

    /**
     * The non admin user has rights to modify user rights, not group rights
     */
    public function testNonAdminTryingToRemoveRightAdminOfOneOfAdminGroup()
    {
        // add a right admin on users (we want to be sure it is set before
        // saveGroupRights, removing a right from a group and setting it to
        // another group is an other test)
        $this->insertRecordsIntoTable(
            'jacl2_rights',
            array('id_aclsbj', 'id_aclgrp', 'id_aclres', 'canceled'),
            array(
                array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.user.modify',  'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.user.view',  'id_aclres'=>'-', 'canceled'=>0)
            ),
            false
        );

        // now let's remove the same right from admins
        jAuth::login('oneuser', 'pwd', false);
        $mgr    = new jAcl2DbAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'admins' => array(
                'acl.group.view'   => 'y',
                'acl.group.modify' => 'y',
                'acl.user.view'    => 'y',
                'acl.user.modify'  => '', // change
                'super.cms.list'   => 'y',
                'super.cms.update' => 'y',
            ),
            'users'  => array(
                'super.cms.list'   => 'y',
                'super.cms.update' => 'y',
                'acl.user.view'    => 'y',
                'acl.user.modify'  => 'y',
            ),
        );
        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->saveGroupRights($rights, 'oneuser');
    }

    /**
     * The non admin user has rights to modify user rights, not group rights
     */
    public function testNonAdminTryingToRemoveRightAdminOfOneOfAdminGroup2()
    {
        // add a right admin on users (we want to be sure it is set before
        // saveGroupRights, removing a right from a group and setting it to
        // another group is an other test)
        $this->insertRecordsIntoTable(
            'jacl2_rights',
            array('id_aclsbj', 'id_aclgrp', 'id_aclres', 'canceled'),
            array(
                array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.user.modify',  'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.user.view',  'id_aclres'=>'-', 'canceled'=>0)
            ),
            false
        );

        // now let's remove the same right from admins
        jAuth::login('oneuser', 'pwd', false);
        $mgr    = new jAcl2DbAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'acl.group.view'   => 'y',
            'acl.group.modify' => 'y',
            'acl.user.view'    => 'y',
            'acl.user.modify'  => '', // change
            'super.cms.list'   => 'y',
            'super.cms.update' => 'y',
        );
        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->setRightsOnGroup($rights, 'admins', 'oneuser');
    }



    public function testNonAdminRemoveRightAdminOfOneOfAdminGroup()
    {
        // add a right admin on users (we want to be sure it is set before
        // saveGroupRights, removing a right from a group and setting it to
        // another group is an other test)
        $this->insertRecordsIntoTable(
            'jacl2_rights',
            array('id_aclsbj', 'id_aclgrp', 'id_aclres', 'canceled'),
            array(
                array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.group.modify',  'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.group.view',  'id_aclres'=>'-', 'canceled'=>0)
            ),
            false
        );

        // now let's remove the same right from admins
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'admins' => array(
                'acl.group.view' => 'y',
                'acl.group.modify' => '',// change
                'acl.user.view'    => 'y',
                'acl.user.modify'  => 'y',
                'super.cms.list'   => 'y',
                'super.cms.update' => 'y',
            ),
            'users' => array(
                'super.cms.list'   => 'y',
                'super.cms.update' => 'y',
                'acl.group.view'  => 'y',
                'acl.group.modify'  => 'y',
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
                    'admins'      => '',
                    'users'       => 'y',
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
                    'admins'      => 'y',
                    'users'       => 'y',
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


    public function testNonAdminRemoveRightAdminOfOneOfAdminGroup2()
    {
        // add a right admin on users (we want to be sure it is set before
        // saveGroupRights, removing a right from a group and setting it to
        // another group is an other test)
        $this->insertRecordsIntoTable(
            'jacl2_rights',
            array('id_aclsbj', 'id_aclgrp', 'id_aclres', 'canceled'),
            array(
                array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.group.modify',  'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.group.view',  'id_aclres'=>'-', 'canceled'=>0)
            ),
            false
        );

        // now let's remove the same right from admins
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // id_aclgrp=> array(idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove))
            'acl.group.view' => 'y',
            'acl.group.modify' => '',// change
            'acl.user.view'    => 'y',
            'acl.user.modify'  => 'y',
            'super.cms.list'   => 'y',
            'super.cms.update' => 'y',
        );
        $mgr->setRightsOnGroup($rights, 'admins', 'oneuser');

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
                    'admins'      => '',
                    'users'       => 'y',
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
                    'admins'      => 'y',
                    'users'       => 'y',
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
            array(
                array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.group.view',  'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.group.delete',  'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.user.view',  'id_aclres'=>'-', 'canceled'=>0),
                array('id_aclgrp'=>'users', 'id_aclsbj'=>'acl.user.modify',  'id_aclres'=>'-', 'canceled'=>0)
            ),
            false
        );

        // now remove the same right from a user
        jAuth::login('oneuser', 'foo', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
            'acl.group.view'   => 'y',
            'acl.group.delete' => '', // change
        );
        $mgr->saveUserRights('theadmin', $rights, 'oneuser');

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
                'users'           => 'y',
            ),
            'acl.user.modify' => array(
                '__priv_theadmin' => false,
                'admins'          => 'y',
                'users'           => 'y',
            ),
            'acl.group.view' => array(
                '__priv_theadmin' => 'y',
                'admins'          => '',
                'users'           => 'y',
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
    public function testAdminTryingToRemoveAllRightAdmins()
    {
        // it should fail
        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $rights = array(
            // rights for 'admins' are missing, so all of its rights will be deleted
            'users' => array(
                'super.cms.list'   => 'y',
                'super.cms.update' => 'y',
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
        $mgr->saveUserRights('theadmin', $rights, 'theadmin');
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
        $mgr->saveUserRights('theadmin', $rights, 'theadmin');

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
        $mgr->saveUserRights('specificadmin', $rights, 'theadmin');

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
                    'users'       => 'y', // because acl.user.modify is set
                ),
                'acl.user.modify' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => 'y',
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

    public function testNonAdminTryingToRemoveRightAdminAndToAddRightAdmin2()
    {
        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();

        $mgr->setRightsOnGroup(array(
            'super.cms.list'   => 'y',
            'super.cms.update' => 'y',
            'acl.user.modify'  => 'y',
        ),'users' , 'theadmin');
        $mgr->setRightsOnGroup(array(
            'acl.user.view'    => 'y',
            'acl.group.modify' => 'y',
            'acl.user.modify'  => '', // change
            'super.cms.list'   => 'y',
            'super.cms.update' => 'y',
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
                    'users'       => 'y', // because acl.user.modify is set
                ),
                'acl.user.modify' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => 'y',
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
        $mgr->removeGroup('admins', 'theadmin');
    }

    /**
     * it should fail.
     *
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
        $mgr->removeGroup('admins2', 'theadmin');

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
        $mgr->removeGroup('admins', 'theadmin');
    }

    public function testAdminRemoveAUserFromAdminGroup()
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
        $mgr->removeUserFromGroup('oneuser', 'admins', 'theadmin');
        $rightsResult = $mgr->getUserRights('oneuser');

        $hisGroup = '<object>
                            <string property="login" value="oneuser" />
                            <string property="id_aclgrp" value="__priv_oneuser" />
                            <string property="name" value="oneuser" />
                            <'.$this->numberAs.' property="grouptype" value="2" />
                        </object>';
        $this->assertComplexIdenticalStr($rightsResult['hisgroup'], $hisGroup);

        $this->assertEquals(array('users'), array_keys($rightsResult['groupsuser']));

        $groups = '<array>
            <object>
                <string property="id_aclgrp" value="admins" />
                <string property="name" value="Admins" />
                <'.$this->numberAs.' property="grouptype" value="0" />
            </object>
            <object>
                <string property="id_aclgrp" value="users" />
                <string property="name" value="Users" />
                <'.$this->numberAs.' property="grouptype" value="0" />
            </object>
        </array>';
        $this->assertComplexIdenticalStr($rightsResult['groups'], $groups);
    }

    /**
     * It should fail because the admins group has no all rights,
     * (so oneuser has no all rights)
     * and theadmin has all missing rights in his private group
     *
     */
    public function testAdminTryingToRemoveTheAdminFromAdminGroup()
    {
        // add one user into admins, so there is two admin user
        $usergroups = array(
            array('login' => 'oneuser', 'id_aclgrp' => 'admins'),
        );
        $this->insertRecordsIntoTable(
            'jacl2_user_group',
            array('login', 'id_aclgrp'),
            $usergroups,
            false
        );

        jAuth::login('oneuser', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $this->expectException(\jAcl2DbAdminUIException::class);
        $mgr->removeUserFromGroup('theadmin', 'admins', 'oneuser');
    }

    /**
     * it should fail.
     *
     */
    public function testAdminTryingToRemoveAUserFromAdminGroupButHeIsTheOnlyOneAdmin()
    {
        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();
        $this->expectException(\jAcl2DbAdminUIException::class);
        $mgr->removeUserFromGroup('theadmin', 'admins', 'theadmin');
    }

    /**
     * it should fail.
     *
     */
    public function testAdminTryingToAddHimselfToNonAdminGroup()
    {
        $rights = array(
            'acl.user.modify'  => 'n',
            'acl.group.modify' => 'n',
        );

        jAuth::login('theadmin', 'pwd', false);
        jAcl2DbManager::setRightsOnGroup('users', $rights);
        $mgr = new jAcl2DbAdminUIManager();
        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->addUserToGroup('theadmin', 'users', 'theadmin');
    }

    /**
     * it should fail.
     *
     */
    public function testNonAdminTryingToRemoveAdminRightsByAddingHimselfIntoGroup()
    {
        $usersRights = array(
            'acl.user.modify'  => 'y',
            'acl.group.modify' => 'y',
        );
        jAcl2DbManager::setRightsOnGroup('users', $usersRights);
        jAuth::login('oneuser', 'pwd', false);

        $NotAdminRights = array(
            'acl.user.modify'  => 'n',
            'acl.group.modify' => 'n',
        );
        jAcl2DbUserGroup::createGroup('NotAdmin', 'notAdmin');
        jAcl2DbManager::setRightsOnGroup('notAdmin', $NotAdminRights);

        $mgr = new jAcl2DbAdminUIManager();
        $this->expectException(jAcl2DbAdminUIException::class);
        $mgr->addUserToGroup('oneuser', 'notAdmin', 'oneuser');
    }

    public function testGetUsersList()
    {
        jAuth::login('theadmin', 'foo', false);
        $mgr = new jAcl2DbAdminUIManager();
        $list = $mgr->getUsersList(jAcl2DbAdminUIManager::FILTER_GROUP_ALL_USERS);

        $this->assertEquals(3, $list['resultsCount']);
        $verif = '<array>
                <object >
                    <string property="login" value="oneuser" />
                    <string property="id_aclgrp" value="__priv_oneuser" />
                    <array property="groups">
                        <string value="Users" />
                    </array>
                </object>
                <object >
                    <string property="login" value="specificadmin" />
                    <string property="id_aclgrp" value="__priv_specificadmin" />
                    <array property="groups">
                       <string value="Users" />
                    </array>
                </object>
                <object >
                    <string property="login" value="theadmin" />
                    <string property="id_aclgrp" value="__priv_theadmin" />
                    <array property="groups">
                        <string value="Admins" />
                    </array>
                </object>
        </array>';
        $this->assertComplexIdenticalStr($list['results'], $verif);
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
                            <'.$this->numberAs.' property="grouptype" value="2" />
                        </object>';
        $this->assertComplexIdenticalStr($rightsResult['hisgroup'], $hisGroup);

        $usergroups = '<array>
                        <object key="admins">
                            <string property="login" value="theadmin" />
                            <string property="id_aclgrp" value="admins" />
                            <string property="name" value="Admins" />
                            <'.$this->numberAs.' property="grouptype" value="0" />
                        </object>
                    </array>
        ';
        $this->assertComplexIdenticalStr($rightsResult['groupsuser'], $usergroups);

        $groups = '<array>
            <object>
                <string property="id_aclgrp" value="admins" />
                <string property="name" value="Admins" />
                <'.$this->numberAs.' property="grouptype" value="0" />
            </object>
            <object>
                <string property="id_aclgrp" value="users" />
                <string property="name" value="Users" />
                <'.$this->numberAs.' property="grouptype" value="0" />
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
            $rightsResult['rightsProperties']
        );

        $this->assertEquals(array(), $rightsResult['rightsGroupsLabels']);
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
        ), $rightsResult['rightsLabels']);
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
        $this->assertComplexIdenticalStr($rightsResult['rightsWithResources'], $verif);
        $this->assertTrue($rightsResult['hasRightsOnResources']);

        $rightsResult = $mgr->getUserRessourceRights('theadmin');
        $this->assertEquals('theadmin', $rightsResult['user']);
        $this->assertEquals(array(), $rightsResult['rightsLabels']);
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
        $mgr->saveUserRights('oneuser', $rights, 'theadmin');

        $rights = array( // idl_aclsbj => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
            'acl.group.modify' => 'y', //change
            'acl.group.delete' => 'y',
            'acl.group.view'   => 'y',
            'super.cms.list'   => 'y',
            'super.cms.update' => 'n', // change
        );
        $mgr->saveUserRights('theadmin', $rights, 'theadmin');

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

    public function testGetGroupsListByFilterByAllId()
    {
        jAuth::login('theadmin', 'foo', false);
        $mgr = new jAcl2DbAdminUIManager();
        $results = $mgr->getGroupByFilter('', 0, 20, $mgr::ORDER_BY_ID);
        $this->assertEquals(2, $results['resultsCount']);
        $verif = '<array>
                <object >
                    <string property="id_aclgrp" value="admins" />
                    <string property="name" value="Admins" />
                    <'.$this->numberAs.' property="nb_users" value="1" />
                    <'.$this->numberAs.' property="grouptype" value="0"/>
                </object>
                <object >
                    <string property="id_aclgrp" value="users" />
                    <string property="name" value="Users" />
                    <'.$this->numberAs.' property="nb_users" value="2" />
                    <'.$this->numberAs.' property="grouptype" value="0"/>
                </object>
        </array>';

        $this->assertComplexIdenticalStr($results['results'], $verif);

        $results = $mgr->getGroupByFilter('', 0, 20, $mgr::ORDER_BY_ID, false);
        $this->assertEquals(2, $results['resultsCount']);
        $verif = '<array>
                <object >
                    <string property="id_aclgrp" value="admins" />
                    <string property="name" value="Admins" />
                    <'.$this->numberAs.' property="grouptype" value="0"/>
                    <null property="ownerlogin" />
                </object>
                <object >
                    <string property="id_aclgrp" value="users" />
                    <string property="name" value="Users" />
                    <'.$this->numberAs.' property="grouptype" value="0"/>
                    <null property="ownerlogin" />
                </object>
        </array>';

        $this->assertComplexIdenticalStr($results['results'], $verif);

    }
    public function testGetGroupsListByFilterById()
    {
        jAuth::login('theadmin', 'foo', false);
        $mgr = new jAcl2DbAdminUIManager();
        $results = $mgr->getGroupByFilter('adm', 0, 20, $mgr::ORDER_BY_ID);
        $this->assertEquals(1, $results['resultsCount']);
        $verif = '<array>
                <object >
                    <string property="id_aclgrp" value="admins" />
                    <string property="name" value="Admins" />
                    <'.$this->numberAs.' property="nb_users" value="1" />
                    <'.$this->numberAs.' property="grouptype" value="0"/>
                </object>
        </array>';

        $this->assertComplexIdenticalStr($results['results'], $verif);

        $results = $mgr->getGroupByFilter('adm', 0, 20, $mgr::ORDER_BY_ID, false);
        $this->assertEquals(1, $results['resultsCount']);
        $verif = '<array>
                <object >
                    <string property="id_aclgrp" value="admins" />
                    <string property="name" value="Admins" />
                    <'.$this->numberAs.' property="grouptype" value="0"/>
                    <null property="ownerlogin" />
                </object>
        </array>';

        $this->assertComplexIdenticalStr($results['results'], $verif);

    }

    public function testGetGroupsListByFilterByNbUsers()
    {
        jAuth::login('theadmin', 'foo', false);
        $mgr = new jAcl2DbAdminUIManager();
        $results = $mgr->getGroupByFilter('', 0, 20, $mgr::ORDER_BY_USERS | $mgr::ORDER_DIRECTION_DESC);
        $this->assertEquals(2, $results['resultsCount']);
        $verif = '<array>
                <object >
                    <string property="id_aclgrp" value="users" />
                    <string property="name" value="Users" />
                    <'.$this->numberAs.' property="nb_users" value="2" />
                    <'.$this->numberAs.' property="grouptype" value="0"/>
                </object>
                <object >
                    <string property="id_aclgrp" value="admins" />
                    <string property="name" value="Admins" />
                    <'.$this->numberAs.' property="nb_users" value="1" />
                    <'.$this->numberAs.' property="grouptype" value="0"/>
                </object>
        </array>';

        $this->assertComplexIdenticalStr($results['results'], $verif);

        $results = $mgr->getGroupByFilter('', 0, 20, $mgr::ORDER_BY_USERS | $mgr::ORDER_DIRECTION_DESC, false);
        // ORDER_BY_USERS is not compatible with the $withUser filer, so we should have
        // an order by name.
        $this->assertEquals(2, $results['resultsCount']);
        $verif = '<array>
                <object >
                    <string property="id_aclgrp" value="users" />
                    <string property="name" value="Users" />
                    <'.$this->numberAs.' property="grouptype" value="0"/>
                    <null property="ownerlogin" />
                </object>
                <object >
                    <string property="id_aclgrp" value="admins" />
                    <string property="name" value="Admins" />
                    <'.$this->numberAs.' property="grouptype" value="0"/>
                    <null property="ownerlogin" />
                </object>
        </array>';

        $this->assertComplexIdenticalStr($results['results'], $verif);

    }




    public function testSetForbiddenRight()
    {
        jAuth::login('theadmin', 'pwd', false);
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


        $mgr->setRightsOnGroup(array(
            'acl.group.view'   => 'n',
        ),'users' , 'theadmin');

        $newRights = $mgr->getGroupRights();
        $this->assertEquals(
            array(
                'acl.group.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => 'n',
                ),
                'acl.group.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => 'n',
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
                    'users'       => 'n',
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


        // set rights again
        $mgr->setRightsOnGroup(array(
            'acl.group.view'   => 'n',
            'acl.group.delete'   => 'n',
            'acl.group.modify'   => 'n',
        ),'users' , 'theadmin');

        $newRights = $mgr->getGroupRights();
        $this->assertEquals(
            array(
                'acl.group.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => 'n',
                ),
                'acl.group.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => 'n',
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
                    'users'       => 'n',
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
    }


    public function testRemoveForbiddenRight()
    {
        jAuth::login('theadmin', 'pwd', false);
        $mgr = new jAcl2DbAdminUIManager();

        $mgr->setRightsOnGroup(array(
            'acl.group.view'   => 'n',
        ),'users' , 'theadmin');

        // set rights again
        $mgr->setRightsOnGroup(array(
            'acl.group.view'   => 'y',
            'acl.group.delete'   => 'n',
            'acl.group.modify'   => 'n',
        ),'users' , 'theadmin');

        $newRights = $mgr->getGroupRights();
        $this->assertEquals(
            array(
                'acl.group.delete' => array(
                    '__anonymous' => false,
                    'admins'      => '',
                    'users'       => 'n',
                ),
                'acl.group.modify' => array(
                    '__anonymous' => false,
                    'admins'      => 'y',
                    'users'       => 'n',
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
                    'users'       => 'y',
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
    }
}
