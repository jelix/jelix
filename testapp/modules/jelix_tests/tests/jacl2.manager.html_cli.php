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

class UTjacl2manager extends jUnitTestCaseDb {


    public function testStart(){
        $this->dbProfile = 'jacl2_profile';
        $this->emptyTable('jacl2_user_group');
        $this->emptyTable('jacl2_rights');
        $this->emptyTable('jacl2_subject');

        $groups= array(array('id_aclgrp'=>'group1', 'name'=>'Group 1', 'grouptype'=>0, 'ownerlogin'=>null));

        $this->insertRecordsIntoTable('jacl2_group', array('id_aclgrp','name','grouptype','ownerlogin'), $groups, true);
    }

    protected $subjects;

    public function testAddSubject(){
        jAcl2DbManager::addSubject('super.cms' , 'cms~rights.super.cms');
        $this->subjects = array(
            array('id_aclsbj'=>'super.cms', 'label_key'=>'cms~rights.super.cms'),
        );
        $this->assertTableContainsRecords('jacl2_subject', $this->subjects);

        jAcl2DbManager::addSubject('jxacl.groups.management', 'jxacl~db.sbj.groups.management');
        jAcl2DbManager::addSubject('admin.access', 'admin~rights.access');
        jAcl2DbManager::addSubject('admin.foo', 'admin~rights.foo');

        $this->subjects[] = array('id_aclsbj'=>'jxacl.groups.management', 'label_key'=>'jxacl~db.sbj.groups.management');
        $this->subjects[] = array('id_aclsbj'=>'admin.access', 'label_key'=>'admin~rights.access');
        $this->subjects[] = array('id_aclsbj'=>'admin.foo', 'label_key'=>'admin~rights.foo');

        $this->assertTableContainsRecords('jacl2_subject', $this->subjects);
    }

    public function testRemoveSubject(){
        jAcl2DbManager::removeSubject('admin.foo');
        array_pop($this->subjects);
        $this->assertTableContainsRecords('jacl2_subject', $this->subjects);
    }

    protected $rights;
    public function testAddRight(){
        jAcl2DbManager::addSubject('super.cms.list' , 'cms~rights.super.cms.list');
        jAcl2DbManager::addSubject('super.cms.update' , 'cms~rights.super.cms.update');
        $this->subjects[] = array('id_aclsbj'=>'super.cms.list', 'label_key'=>'cms~rights.super.cms.list');
        $this->subjects[] = array('id_aclsbj'=>'super.cms.update', 'label_key'=>'cms~rights.super.cms.update');
        $this->assertTableContainsRecords('jacl2_subject', $this->subjects);

        $this->assertTrue(jAcl2DbManager::addRight('group1', 'super.cms.list' ));
        $this->rights = array(array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> null));
        $this->assertTableContainsRecords('jacl2_rights', $this->rights);

        $this->assertTrue(jAcl2DbManager::addRight('group1', 'admin.access'));
        $this->rights[] = array('id_aclsbj'=>'admin.access' ,'id_aclgrp'=>'group1', 'id_aclres'=> null);
        $this->assertTableContainsRecords('jacl2_rights', $this->rights);

