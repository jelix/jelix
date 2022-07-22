<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Julien Issler, Adrien Lagroy de Croutte
 *
 * @copyright   2020 Adrien Lagroy de Croutte
 * @copyright   2008-2022 Laurent Jouanneau
 * @copyright   2009 Julien Issler, 2020 Adrien Lagroy de Croutte
 *
 * @see        http://jelix.org
 * @licence     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
 */
class usersCtrl extends jController
{
    public $pluginParams = array(
        'index'       => array('jacl2.rights.and' => array('acl.user.view')),
        'usersList'       => array('jacl2.rights.and' => array('acl.user.view')),
        'rights'      => array('jacl2.rights.and' => array('acl.user.view')),
        'saverights'  => array('jacl2.rights.and' => array('acl.user.view', 'acl.user.modify')),
        'rightres'      => array('jacl2.rights.and' => array('acl.user.view')),
        'saverightres'  => array('jacl2.rights.and' => array('acl.user.view', 'acl.user.modify')),
        'removegroup' => array('jacl2.rights.and' => array('acl.user.view', 'acl.user.modify')),
        'addgroup'    => array('jacl2.rights.and' => array('acl.user.view', 'acl.user.modify')),
    );

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

    /**
     * Page to list all users
     * @return jResponseHtml
     * @throws jExceptionSelector
     */
    public function index()
    {
        $rep = $this->getResponse('html');

        $groups = array();

        $o = new StdClass();
        $o->id_aclgrp = '-2';
        $o->name = jLocale::get('jacl2db_admin~acl2.all.users.option');
        $o->grouptype = jAcl2DbUserGroup::GROUPTYPE_NORMAL;
        $groups[] = $o;

        $o = new StdClass();
        $o->id_aclgrp = '-1';
        $o->name = jLocale::get('jacl2db_admin~acl2.without.groups.option');
        $o->grouptype = jAcl2DbUserGroup::GROUPTYPE_NORMAL;
        $groups[] = $o;

        $groupList = jAcl2DbUserGroup::getGroupList();
        foreach ($groupList as $grp) {
            $groups[] = $grp;
        }

        $grpid = $this->param('grpid', jAcl2DbAdminUIManager::FILTER_GROUP_ALL_USERS, true);

        $tpl = new jTpl();
        $tpl->assign(compact('grpid', 'groups'));
        $rep->title = jLocale::get('acl2.users.title');
        $rep->body->assign('MAIN', $tpl->fetch('users_list'));
        $rep->body->assign('selectedMenuItem', 'usersrights');

        return $rep;
    }

    /**
     * list of users
     * @return jResponseJson
     */
    public function usersList()
    {
        /** @var jResponseJson $rep */
        $rep = $this->getResponse('json');

        $stringToSearch = '';
        $draw = $this->intParam('draw');
        $offset = $this->intParam('start', 0, true);
        $length = $this->intParam('length', 20, true); // -1 == all
        $grpid = $this->param('grpid', jAcl2DbAdminUIManager::FILTER_GROUP_ALL_USERS, true);

        $searchP = $this->param('search');
        if ($searchP && is_array($searchP) && (!isset($searchP['regexp']) || $searchP['regexp'] == 'false')) {
            $stringToSearch = $searchP['value'];
        }

        $orderData = $this->param('order');

        $manager = new jAcl2DbAdminUIManager();
        $order = $manager::ORDER_BY_NAME;

        if (isset($orderData[0]['column'])) {
            if ($orderData[0]['column'] == 0) {
                $order = $manager::ORDER_BY_NAME;
            }
        }
        if (isset($orderData[0]['dir'])) {
            if ($orderData[0]['dir'] == 'desc') {
                $order |= $manager::ORDER_DIRECTION_DESC;
            }
        }

        $data = array();

        if (is_numeric($grpid) && intval($grpid) < 0) {
            // users in all groups or not in groups
            $usersList = $manager->getUsersList($grpid, null, $stringToSearch, $offset, $length, $order);
        } else {
            // users in a specific group
            $usersList = $manager->getUsersList(jAcl2DbAdminUIManager::FILTER_BY_GROUP, $grpid, $stringToSearch, $offset, $length, $order);
        }

        foreach($usersList['results'] as $user)
        {
            $data[] = array(
                "DT_RowId" => 'usr-'.$user->login,
                "DT_RowData" => [
                    "login" => $user->login
                ],
                'login' => $user->login,
                'groups' => implode(', ', $user->groups),
                'links' => [
                    'rights' => jUrl::get('jacl2db_admin~users:rights', array('user' => $user->login))
                 ]
            );
        }

        $rep->data = array(
            'draw' => $draw,
            'recordsTotal' => $manager->getUsersCount($grpid),
            'recordsFiltered' => $usersList['resultsCount'],
            'data' => $data,
        );

        //$rep->data = array( 'draw' => $draw, 'error' => $error);
        return $rep;
    }

    protected function getLabel($id, $labelKey)
    {
        if ($labelKey) {
            try {
                return jLocale::get($labelKey);
            } catch (Exception $e) {
            }
        }

        return $id;
    }

