<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


/**
 * Tests on user and group management in jAcl
 * CAREFULL ! DON'T CHANGE THE ORDER OF METHODS
 */
class UTjaclusergroup extends jUnitTestCaseDb {
    
    protected $groups;
    protected $defaultGroupId;

    protected $grpId1;
    protected $grpId2;
    protected $grpId3;
    protected $grpId4;
    protected $grpId5;
    protected $grpId6;
    protected $grpId7;

    public function testStart(){
        $this->dbProfile = jAclDb::getProfile();

        $this->emptyTable('jacl_user_group');
        $this->emptyTable('jacl_group');
    }

    public function testCreateGroup(){

        // creation d'un groupe

        $this->grpId1 = jAclDbUserGroup::createGroup('group1');
        $this->assertTrue($this->grpId1 != '', 'jAclDbUserGroup::createGroup failed : id is empty');
        $this->groups = array(array('id_aclgrp'=>$this->grpId1,
            'name'=>'group1',
            'grouptype'=>0,
            'ownerlogin'=>null));
        $this->assertTableContainsRecords('jacl_group', $this->groups);

        // creation de deux autres groupes

        $this->grpId2 = jAclDbUserGroup::createGroup('group2');
        $this->grpId3 = jAclDbUserGroup::createGroup('group3');
        $this->groups[] = array('id_aclgrp'=>$this->grpId2,
            'name'=>'group2',
            'grouptype'=>0,
            'ownerlogin'=>null);
        $this->groups[] = array('id_aclgrp'=>$this->grpId3,
            'name'=>'group3',
            'grouptype'=>0,
            'ownerlogin'=>null);
        $this->assertTableContainsRecords('jacl_group', $this->groups);

    }

    public function testDefaultGroup(){
        // on met un des groupes par defaut
        jAclDbUserGroup::setDefaultGroup($this->grpId2,false);
        $this->assertTableContainsRecords('jacl_group', $this->groups);
        jAclDbUserGroup::setDefaultGroup($this->grpId2,true);
        $this->defaultGroupId = $this->grpId2; // for next test method
        $this->groups[1]['grouptype']=1;
        $this->assertTableContainsRecords('jacl_group', $this->groups);
    }

    public function testRenameGroup(){
        // changement de nom d'un groupe
        jAclDbUserGroup::updateGroup($this->grpId3, 'newgroup3');
        $this->groups[2]['name']='newgroup3';
        $this->assertTableContainsRecords('jacl_group', $this->groups);
    }

    public function testGroupList(){
        // recuperation de la liste de tous les groupes
        $list = jAclDbUserGroup::getGroupList()->fetchAll();

        $verif='<array>
    <object>
        <string property="id_aclgrp" value="'.$this->grpId1.'" />
        <string property="name" value="group1" />
        <string property="grouptype" value="0" />
        <null property="ownerlogin"/>
    </object>
    <object>
        <string property="id_aclgrp" value="'.$this->grpId2.'" />
        <string property="name" value="group2" />
        <string property="grouptype" value="1" />
        <null property="ownerlogin"/>
    </object>
    <object>
        <string property="id_aclgrp" value="'.$this->grpId3.'" />
        <string property="name" value="newgroup3" />
        <string property="grouptype" value="0" />
        <null property="ownerlogin"/>
    </object>
</array>';

        $this->assertComplexIdenticalStr($list, $verif);
    }

    public function testRemoveGroup(){
        // creation d'un autre groupe
        $this->grpId4 = jAclDbUserGroup::createGroup('group4');
        $records2 = $this->groups;
        $records2[] = array('id_aclgrp'=>$this->grpId4,
            'name'=>'group4',
            'grouptype'=>0,
            'ownerlogin'=>null);
        $this->assertTableContainsRecords('jacl_group', $records2);

        // destruction d'un groupe (ici qui n'a pas de user)
        jAclDbUserGroup::removeGroup($this->grpId4);
        $this->assertTableContainsRecords('jacl_group', $this->groups);

    }

    protected $usergroups=array();

