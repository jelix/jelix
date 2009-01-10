<?php
/**
* @package   jelix_admin_modules
* @subpackage jacl2db_admin
* @author    Laurent Jouanneau
* @copyright 2008 Laurent Jouanneau
* @link      http://jelix.org
* @licence   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
*/

class usersCtrl extends jController {

    public $pluginParams=array(
        'index'=>array('jacl2.rights.and'=>array('acl.user.view')),
        'rights'=>array('jacl2.rights.and'=>array('acl.user.view')),
        'saverights'=>array('jacl2.rights.and'=>array('acl.user.view')),
        'removegroup'=>array('jacl2.rights.and'=>array('acl.user.view','acl.user.modify')),
        'addgroup'=>array('jacl2.rights.and'=>array('acl.user.view','acl.user.modify')),
    );

    /**
    *
    */
    function index() {
        $rep = $this->getResponse('html');

        $groups=array();
        $o = new StdClass;
        $o->id_aclgrp ='-2';
        $o->name=jLocale::get('jacl2db_admin~acl2.all.users.option');
        $o->grouptype=0;
        $groups[]=$o;
        $o = new StdClass;
        $o->id_aclgrp ='-1';
        $o->name=jLocale::get('jacl2db_admin~acl2.without.groups.option');
        $o->grouptype=0;
        $groups[]=$o;
        foreach(jAcl2DbUserGroup::getGroupList() as $grp) {
            $groups[]=$grp;
        }

        $listPageSize = 15;
        $offset = $this->intParam('idx',0,true);
        $grpid = $this->intParam('grpid',-2,true);

        $p = jAcl2Db::getProfile();

        if($grpid == -2) {
            //all users
            $dao = jDao::get('jelix~jacl2groupsofuser',$p);
            $cond = jDao::createConditions();
            $cond->addCondition('grouptype', '=', 2);
            $rs = $dao->findBy($cond,$offset,$listPageSize);
            $usersCount = $dao->countBy($cond);

        } elseif($grpid == -1) {
            $cnx = jDb::getConnection($p);
            //only those who have no groups
            if($cnx->dbms != 'pgsql') { 
                // with MYSQL 4.0.12, you must use an alias with the count to use it with HAVING 
                $sql = 'SELECT login, count(id_aclgrp) as nbgrp FROM jacl2_user_group
                        GROUP BY login HAVING nbgrp < 2 ORDER BY login';
            } else { 
                // But PgSQL doesn't support the HAVING structure with an alias. 
                $sql = 'SELECT login, count(id_aclgrp) as nbgrp FROM jacl2_user_group
                        GROUP BY login HAVING count(id_aclgrp) < 2 ORDER BY login';
            }

            $rs = $cnx->query($sql);
            $usersCount = $rs->rowCount();
        } else {
            //in a specific group
            $dao = jDao::get('jelix~jacl2usergroup',$p);
            $rs = $dao->getUsersGroupLimit($grpid, $offset, $listPageSize);
            $usersCount = $dao->getUsersGroupCount($grpid);
        }
        $users=array();
        $dao2 = jDao::get('jelix~jacl2groupsofuser',$p);
        foreach($rs as $u){
            $u->groups = array();
            $gl = $dao2->getGroupsUser($u->login);
            foreach($gl as $g) {
                if($g->grouptype != 2)
                    $u->groups[]=$g;
            }
            $users[] = $u;
        }

        $tpl = new jTpl();
        $tpl->assign(compact('offset','grpid','listPageSize','groups','users','usersCount'));
        $rep->body->assign('MAIN', $tpl->fetch('users_list'));
        $rep->body->assign('selectedMenuItem','usersrights');

        return $rep;
    }


    function rights(){
        $rep = $this->getResponse('html');

        $user = $this->param('user');
        if (!$user) {
            $rep->body->assign('MAIN', '<p>invalid user</p>');
            return $rep;
        }

        $hisgroup = null;
        $groupsuser = array();
        foreach(jAcl2DbUserGroup::getGroupList($user) as $grp) {
            if($grp->grouptype == 2)
                $hisgroup = $grp;
            else
                $groupsuser[$grp->id_aclgrp]=$grp;
        }

        $gid=array($hisgroup->id_aclgrp);
        $groups=array();
        $grouprights=array($hisgroup->id_aclgrp=>false);
        foreach(jAcl2DbUserGroup::getGroupList() as $grp) {
            $gid[]=$grp->id_aclgrp;
            $groups[]=$grp;
            $grouprights[$grp->id_aclgrp]=false;
        }

        $rights=array();
        $p = jAcl2Db::getProfile();
        $rs = jDao::get('jelix~jacl2subject',$p)->findAllSubject();
        foreach($rs as $rec){
            $rights[$rec->id_aclsbj] = $grouprights;
        }


        $rs = jDao::get('jelix~jacl2rights',$p)->getRightsByGroups($gid);
        foreach($rs as $rec){
            $rights[$rec->id_aclsbj][$rec->id_aclgrp] = true;
        }

        $tpl = new jTpl();
        $tpl->assign(compact('hisgroup', 'groupsuser', 'groups', 'rights','user'));
        $tpl->assign('nbgrp', count($groups));

        if(jAcl2::check('acl.user.modify')) {
            $rep->body->assign('MAIN', $tpl->fetch('user_rights'));
        }else{
            $rep->body->assign('MAIN', $tpl->fetch('user_rights_view'));
        }
        $rep->body->assign('selectedMenuItem','usersrights');
        return $rep;
    }

    function saverights(){
        $rep = $this->getResponse('redirect');
        $login = $this->param('user');
        $rights = $this->param('rights',array());

        if($login == '') {
            $rep->action = 'jacl2db_admin~users:index';
            return $rep;
        }

        $rep->action = 'jacl2db_admin~users:rights';
        $rep->params=array('user'=>$login);

        $dao = jDao::get('jelix~jacl2groupsofuser',jAcl2Db::getProfile());
        $grp = $dao->getPrivateGroup($login);

        jAcl2DbManager::setRightsOnGroup($grp->id_aclgrp, $rights);
        jMessage::add(jLocale::get('acl2.message.user.rights.ok'), 'ok');
        return $rep;
    }


    function removegroup(){
        $rep = $this->getResponse('redirect');

        $login = $this->param('user');
        if($login != '') {
            $rep->action = 'jacl2db_admin~users:rights';
            $rep->params=array('user'=>$login);
            jAcl2DbUserGroup::removeUserFromGroup($login, $this->param('grpid') );
        }else{
            $rep->action = 'jacl2db_admin~users:index';
        }

        return $rep;
    }

    function addgroup(){
        $rep = $this->getResponse('redirect');

        $login = $this->param('user');
        if($login != '') {
            $rep->action = 'jacl2db_admin~users:rights';
            $rep->params=array('user'=>$login);
            jAcl2DbUserGroup::addUserToGroup($login, $this->param('grpid') );
        }else{
            $rep->action = 'jacl2db_admin~users:index';
        }

        return $rep;
    }

}
