<?php
/**
* @package     jelix_admin_modules
* @subpackage  jacl2db_admin
* @author      Laurent Jouanneau
* @contributor Julien Issler, Olivier Demah
* @copyright   2008 Laurent Jouanneau
* @copyright   2009 Julien Issler
* @copyright   2010 Olivier Demah
* @link        http://jelix.org
* @licence     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
*/


class groupsCtrl extends jController {

    public $pluginParams=array(
        'index'=>array('jacl2.right'=>'acl.group.view'),
        'rights'=>array('jacl2.rights.and'=>array('acl.group.view','acl.group.modify')),
        'saverights'=>array('jacl2.rights.and'=>array('acl.group.view','acl.group.modify')),
        'newgroup'=>array('jacl2.rights.and'=>array('acl.group.view','acl.group.create')),
        'changename'=>array('jacl2.rights.and'=>array('acl.group.view','acl.group.modify')),
        'delgroup'=>array('jacl2.rights.and'=>array('acl.group.view','acl.group.delete')),
        'setdefault'=>array('jacl2.rights.and'=>array('acl.group.view','acl.group.modify')),
    );

    /**
    *
    */
    function index() {
        $rep = $this->getResponse('html');
        $tpl = new jTpl();

        if (jAcl2::check('acl.group.modify')) {
            $tpl->assign('groups', jAcl2DbUserGroup::getGroupList()->fetchAll());
            $rep->body->assign('MAIN', $tpl->fetch('groups_edit'));
        }
        else {
            $gid=array(0);
            $o = new StdClass;
            $o->id_aclgrp = '0';
            $o->name = jLocale::get('jacl2db_admin~acl2.anonymous.group.name');
            $o->grouptype = 0;
            $groups=array($o);
            $grouprights=array(0=>false);
            foreach(jAcl2DbUserGroup::getGroupList() as $grp) {
                $gid[]=$grp->id_aclgrp;
                $groups[]=$grp;
                $grouprights[$grp->id_aclgrp]=false;
            }
            $rights=array();

            $rs = jDao::get('jacl2db~jacl2subject','jacl2_profile')->findAllSubject();
            foreach($rs as $rec){
                $rights[$rec->id_aclsbj] = $grouprights;
            }

            $rs = jDao::get('jacl2db~jacl2rights','jacl2_profile')->getRightsByGroups($gid);
            foreach($rs as $rec){
                $rights[$rec->id_aclsbj][$rec->id_aclgrp] = true;
            }

            $tpl->assign(compact('groups', 'rights'));
            $rep->body->assign('MAIN', $tpl->fetch('groups_right_view'));
        }
        $rep->body->assign('selectedMenuItem','usersgroups');
        return $rep;
    }

    function rights() {
        $rep = $this->getResponse('html');
        $tpl = new jTpl();

        $gid=array(0);
        $o = new StdClass;
        $o->id_aclgrp ='0';
        $o->name = jLocale::get('jacl2db_admin~acl2.anonymous.group.name');
        $o->grouptype=0;

        $daorights = jDao::get('jacl2db~jacl2rights','jacl2_profile');
        $rightsWithResources = array();
        $hasRightsOnResources = false;

        $groups=array($o);
        $grouprights=array(0=>false);
        foreach(jAcl2DbUserGroup::getGroupList() as $grp) {
            $gid[]=$grp->id_aclgrp;
            $groups[]=$grp;
            $grouprights[$grp->id_aclgrp]=false;

            $rs = $daorights->getRightsHavingRes($grp->id_aclgrp);
            foreach($rs as $rec){
                if (!isset($rightsWithResources[$rec->id_aclsbj]))
                    $rightsWithResources[$rec->id_aclsbj] = array();
                if (!isset($rightsWithResources[$rec->id_aclsbj][$grp->id_aclgrp]))
                    $rightsWithResources[$rec->id_aclsbj][$grp->id_aclgrp] = 0;
                $rightsWithResources[$rec->id_aclsbj][$grp->id_aclgrp] ++;
            }
        }

        $rs = $daorights->getRightsHavingRes(0);
        foreach($rs as $rec){
            if (!isset($rightsWithResources[$rec->id_aclsbj]))
                $rightsWithResources[$rec->id_aclsbj] = array();
            if (!isset($rightsWithResources[$rec->id_aclsbj][0]))
                $rightsWithResources[$rec->id_aclsbj][0] = 0;
            $rightsWithResources[$rec->id_aclsbj][0] ++;
        }

        $rights=array();
        $subjects_localized = array();
        $rs = jDao::get('jacl2db~jacl2subject','jacl2_profile')->findAllSubject();
        foreach($rs as $rec){
            $rights[$rec->id_aclsbj] = $grouprights;
            $subjects_localized[$rec->id_aclsbj] = jLocale::get($rec->label_key);
            if (!isset($rightsWithResources[$rec->id_aclsbj]))
                $rightsWithResources[$rec->id_aclsbj] = array();
        }

        $rs = jDao::get('jacl2db~jacl2rights','jacl2_profile')->getRightsByGroups($gid);
        foreach($rs as $rec){
            $rights[$rec->id_aclsbj][$rec->id_aclgrp] = true;
        }

        $tpl->assign(compact('groups', 'rights', 'subjects_localized', 'rightsWithResources'));
        $rep->body->assign('MAIN', $tpl->fetch('groups_right'));
        $rep->body->assign('selectedMenuItem','usersgroups');
        return $rep;
    }