    public function testCreateUser(){
        $this->assertTableIsEmpty('jacl_user_group');

        // creation d'un user dans les acl, sans le mettre dans les groupes par defaut
        jAclDbUserGroup::createUser('laurent',false);
        $this->grpId5 = $this->getLastId('id_aclgrp', 'jacl_group');

        $this->groups[] = array('id_aclgrp'=>$this->grpId5,
            'name'=>'laurent',
            'grouptype'=>2,
            'ownerlogin'=>'laurent');
        $this->assertTableContainsRecords('jacl_group', $this->groups);

        $this->usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>$this->grpId5),
        );
        $this->assertTableContainsRecords('jacl_user_group', $this->usergroups);
    }

    public function testCreateUser2(){
        // creation d'un deuxième user dans les acl, en le mettant 
        // dans les groupes par defaut
        jAclDbUserGroup::createUser('max');
        $this->grpId6 = $this->getLastId('id_aclgrp', 'jacl_group');

        $this->groups[] = array('id_aclgrp'=>$this->grpId6,
            'name'=>'max',
            'grouptype'=>2,
            'ownerlogin'=>'max');
        $this->assertTableContainsRecords('jacl_group', $this->groups);

        $this->usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>$this->grpId5),
            array('login'=>'max', 'id_aclgrp'=>$this->grpId6),
            array('login'=>'max', 'id_aclgrp'=>$this->defaultGroupId),
        );
        $this->assertTableContainsRecords('jacl_user_group', $this->usergroups);
    }

    public function testAddUserIntoGroup(){
        // ajout d'un user dans un groupe
        jAclDbUserGroup::createUser('robert');
        $this->grpId7 = $this->getLastId('id_aclgrp', 'jacl_group');
        jAclDbUserGroup::addUserToGroup('robert', $this->grpId1);

        $this->groups[] = array('id_aclgrp'=>$this->grpId7,
            'name'=>'robert',
            'grouptype'=>2,
            'ownerlogin'=>'robert');
        $this->assertTableContainsRecords('jacl_group', $this->groups);

        $this->usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>$this->grpId5),
            array('login'=>'max', 'id_aclgrp'=>$this->grpId6),
            array('login'=>'max', 'id_aclgrp'=>$this->defaultGroupId),
            array('login'=>'robert', 'id_aclgrp'=>$this->grpId7),
            array('login'=>'robert', 'id_aclgrp'=>$this->defaultGroupId),
            array('login'=>'robert', 'id_aclgrp'=>$this->grpId1),
        );
        $this->assertTableContainsRecords('jacl_user_group', $this->usergroups);
    }

    public function testUsersList(){

        // récuperation de la liste des users
        $list = jAclDbUserGroup::getUsersList($this->defaultGroupId)->fetchAll();
        $verif='<array>
    <object>
        <string property="id_aclgrp" value="'.$this->defaultGroupId.'" />
        <string property="login" value="max" />
    </object>
    <object>
        <string property="id_aclgrp" value="'.$this->defaultGroupId.'" />
        <string property="login" value="robert" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);
    }

    public function testRemoveUserFromGroup(){

        // on enleve un user dans un groupe
        jAclDbUserGroup::removeUserFromGroup('robert', $this->grpId1);

        $this->usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>$this->grpId5),
            array('login'=>'max', 'id_aclgrp'=>$this->grpId6),
            array('login'=>'max', 'id_aclgrp'=>$this->defaultGroupId),
            array('login'=>'robert', 'id_aclgrp'=>$this->grpId7),
            array('login'=>'robert', 'id_aclgrp'=>$this->defaultGroupId),
        );
        $this->assertTableContainsRecords('jacl_user_group', $this->usergroups);

    }

    public function testRemoveUser(){
        // on enleve un user
        jAclDbUserGroup::removeUser('robert');
        $this->usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>$this->grpId5),
            array('login'=>'max', 'id_aclgrp'=>$this->grpId6),
            array('login'=>'max', 'id_aclgrp'=>$this->defaultGroupId),
        );
        $this->assertTableContainsRecords('jacl_user_group', $this->usergroups);
        array_pop($this->groups);
        $this->assertTableContainsRecords('jacl_group', $this->groups);
    }

    public function testRemoveUsedGroup(){
        // on detruit un groupe qui a des users
        // on ajoute d'abord un user dans un groupe
        jAclDbUserGroup::addUserToGroup('max', $this->grpId3);

        $this->usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>$this->grpId5),
            array('login'=>'max', 'id_aclgrp'=>$this->grpId6),
            array('login'=>'max', 'id_aclgrp'=>$this->defaultGroupId),
            array('login'=>'max', 'id_aclgrp'=> $this->grpId3),
        );
        $this->assertTableContainsRecords('jacl_user_group', $this->usergroups);

        // ok maintenant on supprime le groupe


        
        jAclDbUserGroup::removeGroup($this->grpId3);
        $this->usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>$this->grpId5),
            array('login'=>'max', 'id_aclgrp'=>$this->grpId6),
            array('login'=>'max', 'id_aclgrp'=>$this->defaultGroupId),
        );
        $this->assertTableContainsRecords('jacl_user_group', $this->usergroups);
        unset($this->groups[2]);
        $this->assertTableContainsRecords('jacl_group', $this->groups);


    }
}

?>