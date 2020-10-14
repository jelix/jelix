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


/**
 * Tests on user and group management in jAcl
 * CAREFULL ! DON'T CHANGE THE ORDER OF METHODS
 */
class jacl2_users_groupsTest extends jUnitTestCaseDb {
    
    protected static $groups;
    protected static $defaultGroupId;

    protected static $grpId1;
    protected static $grpId2;
    protected static $grpId3;
    protected static $grpId4;
    protected static $grpId5;
    protected static $grpId6;
    protected static $grpId7;

    protected static $firstSetup = true;
    
    public function setUp(){
        self::initClassicRequest(TESTAPP_URL.'index.php');
        $this->dbProfile = 'jacl2_profile';
        if (!self::$firstSetup) {
            return;
        }
        self::$firstSetup = false;

        $this->emptyTable('jacl2_user_group');
        $this->emptyTable('jacl2_group');
    }

    public function testCreateGroup(){

        // creation d'un groupe

        self::$grpId1 = jAcl2DbUserGroup::createGroup('group1');
        $this->assertTrue(self::$grpId1 != '', 'jAcl2DbUserGroup::createGroup failed : id is empty');
        self::$groups = array(array('id_aclgrp'=>self::$grpId1,
            'name'=>'group1',
            'grouptype'=>0,
            'ownerlogin'=>null));
        $this->assertTableContainsRecords('jacl2_group', self::$groups);

        // creation de deux autres groupes

        self::$grpId2 = jAcl2DbUserGroup::createGroup('group2');
        self::$grpId3 = jAcl2DbUserGroup::createGroup('group3');
        self::$groups[] = array('id_aclgrp'=>self::$grpId2,
            'name'=>'group2',
            'grouptype'=>0,
            'ownerlogin'=>null);
        self::$groups[] = array('id_aclgrp'=>self::$grpId3,
            'name'=>'group3',
            'grouptype'=>0,
            'ownerlogin'=>null);
        $this->assertTableContainsRecords('jacl2_group', self::$groups);

    }

    /**
     * @depends  testCreateGroup
     */
    public function testDefaultGroup(){
        // on met un des groupes par defaut
        jAcl2DbUserGroup::setDefaultGroup(self::$grpId2,false);
        $this->assertTableContainsRecords('jacl2_group', self::$groups);
        jAcl2DbUserGroup::setDefaultGroup(self::$grpId2,true);
        self::$defaultGroupId = self::$grpId2; // for next test method
        self::$groups[1]['grouptype']=1;
        $this->assertTableContainsRecords('jacl2_group', self::$groups);
    }

    /**
     * @depends testDefaultGroup
     */
    public function testRenameGroup(){
        // changement de nom d'un groupe
        jAcl2DbUserGroup::updateGroup(self::$grpId3, 'newgroup3');
        self::$groups[2]['name']='newgroup3';
        $this->assertTableContainsRecords('jacl2_group', self::$groups);
    }

    /**
     * @depends testRenameGroup
     */
    public function testGroupList(){
        // recuperation de la liste de tous les groupes
        $list = jAcl2DbUserGroup::getGroupList()->fetchAll();

        $verif='<array>
    <object>
        <string property="id_aclgrp" value="'.self::$grpId1.'" />
        <string property="name" value="group1" />
        <string property="grouptype" value="0" />
        <null property="ownerlogin"/>
    </object>
    <object>
        <string property="id_aclgrp" value="'.self::$grpId2.'" />
        <string property="name" value="group2" />
        <string property="grouptype" value="1" />
        <null property="ownerlogin"/>
    </object>
    <object>
        <string property="id_aclgrp" value="'.self::$grpId3.'" />
        <string property="name" value="newgroup3" />
        <string property="grouptype" value="0" />
        <null property="ownerlogin"/>
    </object>
</array>';

        $this->assertComplexIdenticalStr($list, $verif);
    }

    /**
     * @depends testGroupList
     */
    public function testRemoveGroup(){
        // creation d'un autre groupe
        self::$grpId4 = jAcl2DbUserGroup::createGroup('group4');
        $records2 = self::$groups;
        $records2[] = array('id_aclgrp'=>self::$grpId4,
            'name'=>'group4',
            'grouptype'=>0,
            'ownerlogin'=>null);
        $this->assertTableContainsRecords('jacl2_group', $records2);

        // destruction d'un groupe (ici qui n'a pas de user)
        jAcl2DbUserGroup::removeGroup(self::$grpId4);
        $this->assertTableContainsRecords('jacl2_group', self::$groups);

    }

    protected static $usergroups=array();

