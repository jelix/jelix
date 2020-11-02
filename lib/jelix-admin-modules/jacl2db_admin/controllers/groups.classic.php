<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Julien Issler, Olivier Demah
 *
 * @copyright   2008-2017 Laurent Jouanneau
 * @copyright   2009 Julien Issler
 * @copyright   2010 Olivier Demah
 *
 * @see        http://jelix.org
 * @licence     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
 */
class groupsCtrl extends jController
{
    public $pluginParams = array(
        'index'      => array('jacl2.right' => 'acl.group.view'),
        'rights'     => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.modify')),
        'saverights' => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.modify')),
        'newgroup'   => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.create')),
        'changename' => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.modify')),
        'delgroup'   => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.delete')),
        'setdefault' => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.modify')),
    );

    /**
     * @param jTpl $tpl
     */
    protected function loadGroupRights($tpl)
    {
        /** @var jAcl2DbAdminUIManager $manager */
        $manager = new jAcl2DbAdminUIManager();
        $data = $manager->getGroupRights();
        $tpl->assign('nbgrp', count($data['groups']));
        $tpl->assign($data);
    }

    protected function checkException(jAcl2DbAdminUIException $e, $category)
    {
        if ($e->getCode() == 1) {
            jMessage::add(jLocale::get('acl2.error.invalid.user'), 'error');
        } elseif ($e->getCode() == 2) {
            jMessage::add(jLocale::get('acl2.message.'.$category.'.error.noacl.anybody'), 'error');
        } elseif ($e->getCode() == 3) {
            jMessage::add(jLocale::get('acl2.message.'.$category.'.error.noacl.yourself'), 'error');
        }
    }

    public function index()
    {
        $rep = $this->getResponse('html');
        $tpl = new jTpl();

        if (jAcl2::check('acl.group.modify')) {
            $tpl->assign('groups', array_merge(jAcl2DbUserGroup::getGroupList()->fetchAll(), array(jDao::get('jacl2db~jacl2group', 'jacl2_profile')->findAnonymousGroup())));
            $rep->body->assign('MAIN', $tpl->fetch('groups_edit'));
        } else {
            $this->loadGroupRights($tpl);
            $rep->body->assign('MAIN', $tpl->fetch('groups_right_view'));
        }
        $rep->body->assign('selectedMenuItem', 'usersgroups');

        return $rep;
    }

    public function rights()
    {
        $rep = $this->getResponse('html');
        $tpl = new jTpl();

        $this->loadGroupRights($tpl);
        $tpl->assign('groupId', $this->param('group'));
        $rep->body->assign('MAIN', $tpl->fetch('groups_right'));
        $rep->body->assign('selectedMenuItem', 'usersgroups');

        return $rep;
    }

    /**
     * save rights of all groups.
     *
     * @return jResponse
     */
    public function saverights()
    {
        $rep = $this->getResponse('redirect');
        $rights = $this->param('rights', array());

        try {
            jLog::dump($rights, '', 'error');
            $manager = new jAcl2DbAdminUIManager();
            $manager->saveGroupRights($rights, jAuth::getUserSession()->login);
            jMessage::add(jLocale::get('acl2.message.group.rights.ok'), 'ok');
        } catch (jAcl2DbAdminUIException $e) {
            $this->checkException($e, 'savegrouprights');
        }
        $rep->action = 'jacl2db_admin~groups:rights';
        $rep->params = array('group' => $this->param('group'));
        return $rep;
    }

    public function rightres()
    {
        $rep = $this->getResponse('html');

        $groupid = $this->param('group', null);

        if ($groupid === null || $groupid == '') {
            $rep = $this->getResponse('redirect');
            $rep->action = 'jacldb_admin~groups:index';
            jMessage::add('Invalid Group', 'error');

            return $rep;
        }

        $daogroup = jDao::get('jacl2db~jacl2group', 'jacl2_profile');
        if ($groupid != '__anonymous') {
            $group = $daogroup->get($groupid);
            if (!$group) {
                $rep = $this->getResponse('redirect');
                $rep->action = 'jacldb_admin~groups:index';
                jMessage::add('Invalid Group', 'error');

                return $rep;
            }
            $groupname = $group->name;
        } else {
            $groupname = jLocale::get('jacl2db_admin~acl2.anonymous.group.name');
        }

        $manager = new jAcl2DbAdminUIManager();
        $data = $manager->getGroupRightsWithResources($groupid);

        $tpl = new jTpl();
        $tpl->assign($data);
        $tpl->assign(compact('groupid', 'groupname'));

        if (jAcl2::check('acl.group.modify')) {
            $rep->body->assign('MAIN', $tpl->fetch('group_rights_res'));
        } else {
            $rep->body->assign('MAIN', $tpl->fetch('group_rights_res_view'));
        }
        $rep->body->assign('selectedMenuItem', 'usersgroups');

        return $rep;
    }

