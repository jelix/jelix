<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007-2008 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


if(!class_exists('jAuth',false)) {
    class jAuth {

        static public $connect = true;

        static function isConnected() {
            return self::$connect;
        }

        static function getUserSession() {
            return $_SESSION['JELIX_USER'];
        }
    }
}


class userTest2 {
    public $login;
}


class UTjacl2 extends jUnitTestCaseDb {

    public function testStart(){
        $this->dbProfil = jAcl2Db::getProfil();
        $this->emptyTable('jacl2_rights');
        $this->emptyTable('jacl2_subject');

        $groups= array(array('id_aclgrp'=>1, 'name'=>'group1', 'grouptype'=>0, 'ownerlogin'=>null),
                       array('id_aclgrp'=>2, 'name'=>'group2', 'grouptype'=>0, 'ownerlogin'=>null));

        $this->insertRecordsIntoTable('jacl2_group', array('id_aclgrp','name','grouptype','ownerlogin'), $groups, true);

        $_SESSION['JELIX_USER'] = new userTest2();
        $_SESSION['JELIX_USER']->login = 'laurent';

        $usergroups=array(
            array('login'=>'laurent', 'id_aclgrp'=>1),
        );
        $this->insertRecordsIntoTable('jacl2_user_group', array('login','id_aclgrp'), $usergroups, true);
    }

    public function testIsMemberOfGroup(){
        $this->assertTrue(jAcl2DbUserGroup::isMemberOfGroup (1));
        $this->assertFalse(jAcl2DbUserGroup::isMemberOfGroup (2));
    }

    public function testCheckRight(){
        jAcl2DbManager::addSubject('super.cms.list', 'cms~rights.super.cms');
        jAcl2DbManager::addSubject('super.cms.update', 'cms~rights.super.cms');
        jAcl2DbManager::addSubject('super.cms.delete', 'cms~rights.super.cms');
        jAcl2DbManager::addSubject('admin.access',1 , 'admin~rights.access');
        jAcl2DbManager::addRight(1, 'super.cms.list' );
        jAcl2DbManager::addRight(1, 'super.cms.update' );
        jAcl2DbManager::addRight(1, 'super.cms.delete', 154);

        $this->assertTrue(jAcl2::check('super.cms.list'));
        $this->assertTrue(jAcl2::check('super.cms.update'));
        $this->assertFalse(jAcl2::check('super.cms.delete'));
        $this->assertFalse(jAcl2::check('super.cms.create')); // doesn't exist
        $this->assertFalse(jAcl2::check('super.cms.read'));// doesn't exist
        $this->assertFalse(jAcl2::check('super.cms.delete'));// doesn't exist



        $this->assertFalse(jAcl2::check('admin.access'));
        $this->assertTrue(jAcl2::check('super.cms.list',154)); // droit sur une ressource
        $this->assertTrue(jAcl2::check('super.cms.update',154)); // droit sur une ressource
        $this->assertTrue(jAcl2::check('super.cms.delete',154)); // droit sur une ressource
        $this->assertTrue(jAcl2::check('super.cms.list',122)); // ressource non repertoriée
        $this->assertTrue(jAcl2::check('super.cms.update',122)); // ressource non repertoriée
        $this->assertFalse(jAcl2::check('super.cms.delete',122)); // ressource non repertoriée

        jAcl2DbManager::addRight(1, 'admin.access');

        $this->assertTrue(jAcl2::check('admin.access'));

    }

    public function testGetRightDisconnect(){
        jAuth::$connect = false;
        jAcl2::clearCache();
        $this->assertFalse(jAcl2::check('super.cms.list'));
        $this->assertFalse(jAcl2::check('admin.access'));
        jAcl2::clearCache();
        jAcl2DbManager::addRight(0, 'super.cms.list' );
        $this->assertTrue(jAcl2::check('super.cms.list'));
        $this->assertFalse(jAcl2::check('admin.access'));
        jAuth::$connect = true;
        jAcl2::clearCache();
    }


    public function testEnd(){
        $_SESSION['JELIX_USER']=null;
    }

}

?>