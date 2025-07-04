<?php
/**
 * @package     jelix
 * @subpackage  jacl2db
 *
 * @author      Laurent Jouanneau
 * @contributor Julien Issler, Vincent Viaud
 *
 * @copyright   2006-2020 Laurent Jouanneau
 * @copyright   2009 Julien Issler
 * @copyright   2011 Vincent Viaud
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 *
 * @since 1.1
 */

/**
 * Use this class to register or unregister users in the acl system, and to manage user groups.
 *  Works only with db driver of jAcl2.
 *
 * @static
 */
class jAcl2DbUserGroup
{
    /**
     * Group type in the grouptype field.
     */
    const GROUPTYPE_NORMAL = 0;

    /**
     * Group type in the grouptype field.
     * Indicates that the group is the default one for new users.
     */
    const GROUPTYPE_DEFAULT = 1;

    /**
     * Group type in the grouptype field.
     * Indicates that the group belongs to a unique User.
     */
    const GROUPTYPE_PRIVATE = 2;

    /**
     * @internal The constructor is private, because all methods are static
     */
    private function __construct()
    {
    }

    /**
     * Indicates if the current user is a member of the given user group.
     *
     * @param string $groupid The id of a group
     *
     * @return bool true if it's ok
     */
    public static function isMemberOfGroup($groupid)
    {
        return in_array($groupid, self::getGroups());
    }

    /**
     * @var null|string[] list of groups of the current user
     */
    protected static $groups;

    /**
     * Retrieve the list of group the current user is member of.
     *
     * @return array list of group id
     */
    public static function getGroups()
    {
        $login = jAcl2Authentication::getAdapter()->getCurrentUserLogin();
        if ($login === null) {
            self::$groups = null;

            return array();
        }

        // load groups
        if (self::$groups === null) {
            $gp = jDao::get('jacl2db~jacl2usergroup', 'jacl2_profile')
                ->getGroupsUser($login)
            ;
            self::$groups = array();
            foreach ($gp as $g) {
                self::$groups[] = $g->id_aclgrp;
            }
        }

        return self::$groups;
    }

    /**
     * Retrieve the list of group the given user is member of.
     *
     * @param string $login The user's login
     *
     * @return array list of group id
     *
     * @since 1.6.29
     */
    public static function getGroupsIdByUser($login)
    {
        if ($login === '' || $login === null) {
            return array();
        }

        $gp = jDao::get('jacl2db~jacl2usergroup', 'jacl2_profile')
            ->getGroupsUser($login)
        ;
        $groups = array();
        foreach ($gp as $g) {
            $groups[] = $g->id_aclgrp;
        }

        return $groups;
    }

    /**
     * Get the private group for the current user or for the given login.
     *
     * @param string $login The user's login
     *
     * @return string the id of the private group
     *
     * @since 1.2
     */
    public static function getPrivateGroup($login = null)
    {
        if (!$login) {
            $login = jAcl2Authentication::getAdapter()->getCurrentUserLogin();
            if ($login === null) {
                return null;
            }
        }
        $privateGroup = jDao::get('jacl2db~jacl2group', 'jacl2_profile')->getPrivateGroup($login);
        if (!$privateGroup) {
            return null;
        }

        return $privateGroup->id_aclgrp;
    }

    /**
     * Get a group.
     *
     * @param string $code The code
     *
     * @return false|\Jelix\Dao\AbstractDaoRecord the dao object r false if it doesn't exist
     *
     * @since 1.2
     */
    public static function getGroup($code)
    {
        return jDao::get('jacl2db~jacl2group', 'jacl2_profile')->get($code);
    }

    /**
     * get the list of the users of a group.
     *
     * @param string $groupid id of the user group
     *
     * @return object[] a list of users object (dao records)
     */
    public static function getUsersList($groupid)
    {
        return jDao::get('jacl2db~jacl2usergroup', 'jacl2_profile')->getUsersGroup($groupid);
    }

