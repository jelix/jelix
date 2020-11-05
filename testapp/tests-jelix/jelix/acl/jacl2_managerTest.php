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

class jacl2_managerTest extends jUnitTestCaseDb {


    protected static $firstSetup = true;
    
    public function setUp(){
        self::initClassicRequest(TESTAPP_URL.'index.php');
        if (!self::$firstSetup) {
            return;
        }
        self::$firstSetup = false;
        $this->dbProfile = 'jacl2_profile';
        $this->emptyTable('jacl2_user_group');
        $this->emptyTable('jacl2_rights');
        $this->emptyTable('jacl2_subject');

        $groups= array(array('id_aclgrp'=>'group1', 'name'=>'Group 1', 'grouptype'=>0, 'ownerlogin'=>null));

        $this->insertRecordsIntoTable('jacl2_group', array('id_aclgrp','name','grouptype','ownerlogin'), $groups, true);
    }

    protected static $subjects;

    public function testAddSubject(){
        jAcl2DbManager::createRight('super.cms' , 'cms~rights.super.cms');
        self::$subjects = array(
            array('id_aclsbj'=>'super.cms', 'label_key'=>'cms~rights.super.cms'),
        );
        $this->assertTableContainsRecords('jacl2_subject', self::$subjects);

        jAcl2DbManager::createRight('jxacl.groups.management', 'jxacl~db.sbj.groups.management');
        jAcl2DbManager::createRight('admin.access', 'admin~rights.access');
        jAcl2DbManager::createRight('admin.foo', 'admin~rights.foo');

        self::$subjects[] = array('id_aclsbj'=>'jxacl.groups.management', 'label_key'=>'jxacl~db.sbj.groups.management');
        self::$subjects[] = array('id_aclsbj'=>'admin.access', 'label_key'=>'admin~rights.access');
        self::$subjects[] = array('id_aclsbj'=>'admin.foo', 'label_key'=>'admin~rights.foo');

        $this->assertTableContainsRecords('jacl2_subject', self::$subjects);
    }

    /**
     * @depends testAddSubject
     */
    public function testRemoveSubject(){
        jAcl2DbManager::deleteRight('admin.foo');
        array_pop(self::$subjects);
        $this->assertTableContainsRecords('jacl2_subject', self::$subjects);
    }

    protected static $rights;
    /**
     * @depends testRemoveSubject
     */
    public function testAddRight(){
        jAcl2DbManager::createRight('super.cms.list' , 'cms~rights.super.cms.list');
        jAcl2DbManager::createRight('super.cms.list2' , 'cms~rights.super.cms.list');
        jAcl2DbManager::createRight('super.cms.update' , 'cms~rights.super.cms.update');
        self::$subjects[] = array('id_aclsbj'=>'super.cms.list', 'label_key'=>'cms~rights.super.cms.list');
        self::$subjects[] = array('id_aclsbj'=>'super.cms.list2', 'label_key'=>'cms~rights.super.cms.list');
        self::$subjects[] = array('id_aclsbj'=>'super.cms.update', 'label_key'=>'cms~rights.super.cms.update');
        $this->assertTableContainsRecords('jacl2_subject', self::$subjects);

        // ok, we have all subjects we need. let's add some rights

        $this->assertTrue(jAcl2DbManager::addRight('group1', 'super.cms.list' ));
        $this->assertTrue(jAcl2DbManager::addRight('group1', 'super.cms.list2' ));
        self::$rights = array(array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>0),
                              array('id_aclsbj'=>'super.cms.list2' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>0));
        $this->assertTableContainsRecords('jacl2_rights', self::$rights);