        $this->assertFalse(jAcl2DbManager::addRight('group1', 'admin.access.bla'));
        $this->assertFalse(jAcl2DbManager::addRight('group1', 'admin.dont.exist'));
        $this->assertTrue(jAcl2DbManager::addRight('group1', 'super.cms.list' )); // on tente d'inserer le meme droit
        $this->assertTableContainsRecords('jacl2_rights', $this->rights);
    }

    public function testRemoveRight(){
        jAcl2DbManager::removeRight('group1', 'admin.access' );
        $r = $this->rights;
        array_pop($r);
        $this->assertTableContainsRecords('jacl2_rights', $r);
        $this->assertTrue(jAcl2DbManager::addRight('group1', 'admin.access' ));
    }

    public function testAddResourceRight(){
        $this->assertTrue(jAcl2DbManager::addRight('group1', 'super.cms.update', 154));
        $this->assertTrue(jAcl2DbManager::addRight('group1', 'super.cms.update', 92));
        $this->rights[] = array('id_aclsbj'=>'super.cms.update' ,'id_aclgrp'=>'group1', 'id_aclres'=> '154');
        $this->rights[] = array('id_aclsbj'=>'super.cms.update' ,'id_aclgrp'=>'group1', 'id_aclres'=> '92');
        $this->assertTableContainsRecords('jacl2_rights', $this->rights);
    }
    public function testRemoveResourceRight(){
        jAcl2DbManager::removeResourceRight('super.cms.update', 92);
        array_pop($this->rights);
        $this->assertTableContainsRecords('jacl2_rights', $this->rights);
    }

    public function testRemoveSubject2(){
        // remove a subject when rights exists on it
        jAcl2DbManager::removeSubject('super.cms.update');
        array_pop($this->subjects);
        $this->assertTableContainsRecords('jacl2_subject', $this->subjects);

        $this->rights=  array( array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> null),
                                array('id_aclsbj'=>'admin.access' ,'id_aclgrp'=>'group1', 'id_aclres'=> null));
        $this->assertTableContainsRecords('jacl2_rights', $this->rights);
    }
    
    
    public function testSetRightsOnGroup() {
      $this->emptyTable('jacl2_user_group');
      $this->emptyTable('jacl2_rights');
      $this->emptyTable('jacl2_subject');

      $groups= array(array('id_aclgrp'=>'group1', 'name'=>'group1', 'grouptype'=>0, 'ownerlogin'=>null),
                     array('id_aclgrp'=>'group2', 'name'=>'group2', 'grouptype'=>0, 'ownerlogin'=>null));

      $this->insertRecordsIntoTable('jacl2_group', array('id_aclgrp','name','grouptype','ownerlogin'), $groups, true);

      jAcl2DbManager::addSubject('super.cms.list' , 'cms~rights.super.cms.list');
      jAcl2DbManager::addSubject('super.cms.update' , 'cms~rights.super.cms.update');
      jAcl2DbManager::addSubject('super.cms.create' , 'cms~rights.super.cms.update');
      jAcl2DbManager::addSubject('super.cms.view' , 'cms~rights.super.cms.update');
      jAcl2DbManager::addSubject('super.cms.delete' , 'cms~rights.super.cms.delete');
      
      $rights = array();
      $this->assertTableContainsRecords('jacl2_rights', $rights);
      
      // rights for group 1
      $newRights = array('super.cms.list'=>true, 'super.cms.create'=>true);
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> null),
                      array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> null),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);
      
      // rights for group 2 (we won't modify them, we add them to verify that changes on rights of group1
      // won't changed rights of group 2)
      $newRights = array('super.cms.list'=>true, 'super.cms.view'=>true);
      jAcl2DbManager::setRightsOnGroup('group2', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> null),
                      array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> null),
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> null),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> null),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // add a right for group 1
      $newRights = array('super.cms.list'=>true, 'super.cms.create'=>true, 'super.cms.delete'=>true);
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> null),
                      array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> null),
                      array('id_aclsbj'=>'super.cms.delete' ,'id_aclgrp'=>'group1', 'id_aclres'=> null),
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> null),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> null),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // remove rights for group 1
      $newRights = array('super.cms.list'=>true, 'super.cms.create'=>false);
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> null),
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> null),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> null),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);
      
      // new rights for group 1, by deleting existing one and adding new ones
      $newRights = array( 'super.cms.create'=>true, 'super.cms.update'=>true);
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.update' ,'id_aclgrp'=>'group1', 'id_aclres'=> null),
                      array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> null),
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> null),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> null),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);
      
      // remove all rights for group 1
      $newRights = array();
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> null),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> null),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);
    }
}

?>