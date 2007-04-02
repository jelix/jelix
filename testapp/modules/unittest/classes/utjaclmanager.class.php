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

class UTjaclmanager extends jUnitTestCaseDb {


    public function testStart(){
        $this->emptyTable('jacl_user_group');
        $this->emptyTable('jacl_rights');
        $this->emptyTable('jacl_right_values');
        $this->emptyTable('jacl_right_values_group');
        $this->emptyTable('jacl_subject');

        $groups= array(array('id_aclgrp'=>1,
            'name'=>'group1',
            'grouptype'=>0,
            'ownerlogin'=>null));
        $this->insertRecordsIntoTable('jacl_group', array('id_aclgrp','name','grouptype','ownerlogin'), $groups, true);


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


    public function testAddSubject(){
//addSubject($subject, $id_aclvalgrp, $label_key)
    }
    public function testRemoveSubject(){
//removeSubject($subject)
    }
    public function testAddRight(){
//addRight($group, $subject, $value , $resource='')
    }
    public function testRemoveRight(){
//removeRight($group, $subject, $value , $resource='')
    }
    public function testAddResourceRight(){

    }
    public function testRemoveResourceRight(){
//removeResourceRight($subject, $resource)
    }
    public function testRemoveSubject2(){
        // remove a subject when rights exists on it
//removeSubject($subject)
    }

}

?>