    /**
     * register a user in the acl system.
     *
     * For example, this method is called by the acl module when responding
     * to the event generated by the auth module when a user is created.
     * When a user is registered, a private group is created.
     *
     * @param string $login        the user login
     * @param bool   $defaultGroup if true, the user become the member of default groups
     */
    public static function createUser($login, $defaultGroup = true)
    {
        $daousergroup = jDao::get('jacl2db~jacl2usergroup', 'jacl2_profile');
        $daogroup = jDao::get('jacl2db~jacl2group', 'jacl2_profile');
        $usergrp = jDao::createRecord('jacl2db~jacl2usergroup', 'jacl2_profile');
        $usergrp->login = $login;

        // if $defaultGroup -> assign the user to default groups
        if ($defaultGroup) {
            $defgrp = $daogroup->getDefaultGroups();
            foreach ($defgrp as $group) {
                if ($daousergroup->get($login, $group->id_aclgrp)) {
                    continue;
                }
                $usergrp->id_aclgrp = $group->id_aclgrp;
                $daousergroup->insert($usergrp);
            }
        }

        // create a private group
        if ($daogroup->get('__priv_'.$login)) {
            return;
        }
        $persgrp = jDao::createRecord('jacl2db~jacl2group', 'jacl2_profile');
        $persgrp->id_aclgrp = '__priv_'.$login;
        $persgrp->name = $login;
        $persgrp->grouptype = self::GROUPTYPE_PRIVATE;
        $persgrp->ownerlogin = $login;

        $daogroup->insert($persgrp);
        $usergrp->id_aclgrp = $persgrp->id_aclgrp;
        $daousergroup->insert($usergrp);
    }

    /**
     * Add a user into a group.
     *
     * (a user can be a member of several groups)
     *
     * @param string $login   the user login
     * @param string $groupid the group id
     */
    public static function addUserToGroup($login, $groupid)
    {
        if ($groupid == '__anonymous') {
            throw new Exception('jAcl2DbUserGroup::addUserToGroup : invalid group id');
        }
        $dao = jDao::get('jacl2db~jacl2usergroup', 'jacl2_profile');
        if ($dao->get($login, $groupid)) {
            return;
        }
        $usergrp = jDao::createRecord('jacl2db~jacl2usergroup', 'jacl2_profile');
        $usergrp->login = $login;
        $usergrp->id_aclgrp = $groupid;
        $dao->insert($usergrp);
    }

    /**
     * Remove a user from a group.
     *
     * @param string $login   the user login
     * @param string $groupid the group id
     */
    public static function removeUserFromGroup($login, $groupid)
    {
        jDao::get('jacl2db~jacl2usergroup', 'jacl2_profile')->delete($login, $groupid);
    }

    /**
     * Unregister a user in the acl system.
     *
     * @param string $login the user login
     */
    public static function removeUser($login)
    {
        $daogroup = jDao::get('jacl2db~jacl2group', 'jacl2_profile');

        // get the private group
        $privategrp = $daogroup->getPrivateGroup($login);
        if ($privategrp) {
            // delete the rights on the private group (jacl2_rights)
            jDao::get('jacl2db~jacl2rights', 'jacl2_profile')->deleteByGroup($privategrp->id_aclgrp);
        }

        // remove from all the groups (jacl2_users_group)
        jDao::get('jacl2db~jacl2usergroup', 'jacl2_profile')->deleteByUser($login);

        if ($privategrp) {
            // remove the user's personal group (jacl2_group)
            $daogroup->delete($privategrp->id_aclgrp);
        }
    }

    /**
     * Create a new group.
     *
     * @param string $name      its name
     * @param string $id_aclgrp its id
     *
     * @return string the id of the new group
     */
    public static function createGroup($name, $id_aclgrp = null)
    {
        if ($id_aclgrp === null) {
            $id_aclgrp = $name;
        }
        $id_aclgrp = str_replace(' ', '_', $id_aclgrp);
        $id_aclgrp = preg_replace('/[^a-zA-Z0-9_\\-]/', '', $id_aclgrp);

        $dao = jDao::get('jacl2db~jacl2group', 'jacl2_profile');
        $group = $dao->get($id_aclgrp);
        if (!$group) {
            $group = jDao::createRecord('jacl2db~jacl2group', 'jacl2_profile');
            $group->id_aclgrp = $id_aclgrp;
            $group->name = $name;
            $group->grouptype = self::GROUPTYPE_NORMAL;
            $dao->insert($group);
        }

        return $group->id_aclgrp;
    }

    /**
     * Set a group to be default (or not).
     *
     * there can have several default group. A default group is a group
     * where a user is assigned to during its registration
     *
     * @param string $groupid the group id
     * @param bool   $default true if the group is to be default, else false
     */
    public static function setDefaultGroup($groupid, $default = true)
    {
        if ($groupid == '__anonymous') {
            throw new Exception('jAcl2DbUserGroup::setDefaultGroup : invalid group id');
        }

        $daogroup = jDao::get('jacl2db~jacl2group', 'jacl2_profile');
        if ($default) {
            $daogroup->setToDefault($groupid);
        } else {
            $daogroup->setToNormal($groupid);
        }
    }

