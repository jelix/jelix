<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Julien Issler, Olivier Demah, Adrien Lagroy de Croutte
 *
 * @copyright   2020 Adrien Lagroy de Croutte
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
        'groupsList'      => array('jacl2.right' => 'acl.group.view'),
        'view'      => array('jacl2.right' => 'acl.group.view'),
        'autocomplete'      => array('jacl2.right' => 'acl.group.view'),
        'rights'     => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.modify')),
        'saverights' => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.modify')),
        'rightres'     => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.modify')),
        'saverightres' => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.modify')),
        'newgroup'   => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.create')),
        'create'   => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.create')),
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
        // 'groups', 'rights', 'rightsProperties',
        // 'rightsGroupsLabels', 'rightsWithResources',
        $tpl->assign($data);
    }

    protected function getExceptionMessage(jAcl2DbAdminUIException $e, $category)
    {
        if ($e->getCode() == 1) {
            return jLocale::get('acl2.error.invalid.user');
        } elseif ($e->getCode() == 2) {
            return jLocale::get('acl2.message.'.$category.'.error.noacl.anybody');
        } elseif ($e->getCode() == 3) {
            return jLocale::get('acl2.message.'.$category.'.error.noacl.yourself');
        }
        return '';
    }

    protected function checkException(jAcl2DbAdminUIException $e, $category)
    {
        $msg = $this->getExceptionMessage($e, $category);
        if ($msg) {
            jMessage::add($msg);
        }
    }

    public function index()
    {
        $rep = $this->getResponse('html');
        $listPageSize = 15;
        $offset = $this->param('idx', 0, true);
        $filter = trim($this->param('filter'));
        $tpl = new jTpl();
        $tpl->assign(compact('offset', 'listPageSize', 'filter'));
        $rep->body->assign('MAIN', $tpl->fetch('groups_list'));
        $rep->body->assign('selectedMenuItem', 'usersrights');

        return $rep;
    }

    public function groupsList()
    {
        /** @var jResponseJson $rep */
        $rep = $this->getResponse('json');

        $stringToSearch = '';
        $draw = $this->intParam('draw');
        $offset = $this->intParam('start', 0, true);
        $length = $this->intParam('length', 20, true); // -1 == all

        $searchP = $this->param('search');
        if ($searchP && is_array($searchP) && (!isset($searchP['regexp']) || $searchP['regexp'] == 'false')) {
            $stringToSearch = $searchP['value'];
        }

        $orderData = $this->param('order');

        $data = array();
        $manager = new jAcl2DbAdminUIManager();

        $order = $manager::ORDER_BY_NAME;

        if (isset($orderData[0]['column'])) {
            if ($orderData[0]['column'] == 0) {
                $order = $manager::ORDER_BY_ID;
            }
            else if ($orderData[0]['column'] == 1) {
                $order = $manager::ORDER_BY_NAME;
            }
            else if ($orderData[0]['column'] == 2) {
                $order = $manager::ORDER_BY_USERS;
            }
            else if ($orderData[0]['column'] == 3) {
                $order = $manager::ORDER_BY_GROUPTYPE;
            }
        }
        if (isset($orderData[0]['dir'])) {
            if ($orderData[0]['dir'] == 'desc') {
                $order |= $manager::ORDER_DIRECTION_DESC;
            }
        }

        $allGroups = $manager->getGroupByFilter($stringToSearch, $offset, $length, $order);
        foreach($allGroups['results'] as $group)
        {
            $data[] = array(
                "DT_RowId" => 'grp-'.$group->id_aclgrp,
                "DT_RowData" => [
                    "id_aclgrp" => $group->id_aclgrp
                ],
                'id' => $group->id_aclgrp,
                'name' => $group->name,
                'nb_users' => $group->nb_users,
                'grouptype' => $group->grouptype,
                'links' => [
                    'rights' => jUrl::get('jacl2db_admin~groups:rights', array('group' => $group->id_aclgrp)),
                    'delete' => jUrl::get('jacl2db_admin~groups:delgroup', array('group_id' => $group->id_aclgrp))
                ]
            );
        }

        $rep->data = array(
            'draw' => $draw,
            'recordsTotal' => $manager->getGroupsCount(),
            'recordsFiltered' => $allGroups['resultsCount'],
            'data' => $data,
        );

        //$rep->data = array( 'draw' => $draw, 'error' => $error);
        return $rep;
    }

    public function rights()
    {
        $rep = $this->getResponse('html');
        $tpl = new jTpl();

        $this->loadGroupRights($tpl);
        $tpl->assign('groupId', $this->param('group'));
        $rep->body->assign('MAIN', $tpl->fetch('groups_right'));
        $rep->body->assign('selectedMenuItem', 'usersrights');
        $rep->title = jLocale::get('acl2.groups.rights.title');

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
        $rep->body->assign('selectedMenuItem', 'usersrights');

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

    public function setalldefault()
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

    public function setdefault()
    {
        $rep = $this->getResponse('json');
        $id = $this->param('id');
        $default = $this->param('isdefault') != '';
        if ($id != '' && $id != '__anonymous') {
            jAcl2DbUserGroup::setDefaultGroup($id, $default);
            $rep->data = [
                'result' => 'ok',
                'message' => jLocale::get('acl2.message.group.setdefault.ok')
            ];
        }
        else {
            $rep->data = [
                'result' => 'ok',
                'message' => ''
            ];
        }

        return $rep;
    }

    public function newgroup()
    {
        /** @var jResponseRedirect $rep */
        $rep = $this->getResponse('redirect');
        $rep->action = 'jacl2db_admin~groups:index';

        $name = $this->param('name');
        $id = $this->param('id');
        $copyGroup = $this->param('rights-copy');

        if ($name == '') {
            $rep->action = 'jacl2db_admin~groups:create';
            jMessage::add(jLocale::get('acl2.error.groupname.is.missing'), 'error');
            return $rep;
        }
        $id = trim($id);
        if ($id == '__anonymous') {
            $rep->action = 'jacl2db_admin~groups:create';
            $rep->params = array('name'=>$name);
            jMessage::add(jLocale::get('acl2.error.groupid.invalid'), 'error');
            return $rep;
        }
        if ($id == '') {
            $id = null;
        }
        if ($name != '') {
            $grpId = jAcl2DbUserGroup::createGroup($name, $id);
            if ($copyGroup) {
                $groupRights = jDao::get('jacl2db~jacl2rights')->getRightsByGroup($copyGroup);
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

        $tpl->assign('groupname', $this->param('name'));
        $tpl->assign('groups', jAcl2DbUserGroup::getGroupList()->fetchAll());
        $rep->body->assign('MAIN', $tpl->fetch('group_create'));

        return $rep;
    }

    public function changename()
    {
        $rep = $this->getResponse('json');
        $id = $this->param('id');
        $name = $this->param('name');
        if ($id != '' && $name != '' && $id != '__anonymous') {
            jAcl2DbUserGroup::updateGroup($id, $name);
            $rep->data = [
                'result' => 'ok',
                'message' => jLocale::get('acl2.message.group.rename.ok')
            ];
        }
        else {
            $rep->data = [
                'result' => 'ok',
                'message' => jLocale::get('acl2.message.group.rename.ok')
            ];
        }

        return $rep;
    }


    public function delgroup()
    {
        $rep = $this->getResponse('json');
        try {
            $manager = new jAcl2DbAdminUIManager();
            $manager->removeGroup($this->param('group_id'), jAuth::getUserSession()->login);

            $rep->data = [
                'result' => 'ok',
                'message' => jLocale::get('acl2.message.group.delete.ok')
            ];
        } catch (jAcl2DbAdminUIException $e) {
            $msg = $this->getExceptionMessage($e, 'group.delete');
            $rep->data = [
                'result' => 'error',
                'message' => $msg ?: $e->getMessage()
            ];
        }

        return $rep;
    }

    public function view()
    {
        $rep = $this->getResponse('html');
        $groupName = $this->param('group');

        if ($groupName === '__anonymous') {
            $group = jDao::get('jacl2db~jacl2group', 'jacl2_profile')->findAnonymousGroup();
            $group->name = jLocale::get('acl2.anonymous.group.name');
        } else {
            $group = jDao::get('jacl2db~jacl2group')->get($groupName);
        }
        if ($group === null) {
            $rep = $this->getResponse('redirect');
            $rep->action = 'jacl2db_admin~groups:index';
            jMessage::add(jLocale::get('acl2.group.unknown'), 'error');
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
        $hiddenRights = $manager->getHiddenRights();
        $subjects = array_filter($subjects, function ($elem) use ($hiddenRights) {
            return !in_array($elem, $hiddenRights);
        });
        $groupRights = array_filter(array_map(function ($elem) use ($groupRights) {
            if (in_array($elem->id_aclsbj, $groupRights)) {
                return jLocale::get($elem->label_key);
            }
        }, $subjects));

        if ($groupName === '__anonymous') {
            $users = null;
        }
        else {
            $users = array_map(function ($elem) {
                return $elem->login;
            }, jAcl2DbUserGroup::getUsersList($group->id_aclgrp)->fetchAll());
        }

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
        $filteredGroupObjects = $manager->getGroupByFilter($term, 0, 150, $manager::ORDER_BY_NAME, false);
        $rep->data = array_map(function ($elem) {
            return $elem->name;
        }, $filteredGroupObjects['results']);

        return $rep;
    }
}
