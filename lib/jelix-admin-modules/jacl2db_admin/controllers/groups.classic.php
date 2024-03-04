<?php

/**
 * @package     jelix
 * @subpackage  jacl2db_admin
 *
 * @author      Laurent Jouanneau
 * @contributor Julien Issler, Olivier Demah, Adrien Lagroy de Croutte
 *
 * @copyright   2020 Adrien Lagroy de Croutte
 * @copyright   2008-2022 Laurent Jouanneau
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
        'autocomplete'      => array('jacl2.right' => 'acl.group.view'),
        'rights'     => array('jacl2.rights.and' => array('acl.group.view')),
        'saverights' => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.modify')),
        'rightres'     => array('jacl2.rights.and' => array('acl.group.view')),
        'saverightres' => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.modify')),
        'newgroup'   => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.create')),
        'create'   => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.create')),
        'changename' => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.modify')),
        'delgroup'   => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.delete')),
        'setdefault' => array('jacl2.rights.and' => array('acl.group.view', 'acl.group.modify')),
    );

    protected function getExceptionMessage(jAcl2DbAdminUIException $e, $category)
    {
        if ($e->getCode() == 1) {
            return jLocale::get('acl2.error.invalid.user');
        } elseif ($e->getCode() == 2) {
            return jLocale::get('acl2.message.' . $category . '.error.noacl.anybody');
        } elseif ($e->getCode() == 3) {
            return jLocale::get('acl2.message.' . $category . '.error.noacl.yourself');
        }
        return '';
    }

    protected function checkException(jAcl2DbAdminUIException $e, $category)
    {
        $msg = $this->getExceptionMessage($e, $category);
        if ($msg) {
            jMessage::add($msg, 'error');
        }
    }

    /**
     * Page to list all groups
     * @return jResponseJson
     * @throws Exception
     */
    public function index()
    {
        $rep = $this->getResponse('html');
        $listPageSize = 15;
        $offset = $this->param('idx', 0, true);
        $filter = trim($this->param('filter', '', true));
        $tpl = new jTpl();
        $tpl->assign(compact('offset', 'listPageSize', 'filter'));
        $rep->body->assign('MAIN', $tpl->fetch('groups_list'));
        $rep->body->assign('selectedMenuItem', 'rights');

        return $rep;
    }

    /**
     * list of groups
     *
     * @return jResponseJson
     */
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
            } else if ($orderData[0]['column'] == 1) {
                $order = $manager::ORDER_BY_NAME;
            } else if ($orderData[0]['column'] == 2) {
                $order = $manager::ORDER_BY_USERS;
            } else if ($orderData[0]['column'] == 3) {
                $order = $manager::ORDER_BY_GROUPTYPE;
            }
        }
        if (isset($orderData[0]['dir'])) {
            if ($orderData[0]['dir'] == 'desc') {
                $order |= $manager::ORDER_DIRECTION_DESC;
            }
        }

        $allGroups = $manager->getGroupByFilter($stringToSearch, $offset, $length, $order);
        foreach ($allGroups['results'] as $group) {
            $data[] = array(
                "DT_RowId" => 'grp-' . $group->id_aclgrp,
                "DT_RowData" => [
                    "id_aclgrp" => $group->id_aclgrp
                ],
                'id' => $group->id_aclgrp,
                'name' => $group->id_aclgrp != '__anonymous' ? $group->name : jLocale::get('jacl2db_admin~acl2.anonymous.group.name'),
                'nb_users' => $group->nb_users,
                'grouptype' => $group->grouptype,
                'links' => [
                    'rights' => jUrl::get('jacl2db_admin~groups:rights', array('group' => $group->id_aclgrp)),
                    'delete' => ($group->id_aclgrp != '__anonymous' ? jUrl::get('jacl2db_admin~groups:delgroup', array('group' => $group->id_aclgrp)) : '')
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

    /**
     * Page to see rights of all groups
     *
     * @return jResponseHtml
     * @throws jException
     * @throws jExceptionSelector
     */
    public function allrights()
    {
        $rep = $this->getResponse('html');

        $tpl = new jTpl();
        $manager = new jAcl2DbAdminUIManager();

        $data = $manager->getGroupRights();
        $tpl->assign($data);
        $tpl->assign('nbgrp', count($data['groups']));

        $rep->body->assign('MAIN', $tpl->fetch('groups_right'));
        $rep->body->assign('selectedMenuItem', 'rights');
        $rep->title = jLocale::get('acl2.groups.rights.title');

        return $rep;
    }

    /**
     * Page to change rights of a group
     *
     * @return jResponseHtml
     * @throws jException
     * @throws jExceptionSelector
     */
    public function rights()
    {
        $grpId = $this->param('group');
        if (!$grpId) {
            return $this->redirect('groups:allrights');
        }

        $rep = $this->getResponse('html');

        $tpl = new jTpl();
        $tpl->assign('groupId', $grpId);

        $manager = new jAcl2DbAdminUIManager();

        $data = $manager->getRightsOfGroup($grpId);
        $tpl->assign($data);
        $daoGroup = jDao::get('jacl2db~jacl2group', 'jacl2_profile');
        $tpl->assign('group', $daoGroup->get($grpId));
        if (jAcl2::check('acl.group.modify')) {
            $tplName = 'group_right';
        } else {
            $tplName = 'group_right_view';
        }

        $rep->body->assign('MAIN', $tpl->fetch($tplName));
        $rep->body->assign('selectedMenuItem', 'rights');
        $rep->title = jLocale::get('acl2.groups.rights.title');

        return $rep;
    }

    /**
     * save rights of a group.
     *
     * @return jResponseRedirect
     */
    public function saverights()
    {
        $rights = $this->param('rights', array());
        $grpId = $this->param('group');
        if (!$grpId) {
            return $this->redirect('jacl2db_admin~groups:index');
        }

        try {
            $login = jAcl2Authentication::getAdapter()->getCurrentUserLogin();
            if ($login === null) {
                throw new jAcl2DbAdminUIException("No authorized user", 1);
            }
            $manager = new jAcl2DbAdminUIManager();
            $manager->setRightsOnGroup($rights, $grpId, $login);
            jMessage::add(jLocale::get('acl2.message.group.rights.ok'), 'ok');
        } catch (jAcl2DbAdminUIException $e) {
            $this->checkException($e, 'savegrouprights');
        }
        return $this->redirect(
            'jacl2db_admin~groups:rights',
            array('group' => $this->param('group'))
        );
    }

    /**
     * Show rights on ressources of a group
     *
     * @return jResponseHtml
     * @throws jException
     * @throws jExceptionSelector
     */
    public function rightres()
    {
        $rep = $this->getResponse('html');

        $groupid = $this->param('group', null);

        if ($groupid === null || $groupid == '') {
            jMessage::add('Invalid Group', 'error');

            return $this->redirect('jacl2db_admin~groups:index');
        }

        $daogroup = jDao::get('jacl2db~jacl2group', 'jacl2_profile');
        if ($groupid != '__anonymous') {
            $group = $daogroup->get($groupid);
            if (!$group) {
                jMessage::add('Invalid Group', 'error');

                return $this->redirect('jacl2db_admin~groups:index');
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
        $rep->body->assign('selectedMenuItem', 'rights');

        return $rep;
    }

    /**
     * save rights on ressources of a group.
     *
     * @return jResponseRedirect
     */
    public function saverightres()
    {

        $subjects = $this->param('subjects', array());

        $groupid = $this->param('group', null);
        if ($groupid === null || $groupid == '') {

            return $this->redirect('jacl2db_admin~groups:allrights');
        }

        $daogroup = jDao::get('jacl2db~jacl2group', 'jacl2_profile');
        if ($groupid != '__anonymous') {
            $group = $daogroup->get($groupid);
            if (!$group) {

                return $this->redirect('jacl2db_admin~groups:allrights');
            }
        }

        $manager = new jAcl2DbAdminUIManager();
        $manager->removeGroupRightsWithResources($groupid, $subjects);

        jMessage::add(jLocale::get('jacl2db_admin~acl2.message.group.rights.ok'), 'ok');

        return $this->redirect('jacl2db_admin~groups:rightres', array('group' => $groupid));
    }

    /**
     * Set or unset a group as a default group
     * @return jResponseJson
     * @throws jExceptionSelector
     */
    public function setdefault()
    {
        $rep = $this->getResponse('json');
        $id = $this->param('group');
        $default = $this->param('isdefault') != '';
        if ($id != '' && $id != '__anonymous') {
            jAcl2DbUserGroup::setDefaultGroup($id, $default);
            $rep->data = [
                'result' => 'ok',
                'message' => jLocale::get('acl2.message.group.setdefault.ok')
            ];
        } else {
            $rep->data = [
                'result' => 'ok',
                'message' => ''
            ];
        }

        return $rep;
    }

    /**
     * Create a group
     * @return jResponseRedirect
     * @throws jExceptionSelector
     */
    public function newgroup()
    {
        $name = $this->param('name');
        $id = $this->param('id');
        $copyGroup = $this->param('rights-copy');

        if ($name == '') {
            jMessage::add(jLocale::get('acl2.error.groupname.is.missing'), 'error');
            return $this->redirect('jacl2db_admin~groups:create');
        }

        $id = trim($id);
        if ($id == '__anonymous') {
            jMessage::add(jLocale::get('acl2.error.groupid.invalid'), 'error');
            return $this->redirect('jacl2db_admin~groups:create', array('name' => $name));
        }

        if ($id == '') {
            $id = null;
        }

        if ($name == '') {
            return $this->redirect('jacl2db_admin~groups:index');
        }

        $grpId = jAcl2DbUserGroup::createGroup($name, $id);
        if ($copyGroup) {
            $groupRights = jDao::get('jacl2db~jacl2rights')->getRightsByGroup($copyGroup);
            $rights = array();
            foreach ($groupRights as $groupRight) {
                $rights[$groupRight->id_aclsbj] = $groupRight->canceled ? 'n' : 'y';
            }
            jAcl2DbManager::setRightsOnGroup($grpId, $rights);
        }
        jMessage::add(jLocale::get('acl2.message.group.create.ok'), 'ok');
        return $this->redirect('jacl2db_admin~groups:rights', array('group' => $grpId));
    }

    /**
     * Form to create group
     *
     * @return jResponseHtml
     * @throws Exception
     */
    public function create()
    {
        $rep = $this->getResponse('html');
        $tpl = new jTpl();

        $tpl->assign('groupname', $this->param('name'));
        $tpl->assign('groups', jAcl2DbUserGroup::getGroupList()->fetchAll());
        $rep->body->assign('MAIN', $tpl->fetch('group_create'));

        return $rep;
    }

    /**
     * Change the name of a group
     * @return jResponseJson
     * @throws jExceptionSelector
     */
    public function changename()
    {
        $rep = $this->getResponse('json');
        $id = $this->param('group');
        $name = $this->param('name');
        if ($id != '' && $name != '' && $id != '__anonymous') {
            jAcl2DbUserGroup::updateGroup($id, $name);
            $rep->data = [
                'result' => 'ok',
                'message' => jLocale::get('acl2.message.group.rename.ok')
            ];
        } else {
            $rep->data = [
                'result' => 'ok',
                'message' => jLocale::get('acl2.message.group.rename.ok')
            ];
        }

        return $rep;
    }

    /**
     * delete a group
     * @return jResponseJson
     * @throws jExceptionSelector
     */
    public function delgroup()
    {
        $rep = $this->getResponse('json');
        try {
            $login = jAcl2Authentication::getAdapter()->getCurrentUserLogin();
            if ($login === null) {
                throw new jAcl2DbAdminUIException("No authorized user", 1);
            }
            $manager = new jAcl2DbAdminUIManager();
            $manager->removeGroup($this->param('group'), $login);

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