    /**
     * Change the name of a group.
     *
     * @param string $groupid the group id
     * @param string $name    the new name
     */
    public static function updateGroup($groupid, $name)
    {
        if ($groupid == '__anonymous') {
            throw new Exception('jAcl2DbUserGroup::updateGroup : invalid group id');
        }
        jDao::get('jacl2db~jacl2group', 'jacl2_profile')->changeName($groupid, $name);
    }

    /**
     * delete a group from the acl system.
     *
     * @param string $groupid the group id
     */
    public static function removeGroup($groupid)
    {
        if ($groupid == '__anonymous') {
            throw new Exception('jAcl2DbUserGroup::removeGroup : invalid group id');
        }
        // remove all the rights attached to the group
        jDao::get('jacl2db~jacl2rights', 'jacl2_profile')->deleteByGroup($groupid);
        // remove the users from the group
        jDao::get('jacl2db~jacl2usergroup', 'jacl2_profile')->deleteByGroup($groupid);
        // remove the group itself
        jDao::get('jacl2db~jacl2group', 'jacl2_profile')->delete($groupid);
    }

    /**
     * Return a list of group.
     *
     * if a login is given, it returns only the groups of the user.
     * Else it returns all groups (except private groups)
     *
     * @param string $login an optional login
     *
     * @return Jelix\Database\ResultSetInterface a list of groups object (dao records)
     */
    public static function getGroupList($login = '', $withAnonymous = false)
    {
        if ($login === '') {
            if ($withAnonymous) {
                return jDao::get('jacl2db~jacl2group', 'jacl2_profile')->findAllPublicGroupAndAnonymous();
            }
            return jDao::get('jacl2db~jacl2group', 'jacl2_profile')->findAllPublicGroup();
        }

        return jDao::get('jacl2db~jacl2groupsofuser', 'jacl2_profile')->getGroupsUser($login);
    }

    /**
     * Clear cache of variables of this class.
     *
     * @since 1.3
     */
    public static function clearCache()
    {
        self::$groups = null;
    }

    public static function renameUser($oldLogin, $newLogin)
    {
        $groupFactory = jDao::get('jacl2db~jacl2group', 'jacl2_profile');
        $userGroupFactory = jDao::get('jacl2db~jacl2usergroup', 'jacl2_profile');
        $rightsFactory = jDao::get('jacl2db~jacl2rights', 'jacl2_profile');

        $oldPrivGroupName = '__priv_'.$oldLogin;
        $newPrivGroupName = '__priv_'.$newLogin;

        $oldPrivGroup = $groupFactory->get($oldPrivGroupName);
        if (!$oldPrivGroup) {
            return;
        }
        if ($groupFactory->get($newPrivGroupName)) {
            // there is already a private group with the new name
            return;
        }

        // put the new login to existing groups where the old login is in
        $GroupsOfUserFactory = jDao::get('jacl2db~jacl2groupsofuser', 'jacl2_profile');

        $oldGroupsList = $GroupsOfUserFactory->getGroupsUser($oldLogin);

        foreach($oldGroupsList as $userGroup) {
            $newUserGroup = $userGroupFactory->createRecord();
            $newUserGroup->login = $newLogin;
            if ($oldPrivGroupName == $userGroup->id_aclgrp) {
                $newGroup = $groupFactory->createRecord();
                $newGroup->id_aclgrp = $newPrivGroupName;
                $newGroup->name = $userGroup->name;
                $newGroup->grouptype = $userGroup->grouptype;
                $newGroup->ownerlogin = $newLogin;
                $groupFactory->insert($newGroup);

                $newUserGroup->id_aclgrp = $newPrivGroupName;
            }
            else {
                $newUserGroup->id_aclgrp = $userGroup->id_aclgrp;
            }
            $userGroupFactory->insert($newUserGroup);
        }

        // set same rights of previous group, onto the new group
        $oldRights = $rightsFactory->getRightsByGroup($oldPrivGroupName);
        foreach ($oldRights as $oldRight) {
            $newRight = $rightsFactory->createRecord();
            $newRight->id_aclsbj = $oldRight->id_aclsbj;
            $newRight->id_aclres = $oldRight->id_aclres;
            $newRight->canceled = $oldRight->canceled;
            $newRight->id_aclgrp = $newPrivGroupName;
            $rightsFactory->insert($newRight);
        }

        // remove rights of the old login, and its private group
        $rightsFactory->deleteByGroup($oldPrivGroupName);
        $userGroupFactory->deleteByUser($oldLogin);
        $groupFactory->delete($oldPrivGroupName);
        self::clearCache();
    }

}
