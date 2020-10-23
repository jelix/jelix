<?php
/**
 * @author      Adrien Lagroy de Croutte
 *
 * @copyright   2020 Adrien Lagroy de Croutte
 *
 * @see        http://jelix.org
 * @licence     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
 */


class rightsCtrl extends jController
{
    public $pluginParams = array(
        'index'      => array('jacl2.right' => 'acl.group.view'),
        'rights'     => array('jacl2.right' => 'acl.group.view'),
    );

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

        foreach (jAcl2DbUserGroup::getGroupList() as $grp) {
            $groups[] = $grp;
        }

        $manager = new jAcl2DbAdminUIManager();
        $listPageSize = 15;

        $type = $this->param('typeName', 'group');
        $offset = $this->param('idx', 0, true);
        $grpid = $this->param('grpid', jAcl2DbAdminUIManager::FILTER_GROUP_ALL_USERS, true);
        $filter = trim($this->param('filter'));
        $tpl = new jTpl();

        if ($type === 'user' && is_numeric($grpid) && intval($grpid) < 0) {
            $tpl->assign($manager->getUsersList($grpid, null, $filter, $offset, $listPageSize));
        } elseif ($type === 'user') {
            $tpl->assign($manager->getUsersList(jAcl2DbAdminUIManager::FILTER_BY_GROUP, $grpid, $filter, $offset, $listPageSize));
        } elseif ($type === 'group') {
            $tpl->assign($manager->getGroupByFilter($filter));
        } elseif ($type === 'all') {
            $usersResults = $manager->getUsersList($grpid, null, $filter, $offset, $listPageSize);
            $groupResults = $manager->getGroupByFilter($filter);
            $results = array(
                'results'      => array_merge($usersResults['results'], $groupResults['results']),
                'resultsCount' => $usersResults['resultsCount'] + $groupResults['resultsCounts'],
            );
            $tpl->assign($results);
        }

        $tpl->assign(compact('offset', 'grpid', 'listPageSize', 'groups', 'filter', 'type'));
        $rep->body->assign('MAIN', $tpl->fetch('users_list'));
        $rep->body->assign('selectedMenuItem', 'usersrights');

        return $rep;
    }

    public function rights()
    {
        $rep = $this->getResponse('redirect');
        $type = $this->param('type').'s';
        if ($type === 's') {
            $rep = $this->getResponse('redirect');
            $rep->action = 'jacl2db_admin~rights:index';
            jMessage::add('Invalid Entry, select an entry from the autocomplete list.', 'error');

            return $rep;
        }
        $name = $this->param('name');
        $group = null;
        if ($type == 'groups') {
            $group = jDao::get('jacl2db~jacl2group')->getGroupByName($name)->id_aclgrp;
        }
        $rep->params = array(
            'user'  => $name,
            'group' => $group,
        );
        $rep->action = 'jacl2db_admin~'.$type.':rights';

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

        $results = array();
        $manager = new jAcl2DbAdminUIManager();
        $usersResults = $manager->getUsersList(jAcl2DbAdminUIManager::FILTER_GROUP_ALL_USERS, null, $term);
        $groupResults = $manager->getGroupByFilter($term);
        $resultsObjects = array_merge($usersResults['results'], $groupResults['results']);
        foreach ($resultsObjects as $result) {
            $results[] = array(
                'label' => $result->login.' ('.jLocale::get('jacl2db_admin~acl2.type.'.$result->type).')',
                'value' => array(
                    'login' => $result->login,
                    'type'  => $result->type,
                ),
            );
        }

        $rep->data = $results;

        return $rep;
    }
}