    public function saverightres()
    {
        $rep = $this->getResponse('redirect');
        $rep->action = 'jacl2db_admin~groups:rightres';

        $subjects = $this->param('subjects', array());

        $groupid = $this->param('group', null);
        if ($groupid === null || $groupid == '') {
            $rep->action = 'jacl2db_admin~groups:rights';

            return $rep;
        }

        $daogroup = jDao::get('jacl2db~jacl2group', 'jacl2_profile');
        if ($groupid != '__anonymous') {
            $group = $daogroup->get($groupid);
            if (!$group) {
                $rep->action = 'jacl2db_admin~groups:rights';

                return $rep;
            }
        }

        $rep->params = array('group' => $groupid);
        $manager = new jAcl2DbAdminUIManager();
        $manager->removeGroupRightsWithResources($groupid, $subjects);

        jMessage::add(jLocale::get('jacl2db_admin~acl2.message.group.rights.ok'), 'ok');

        return $rep;
    }

    public function setdefault()
    {
        $rep = $this->getResponse('redirect');
        $groups = $this->param('groups', array());

        foreach (jAcl2DbUserGroup::getGroupList() as $grp) {
            $default = in_array($grp->id_aclgrp, $groups);
            jAcl2DbUserGroup::setDefaultGroup($grp->id_aclgrp, $default);
        }
        jMessage::add(jLocale::get('acl2.message.groups.setdefault.ok'), 'ok');

        $rep->action = 'jacl2db_admin~groups:index';

        return $rep;
    }

    public function newgroup()
    {
        $rep = $this->getResponse('redirect');
        $rep->action = 'jacl2db_admin~groups:rights';

        $name = $this->param('name');
        $id = $this->param('id');
        $copyGroup = $this->param('rights-copy');


        if (trim($id) == '') {
            $id = null;
        }
        if ($name != '') {
            $grpId = jAcl2DbUserGroup::createGroup($name, $id);
            if ($copyGroup) {
                $groupRights = jDao::get('jacl2db~jacl2rights')->getRightsByGroup($copyGroup)->fetchAll();
                $rights = array();
                foreach($groupRights as $groupRight) {
                    $rights[$groupRight->id_aclsbj] = $groupRight->canceled ? 'n' : 'y';
                }
                jAcl2DbManager::setRightsOnGroup($grpId, $rights);
            }
            jMessage::add(jLocale::get('acl2.message.group.create.ok'), 'ok');
            $rep->params = array('group' => $grpId);
        }

        return $rep;
    }

    public function create()
    {
        $rep = $this->getResponse('html');
        $tpl = new jTpl();

        $tpl->assign('groups', jAcl2DbUserGroup::getGroupList()->fetchAll());
        $rep->body->assign('MAIN', $tpl->fetch('group_create'));

        return $rep;
    }

    public function changename()
    {
        $rep = $this->getResponse('redirect');
        $rep->action = 'jacl2db_admin~groups:index';

        $id = $this->param('group_id');
        $name = $this->param('newname');
        if ($id != '' && $name != '') {
            jAcl2DbUserGroup::updateGroup($id, $name);
            jMessage::add(jLocale::get('acl2.message.group.rename.ok'), 'ok');
        }

        return $rep;
    }

    public function delgroup()
    {
        $rep = $this->getResponse('redirect');
        $rep->action = 'jacl2db_admin~groups:index';

        try {
            $manager = new jAcl2DbAdminUIManager();
            $manager->removeGroup($this->param('group'));
            jMessage::add(jLocale::get('acl2.message.group.delete.ok'), 'ok');
        } catch (jAcl2DbAdminUIException $e) {
            $this->checkException($e, 'group.delete');
        }

        return $rep;
    }

    public function view()
    {
        $rep = $this->getResponse('html');


        if ($this->param('group') === 'anonymous') {
            $group = jDao::get('jacl2db~jacl2group', 'jacl2_profile')->findAnonymousGroup();
            $group->name = jLocale::get('acl2.anonymous.group.name');
        } else {
            $group = jDao::get('jacl2db~jacl2group')->getGroupByName($this->param('group'));
        }
        if ($group === null) {
            $rep = $this->getResponse('redirect');
            $rep->action = 'jacl2db_admin~groups:index';
            jMessage::add('Invalid Group', 'error');

            return $rep;
        }
        $manager = new jAcl2DbAdminUIManager();
        $rights = $manager->getGroupRights()['rights'];

        $groupRights = array_keys(array_filter($rights, function ($elem) use ($group) {
            if ($elem[$group->id_aclgrp] === 'y') {
                return true;
            }
        }));
        $subjects = jDao::get('jacl2db~jacl2subject')->findAllSubject()->fetchAll();
        $groupRights = array_filter(array_map(function ($elem) use ($groupRights) {
            if (in_array($elem->id_aclsbj, $groupRights)) {
                return jLocale::get($elem->label_key);
            }
        }, $subjects));

        $users = array_map(function ($elem) {
            return $elem->login;
        }, jAcl2DbUserGroup::getUsersList($group->id_aclgrp)->fetchAll());

        $tpl = new jTpl();
        $tpl->assign(array('group' => $group, 'rights' => $groupRights, 'users' => $users));
        $rep->body->assign('MAIN', $tpl->fetch('group_view'));

        return $rep;
    }

    public function autocomplete()
    {
        $rep = $this->getResponse('json');
        $term = $this->param('term', '');

        if (strlen($term) < 2) {
            $rep->data = array();

            return $rep;
        }
        $manager = new jAcl2DbAdminUIManager();
        $filteredGroupObjects = $manager->getGroupByFilter($term);
        $rep->data = array_map(function ($elem) {
            return $elem->name;
        }, $filteredGroupObjects['results']);

        return $rep;
    }
}
