<?php
/**
* @package
* @subpackage 
* @author
* @copyright
* @link
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

class groupsCtrl extends jController {

    public $pluginParams=array(
        'index'=>array('jacl2.rights.and'=>array('acl.group.view')),
        'saverights'=>array('jacl2.rights.and'=>array('acl.group.view')),
        'newgroup'=>array('jacl2.rights.and'=>array('acl.group.view','acl.group.create')),
        'changename'=>array('jacl2.rights.and'=>array('acl.group.view','acl.group.modify')),
        'delgroup'=>array('jacl2.rights.and'=>array('acl.group.view','acl.group.delete')),
    );

    /**
    *
    */
    function index() {
        $rep = $this->getResponse('html');
        $tpl = new jTpl();

        $gid=array(0);
        $o = new StdClass;
        $o->id_aclgrp ='0';
        $o->name = jLocale::get('jacl2_admin~acl2.anonymous.group.name');
        $o->grouptype=0;
        $groups=array($o);
        $grouprights=array(0=>false);
        foreach(jAcl2DbUserGroup::getGroupList() as $grp) {
            $gid[]=$grp->id_aclgrp;
            $groups[]=$grp;
            $grouprights[$grp->id_aclgrp]=false;
        }
        $rights=array();
        $p = jAcl2Db::getProfil();

        $rs = jDao::get('jelix~jacl2subject',$p)->findAllSubject();
        foreach($rs as $rec){
            $rights[$rec->id_aclsbj] = $grouprights;
        }

        $rs = jDao::get('jelix~jacl2rights',$p)->getRightsByGroups($gid);
        foreach($rs as $rec){
            $rights[$rec->id_aclsbj][$rec->id_aclgrp] = true;
        }

        $tpl->assign(compact('groups', 'rights'));
        if (jAcl2::check('acl.group.modify'))
            $rep->body->assign('MAIN', $tpl->fetch('groups_right'));
        else
            $rep->body->assign('MAIN', $tpl->fetch('groups_right_view'));
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

        $rep->action = 'jacl2_admin~groups:index';
        return $rep;
    }



    function newgroup() {
        $rep = $this->getResponse('redirect');
        $rep->action = 'jacl2_admin~groups:index';

        $name = $this->param('newgroup');
        if($name != '') {
            jAcl2DbUserGroup::createGroup($name);
        }

        return $rep;
    }

    function changename() {
        $rep = $this->getResponse('redirect');
        $rep->action = 'jacl2_admin~groups:index';

        $id = $this->param('group_id');
        $name = $this->param('newname');
        if ($id && $name != '') {
            jAcl2DbUserGroup::updateGroup($id, $name);
        }
        return $rep;
    }

    function delgroup() {
        $rep = $this->getResponse('redirect');
        $rep->action = 'jacl2_admin~groups:index';

        jAcl2DbUserGroup::removeGroup($this->param('group_id'));

        return $rep;
    }

}