    function saverights(){
        $rep = $this->getResponse('redirect');
        $rights = $this->param('rights',array());

        foreach(jAcl2DbUserGroup::getGroupList() as $grp) {
            $id = intval($grp->id_aclgrp);
            jAcl2DbManager::setRightsOnGroup($id, (isset($rights[$id])?$rights[$id]:array()));
        }

        jAcl2DbManager::setRightsOnGroup(0, (isset($rights[0])?$rights[0]:array()));
        jMessage::add(jLocale::get('acl2.message.group.rights.ok'), 'ok');
        $rep->action = 'jacl2db_admin~groups:rights';
        return $rep;
    }

    function rightres(){
        $rep = $this->getResponse('html');

        $groupid = $this->intParam('group', null);

        if ($groupid === null || $groupid < 0) {
            $rep->body->assign('MAIN', '<p>invalid group.</p>');
            return $rep;
        }

        $daogroup = jDao::get('jacl2db~jacl2group','jacl2_profile');
        if ($groupid > 0) {
            $group = $daogroup->get($groupid);
            if (!$group) {
                $rep->body->assign('MAIN', '<p>invalid group.</p>');
                return $rep;
            }
            $groupname = $group->name;
        }
        else
            $groupname = jLocale::get('jacl2db_admin~acl2.anonymous.group.name');

        $rightsWithResources = array();
        $daorights = jDao::get('jacl2db~jacl2rights','jacl2_profile');

        $rs = $daorights->getRightsHavingRes($groupid);
        $hasRightsOnResources = false;
        foreach($rs as $rec){
            if (!isset($rightsWithResources[$rec->id_aclsbj]))
                $rightsWithResources[$rec->id_aclsbj] = array();
            $rightsWithResources[$rec->id_aclsbj][] = $rec->id_aclres;
            $hasRightsOnResources = true;
        }
        $subjects_localized = array();
        if(!empty($rightsWithResources)){
            $conditions = jDao::createConditions();
            $conditions->addCondition('id_aclsbj', 'in', array_keys($rightsWithResources));
            foreach(jDao::get('jacl2db~jacl2subject','jacl2_profile')->findBy($conditions) as $rec)
                $subjects_localized[$rec->id_aclsbj] = jLocale::get($rec->label_key);
        }
        $tpl = new jTpl();
        $tpl->assign(compact('groupid', 'groupname', 'subjects_localized', 'rightsWithResources', 'hasRightsOnResources'));

        if(jAcl2::check('acl.group.modify')) {
            $rep->body->assign('MAIN', $tpl->fetch('group_rights_res'));
        }else{
            $rep->body->assign('MAIN', $tpl->fetch('group_rights_res_view'));
        }
        $rep->body->assign('selectedMenuItem','usersgroups');
        return $rep;
    }

    function saverightres(){
        $rep = $this->getResponse('redirect');
        $rep->action = 'jacl2db_admin~groups:rightres';

        $subjects = $this->param('subjects',array());

        $groupid = $this->intParam('group', null);
        if ($groupid === null || $groupid < 0) {
            $rep->action = 'jacl2db_admin~groups:rights';
            return $rep;
        }

        $daogroup = jDao::get('jacl2db~jacl2group', 'jacl2_profile');
        if ($groupid > 0) {
            $group = $daogroup->get($groupid);
            if (!$group) {
                $rep->action = 'jacl2db_admin~groups:rights';

                return $rep;
            }
        }

        $rep->params = array('group'=>$groupid);

        $subjectsToRemove = array();

        foreach($subjects as $sbj=>$val) {
            if ($val != '' || $val == true) {
                $subjectsToRemove[] = $sbj;
            }
        }

        jDao::get('jacl2db~jacl2rights', 'jacl2_profile')
            ->deleteRightsOnResource($groupid, $subjectsToRemove);
        jMessage::add(jLocale::get('jacl2db_admin~acl2.message.group.rights.ok'), 'ok');
        return $rep;
    }

    function setdefault(){
        $rep = $this->getResponse('redirect');
        $groups = $this->param('groups',array());

        foreach(jAcl2DbUserGroup::getGroupList() as $grp) {
            $default = in_array($grp->id_aclgrp, $groups);
            jAcl2DbUserGroup::setDefaultGroup($grp->id_aclgrp, $default);
        }
        jMessage::add(jLocale::get('acl2.message.groups.setdefault.ok'), 'ok');

        $rep->action = 'jacl2db_admin~groups:index';
        return $rep;
    }

    function newgroup() {
        $rep = $this->getResponse('redirect');
        $rep->action = 'jacl2db_admin~groups:index';

        $name = $this->param('newgroup');
        if($name != '') {
            jAcl2DbUserGroup::createGroup($name);
            jMessage::add(jLocale::get('acl2.message.group.create.ok'), 'ok');
        }
        return $rep;
    }

    function changename() {
        $rep = $this->getResponse('redirect');
        $rep->action = 'jacl2db_admin~groups:index';

        $id = $this->param('group_id');
        $name = $this->param('newname');
        if ($id && $name != '') {
            jAcl2DbUserGroup::updateGroup($id, $name);
            jMessage::add(jLocale::get('acl2.message.group.rename.ok'), 'ok');
        }
        return $rep;
    }

    function delgroup() {
        $rep = $this->getResponse('redirect');
        $rep->action = 'jacl2db_admin~groups:index';

        jAcl2DbUserGroup::removeGroup($this->param('group_id'));
        jMessage::add(jLocale::get('acl2.message.group.delete.ok'), 'ok');

        return $rep;
    }
}