    /**
     * @depends  testRemoveGroup
     */
    public function testCreateUser(){
        $this->assertTableIsEmpty('jacl2_user_group');

        // creation d'un user dans les acl, sans le mettre dans les groupes par defaut
        jAcl2DbUserGroup::createUser('laurent',false);
        self::$grpId5 = '__priv_laurent';

        self::$groups[] = array('id_aclgrp'=>self::$grpId5,
            'name'=>'laurent',
            'grouptype'=>2,
            'ownerlogin'=>'laurent');
        $this->assertTableContainsRecords('jacl2_group', self::$groups);

        self::$usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>self::$grpId5),
        );
        $this->assertTableContainsRecords('jacl2_user_group', self::$usergroups);
    }

    /**
     * @depends testCreateUser
     */
    public function testCreateUser2(){
        // creation d'un deuxième user dans les acl, en le mettant 
        // dans les groupes par defaut
        jAcl2DbUserGroup::createUser('max');
        self::$grpId6 = '__priv_max';

        self::$groups[] = array('id_aclgrp'=>self::$grpId6,
            'name'=>'max',
            'grouptype'=>2,
            'ownerlogin'=>'max');
        $this->assertTableContainsRecords('jacl2_group', self::$groups);

        self::$usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>self::$grpId5),
            array('login'=>'max', 'id_aclgrp'=>self::$grpId6),
            array('login'=>'max', 'id_aclgrp'=>self::$defaultGroupId),
        );
        $this->assertTableContainsRecords('jacl2_user_group', self::$usergroups);
    }

    /**
     * @depends testCreateUser2
     */
    public function testAddUserIntoGroup(){
        // ajout d'un user dans un groupe
        jAcl2DbUserGroup::createUser('robert');
        self::$grpId7 = '__priv_robert';
        jAcl2DbUserGroup::addUserToGroup('robert', self::$grpId1);

        self::$groups[] = array('id_aclgrp'=>self::$grpId7,
            'name'=>'robert',
            'grouptype'=>2,
            'ownerlogin'=>'robert');
        $this->assertTableContainsRecords('jacl2_group', self::$groups);

        self::$usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>self::$grpId5),
            array('login'=>'max', 'id_aclgrp'=>self::$grpId6),
            array('login'=>'max', 'id_aclgrp'=>self::$defaultGroupId),
            array('login'=>'robert', 'id_aclgrp'=>self::$grpId7),
            array('login'=>'robert', 'id_aclgrp'=>self::$defaultGroupId),
            array('login'=>'robert', 'id_aclgrp'=>self::$grpId1),
        );
        $this->assertTableContainsRecords('jacl2_user_group', self::$usergroups);
    }

    /**
     * @depends testAddUserIntoGroup
     */
    public function testUsersList(){

        // récuperation de la liste des users
        $list = jAcl2DbUserGroup::getUsersList(self::$defaultGroupId)->fetchAll();
        $verif='<array>
    <object>
        <string property="id_aclgrp" value="'.self::$defaultGroupId.'" />
        <string property="login" value="max" />
    </object>
    <object>
        <string property="id_aclgrp" value="'.self::$defaultGroupId.'" />
        <string property="login" value="robert" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);
    }

    /**
     * @depends testUsersList
     */
    public function testRemoveUserFromGroup(){

        // on enleve un user dans un groupe
        jAcl2DbUserGroup::removeUserFromGroup('robert', self::$grpId1);

        self::$usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>self::$grpId5),
            array('login'=>'max', 'id_aclgrp'=>self::$grpId6),
            array('login'=>'max', 'id_aclgrp'=>self::$defaultGroupId),
            array('login'=>'robert', 'id_aclgrp'=>self::$grpId7),
            array('login'=>'robert', 'id_aclgrp'=>self::$defaultGroupId),
        );
        $this->assertTableContainsRecords('jacl2_user_group', self::$usergroups);

    }

    /**
     * @depends testRemoveUserFromGroup
     */
    public function testRemoveUser(){
        // on enleve un user
        jAcl2DbUserGroup::removeUser('robert');
        self::$usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>self::$grpId5),
            array('login'=>'max', 'id_aclgrp'=>self::$grpId6),
            array('login'=>'max', 'id_aclgrp'=>self::$defaultGroupId),
        );
        $this->assertTableContainsRecords('jacl2_user_group', self::$usergroups);
        array_pop(self::$groups);
        $this->assertTableContainsRecords('jacl2_group', self::$groups);
    }

    /**
     * @depends testRemoveUser
     */
    public function testRemoveUsedGroup(){
        // on detruit un groupe qui a des users
        // on ajoute d'abord un user dans un groupe
        jAcl2DbUserGroup::addUserToGroup('max', self::$grpId3);

        self::$usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>self::$grpId5),
            array('login'=>'max', 'id_aclgrp'=>self::$grpId6),
            array('login'=>'max', 'id_aclgrp'=>self::$defaultGroupId),
            array('login'=>'max', 'id_aclgrp'=> self::$grpId3),
        );
        $this->assertTableContainsRecords('jacl2_user_group', self::$usergroups);

        // ok maintenant on supprime le groupe

        jAcl2DbUserGroup::removeGroup(self::$grpId3);
        self::$usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>self::$grpId5),
            array('login'=>'max', 'id_aclgrp'=>self::$grpId6),
            array('login'=>'max', 'id_aclgrp'=>self::$defaultGroupId),
        );
        $this->assertTableContainsRecords('jacl2_user_group', self::$usergroups);
        unset(self::$groups[2]);
        $this->assertTableContainsRecords('jacl2_group', self::$groups);
    }
}