        // let's cancel a right, and let's see if adding the same right remove the cancel status
        jAcl2DbManager::removeRight('group1', 'super.cms.list2', '', true);
        self::$rights = array(array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>0),
                              array('id_aclsbj'=>'super.cms.list2' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>1));
        $this->assertTableContainsRecords('jacl2_rights', self::$rights);

        $this->assertTrue(jAcl2DbManager::addRight('group1', 'super.cms.list2' ));

        self::$rights = array(array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>0),
                              array('id_aclsbj'=>'super.cms.list2' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>0));
        $this->assertTableContainsRecords('jacl2_rights', self::$rights);

        // let's add a single right
        $this->assertTrue(jAcl2DbManager::addRight('group1', 'admin.access'));
        self::$rights[] = array('id_aclsbj'=>'admin.access' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>0);
        $this->assertTableContainsRecords('jacl2_rights', self::$rights);

        // let's insert some inexisting rights
        $this->assertFalse(jAcl2DbManager::addRight('group1', 'admin.access.bla'));
        $this->assertFalse(jAcl2DbManager::addRight('group1', 'admin.dont.exist'));
        // let's insert an existing right
        $this->assertTrue(jAcl2DbManager::addRight('group1', 'super.cms.list' ));
        $this->assertTableContainsRecords('jacl2_rights', self::$rights);
    }

    /**
     * @depends testAddRight
     */
    public function testRemoveRight(){
        // let's cancel an existing right
        jAcl2DbManager::removeRight('group1', 'admin.access', '', true);
        $r = array(array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>0),
                  array('id_aclsbj'=>'super.cms.list2' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>0),
                  array('id_aclsbj'=>'admin.access' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>1));
        $this->assertTableContainsRecords('jacl2_rights', $r);

        // let's remove an existing right
        jAcl2DbManager::removeRight('group1', 'admin.access' );
        array_pop($r);
        $this->assertTableContainsRecords('jacl2_rights', $r);

        $this->assertTrue(jAcl2DbManager::addRight('group1', 'admin.access' ));
    }

    /**
     * @depends testRemoveRight
     */
    public function testAddResourceRight(){
        $this->assertTrue(jAcl2DbManager::addRight('group1', 'super.cms.update', 154));
        $this->assertTrue(jAcl2DbManager::addRight('group1', 'super.cms.update', 92));
        self::$rights[] = array('id_aclsbj'=>'super.cms.update' ,'id_aclgrp'=>'group1', 'id_aclres'=> '154');
        self::$rights[] = array('id_aclsbj'=>'super.cms.update' ,'id_aclgrp'=>'group1', 'id_aclres'=> '92');
        $this->assertTableContainsRecords('jacl2_rights', self::$rights);
    }
    /**
     * @depends testAddResourceRight
     */
    public function testRemoveResourceRight(){
        jAcl2DbManager::removeResourceRight('super.cms.update', 92);
        array_pop(self::$rights);
        $this->assertTableContainsRecords('jacl2_rights', self::$rights);
    }

    /**
     * @depends testRemoveResourceRight
     */
    public function testRemoveSubject2(){
        // remove a subject when rights exists on it
        jAcl2DbManager::deleteRight('super.cms.update');
        array_pop(self::$subjects);
        $this->assertTableContainsRecords('jacl2_subject', self::$subjects);

        self::$rights=  array( array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-'),
                              array('id_aclsbj'=>'super.cms.list2' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-'),
                                array('id_aclsbj'=>'admin.access' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-'));
        $this->assertTableContainsRecords('jacl2_rights', self::$rights);
    }


    /**
     * @depends testRemoveSubject2
     */
    public function testSetRightsOnGroup() {
      $this->emptyTable('jacl2_user_group');
      $this->emptyTable('jacl2_rights');
      $this->emptyTable('jacl2_subject');

      $groups= array(array('id_aclgrp'=>'group1', 'name'=>'group1', 'grouptype'=>0, 'ownerlogin'=>null),
                     array('id_aclgrp'=>'group2', 'name'=>'group2', 'grouptype'=>0, 'ownerlogin'=>null));

      $this->insertRecordsIntoTable('jacl2_group', array('id_aclgrp','name','grouptype','ownerlogin'), $groups, true);

      jAcl2DbManager::createRight('super.cms.list' , 'cms~rights.super.cms.list');
      jAcl2DbManager::createRight('super.cms.update' , 'cms~rights.super.cms.update');
      jAcl2DbManager::createRight('super.cms.create' , 'cms~rights.super.cms.update');
      jAcl2DbManager::createRight('super.cms.view' , 'cms~rights.super.cms.update');
      jAcl2DbManager::createRight('super.cms.delete' , 'cms~rights.super.cms.delete');

      $rights = array();
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // rights for group 1
      $newRights = array('super.cms.list'=>true, 'super.cms.create'=>true);
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // rights for group 2 (we won't modify them, we add them to verify that changes on rights of group1
      // won't changed rights of group 2)
      $newRights = array('super.cms.list'=>true, 'super.cms.view'=>true);
      jAcl2DbManager::setRightsOnGroup('group2', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // add a right for group 1
      $newRights = array('super.cms.list'=>true, 'super.cms.create'=>true, 'super.cms.delete'=>true);
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.delete' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // remove rights for group 1
      $newRights = array('super.cms.list'=>true, 'super.cms.create'=>false);
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // new rights for group 1, by deleting existing one and adding new ones
      $newRights = array( 'super.cms.create'=>true, 'super.cms.update'=>true);
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.update' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // remove all rights for group 1
      $newRights = array();
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);
    }

    /**
     * @depends testSetRightsOnGroup
     */
    public function testSetNewRightsOnGroup() {
      $this->emptyTable('jacl2_user_group');
      $this->emptyTable('jacl2_rights');
      $this->emptyTable('jacl2_subject');

      $groups= array(array('id_aclgrp'=>'group1', 'name'=>'group1', 'grouptype'=>0, 'ownerlogin'=>null),
                     array('id_aclgrp'=>'group2', 'name'=>'group2', 'grouptype'=>0, 'ownerlogin'=>null));

      $this->insertRecordsIntoTable('jacl2_group', array('id_aclgrp','name','grouptype','ownerlogin'), $groups, true);

      jAcl2DbManager::createRight('super.cms.list' , 'cms~rights.super.cms.list');
      jAcl2DbManager::createRight('super.cms.update' , 'cms~rights.super.cms.update');
      jAcl2DbManager::createRight('super.cms.create' , 'cms~rights.super.cms.update');
      jAcl2DbManager::createRight('super.cms.view' , 'cms~rights.super.cms.update');
      jAcl2DbManager::createRight('super.cms.delete' , 'cms~rights.super.cms.delete');

      $rights = array();
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // rights for group 1
      $newRights = array('super.cms.list'=>'y', 'super.cms.create'=>'y');
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // rights for group 2 (we won't modify them, we add them to verify that changes on rights of group1
      // won't changed rights of group 2)
      $newRights = array('super.cms.list'=>'y', 'super.cms.view'=>'y');
      jAcl2DbManager::setRightsOnGroup('group2', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // add a right for group 1
      $newRights = array('super.cms.list'=>'y', 'super.cms.create'=>'y', 'super.cms.delete'=>'y');
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.delete' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // remove rights for group 1
      $newRights = array('super.cms.list'=>'y', 'super.cms.create'=>'');
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // new rights for group 1, by deleting existing one and adding new ones
      $newRights = array( 'super.cms.create'=>'y', 'super.cms.update'=>'y');
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.update' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // cancel a right for group 1
      $newRights = array( 'super.cms.create'=>'y', 'super.cms.update'=>'n');
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.update' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'1'),
                      array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // changed a canceled right to an approuve right for group 1
      $newRights = array( 'super.cms.create'=>'y', 'super.cms.update'=>'y');
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.update' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);

      // remove all rights for group 1
      $newRights = array();
      jAcl2DbManager::setRightsOnGroup('group1', $newRights);
      $rights = array(
                      array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                      array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
                );
      $this->assertTableContainsRecords('jacl2_rights', $rights);
    }

    /**
     * @depends testSetNewRightsOnGroup
     */
    public function testCopyRightSettings() {
        $this->emptyTable('jacl2_user_group');
        $this->emptyTable('jacl2_rights');
        $this->emptyTable('jacl2_subject');

        $groups= array(
            array('id_aclgrp'=>'group1', 'name'=>'group1', 'grouptype'=>0, 'ownerlogin'=>null),
            array('id_aclgrp'=>'group2', 'name'=>'group2', 'grouptype'=>0, 'ownerlogin'=>null)
        );

        $this->insertRecordsIntoTable('jacl2_group', array('id_aclgrp','name','grouptype','ownerlogin'), $groups, true);

        jAcl2DbManager::createRight('super.cms.list' , 'cms~rights.super.cms.list');
        jAcl2DbManager::createRight('super.cms.update' , 'cms~rights.super.cms.update');
        jAcl2DbManager::createRight('super.cms.create' , 'cms~rights.super.cms.update');
        jAcl2DbManager::createRight('super.cms.view' , 'cms~rights.super.cms.update');
        jAcl2DbManager::createRight('super.cms.delete' , 'cms~rights.super.cms.delete');

        // rights for group 1
        $newRights = array('super.cms.list'=>true, 'super.cms.create'=>true);
        jAcl2DbManager::setRightsOnGroup('group1', $newRights);
        $rights = array(
            array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
            array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
        );
        $this->assertTableContainsRecords('jacl2_rights', $rights);

        // rights for group 2
        $newRights = array('super.cms.list'=>'y');
        jAcl2DbManager::setRightsOnGroup('group2', $newRights);
        $rights = array(
            array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
            array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
            array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
        );
        $this->assertTableContainsRecords('jacl2_rights', $rights);


        jAcl2DbManager::copyRightSettings('super.cms.list', 'super.cms.view');
        $rights = array(
            array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
            array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
            array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
            array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
            array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
        );
        $this->assertTableContainsRecords('jacl2_rights', $rights);


        jAcl2DbManager::removeRight('group1', 'super.cms.list', '-', true);
        $rights = array(
            array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'1'),
            array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
            array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
            array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
            array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
        );
        $this->assertTableContainsRecords('jacl2_rights', $rights);

        jAcl2DbManager::copyRightSettings('super.cms.list', 'super.cms.view');
        $rights = array(
            array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'1'),
            array('id_aclsbj'=>'super.cms.create' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'0'),
            array('id_aclsbj'=>'super.cms.list' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
            array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group1', 'id_aclres'=> '-', 'canceled'=>'1'),
            array('id_aclsbj'=>'super.cms.view' ,'id_aclgrp'=>'group2', 'id_aclres'=> '-', 'canceled'=>'0'),
        );
        $this->assertTableContainsRecords('jacl2_rights', $rights);


    }
}