    /**
     * Page showing rights of a user and his groups
     * @return jResponseHtml
     * @throws jExceptionSelector
     */
    public function rights()
    {
        $rep = $this->getResponse('html');

        $user = $this->param('user');
        if (!$user) {
            $rep->body->assign('MAIN', '<p>invalid user</p>');

            return $rep;
        }

        try {
            $manager = new jAcl2DbAdminUIManager();
            $data = $manager->getUserRights($user);
        } catch (jAcl2DbAdminUIException $e) {
            $rep->body->assign('MAIN', '<p>'.$e->getMessage().'</p>');

            return $rep;
        }

        $tpl = new jTpl();
        $tpl->assign($data);
        $tpl->assign('nbgrp', count($data['groupsuser']));

        if (jAcl2::check('acl.user.modify')) {
            $rep->body->assign('MAIN', $tpl->fetch('user_rights'));
        } else {
            $rep->body->assign('MAIN', $tpl->fetch('user_rights_view'));
        }
        $rep->body->assign('selectedMenuItem', 'rights');
        $rep->title = jLocale::get('acl2.user.rights.title').' '.$user;

        return $rep;
    }

    /**
     * Save rights
     *
     * @return jResponseRedirect
     * @throws jExceptionSelector
     */
    public function saverights()
    {
        $rep = $this->getResponse('redirect');
        $login = $this->param('user');
        $rights = $this->param('rights', array());

        if ($login == '') {
            $rep->action = 'jacl2db_admin~users:index';

            return $rep;
        }

        $rep->action = 'jacl2db_admin~users:rights';
        $rep->params = array('user' => $login);

        try {
            $manager = new jAcl2DbAdminUIManager();
            $manager->saveUserRights($login, $rights, jAuth::getUserSession()->login);
            jMessage::add(jLocale::get('acl2.message.user.rights.ok'), 'ok');
        } catch (jAcl2DbAdminUIException $e) {
            $this->checkException($e, 'saveuserrights');
        }

        return $rep;
    }

    /**
     * Show a page with the list of rights on resources for the user
     *
     * @return jResponseHtml
     * @throws Exception
     */
    public function rightres()
    {
        $rep = $this->getResponse('html');

        $user = $this->param('user');
        if (!$user) {
            $rep->body->assign('MAIN', '<p>invalid user</p>');

            return $rep;
        }

        $manager = new jAcl2DbAdminUIManager();
        $data = $manager->getUserRessourceRights($user);

        $tpl = new jTpl();
        $tpl->assign($data);

        if (jAcl2::check('acl.user.modify')) {
            $rep->body->assign('MAIN', $tpl->fetch('user_rights_res'));
        } else {
            $rep->body->assign('MAIN', $tpl->fetch('user_rights_res_view'));
        }
        $rep->body->assign('selectedMenuItem', 'usersrights');

        return $rep;
    }

    /**
     * Save rights on resources for the user
     * @return jResponseRedirect
     * @throws jExceptionSelector
     */
    public function saverightres()
    {
        $rep = $this->getResponse('redirect');
        $login = $this->param('user');
        $subjects = $this->param('subjects', array());

        if ($login == '') {
            $rep->action = 'jacl2db_admin~users:index';

            return $rep;
        }

        $rep->action = 'jacl2db_admin~users:rightres';
        $rep->params = array('user' => $login);

        $manager = new jAcl2DbAdminUIManager();
        $manager->removeUserRessourceRights($login, $subjects);

        jMessage::add(jLocale::get('acl2.message.user.rights.ok'), 'ok');

        return $rep;
    }

    /**
     * Remove a user from a group
     *
     * @return jResponseRedirect
     */
    public function removegroup()
    {
        $rep = $this->getResponse('redirect');

        $login = $this->param('user');
        if ($login != '') {
            $rep->action = 'jacl2db_admin~users:rights';
            $rep->params = array('user' => $login);

            try {
                $manager = new jAcl2DbAdminUIManager();
                $manager->removeUserFromGroup($login, $this->param('grpid'), jAuth::getUserSession()->login);
            } catch (jAcl2DbAdminUIException $e) {
                $this->checkException($e, 'removeuserfromgroup');
            }
        } else {
            $rep->action = 'jacl2db_admin~users:index';
        }

        return $rep;
    }

    /**
     * Add a user into a group
     *
     * @return jResponseRedirect
     */
    public function addgroup()
    {
        $rep = $this->getResponse('redirect');

        $login = $this->param('user');
        if ($login != '') {
            $rep->action = 'jacl2db_admin~users:rights';
            $rep->params = array('user' => $login);

            try {
                $manager = new jAcl2DbAdminUIManager();
                $manager->addUserToGroup($login, $this->param('grpid'), jAuth::getUserSession()->login);
            } catch (jAcl2DbAdminUIException $e) {
                $this->checkException($e, 'addusertogroup');
            }
        } else {
            $rep->action = 'jacl2db_admin~users:index';
        }

        return $rep;
    }
}
