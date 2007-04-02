<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


require_once(dirname(__FILE__).'/junittestcasedb.class.php');

class jAuth {

    static public $connect = true;

    static function isConnected() {
        return self::$connect;
    }
}

class userTest {
    public $login;
}


class UTjacl extends jUnitTestCaseDb {


    public function testStart(){
        $this->emptyTable('jacl_rights');
        $this->emptyTable('jacl_subject');

        $groups= array(array('id_aclgrp'=>1, 'name'=>'group1', 'grouptype'=>0, 'ownerlogin'=>null),
                       array('id_aclgrp'=>2, 'name'=>'group2', 'grouptype'=>0, 'ownerlogin'=>null));

        $this->insertRecordsIntoTable('jacl_group', array('id_aclgrp','name','grouptype','ownerlogin'), $groups, true);

        $_SESSION['JELIX_USER'] = new userTest();
        $_SESSION['JELIX_USER']->login = 'laurent';

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

    public function testIsMemberOfGroup(){
        $this->assertTrue(jAcl::isMemberOfGroup (1));
        $this->assertFalse(jAcl::isMemberOfGroup (2));
    }

    public function testGetRight(){
        jAclManager::addSubject('super.cms',2 , 'cms~rights.super.cms');
        jAclManager::addSubject('admin.access',1 , 'admin~rights.access');
        jAclManager::addRight(1, 'super.cms', 'LIST' );
        jAclManager::addRight(1, 'super.cms', 'UPDATE' );

        $this->assertEqual(jAcl::getRight('super.cms'), array('LIST','UPDATE'));
        $this->assertEqual(jAcl::getRight('admin.access'), array());

        jAclManager::addRight(1, 'admin.access', 'TRUE' );

        $this->assertEqual(jAcl::getRight('admin.access'), array('TRUE'));

    }

    public function testGetRightDisconnect(){
        jAuth::$connect = false;
        jAcl::clearCache();
        $this->assertEqual(jAcl::getRight('super.cms'), array());
        $this->assertEqual(jAcl::getRight('admin.access'), array());
        jAuth::$connect = true;
        jAcl::clearCache();
    }

    public function testCheck(){
        //jAcl::check($subject, $value, $resource=null)


    }


    public function testEnd(){
        $_SESSION['JELIX_USER']=null;
    }

}

?>