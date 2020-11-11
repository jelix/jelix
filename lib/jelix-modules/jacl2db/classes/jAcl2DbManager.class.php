<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Adrien Lagroy de Croutte
 * @copyright   2006-2019 Laurent Jouanneau, 2020 Adrien Lagroy de Croutte
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 *
 * @since 1.1
 */

/**
 * This class is used to manage rights. Works only with db driver of jAcl2.
 *
 * @static
 */
class jAcl2DbManager
{
    public static $ACL_ADMIN_RIGHTS = array(
        'acl.group.view',
        'acl.group.modify',
        'acl.group.delete',
        'acl.user.view',
        'acl.user.modify',
    );

    /**
     * @internal The constructor is private, because all methods are static
     */
    private function __construct()
    {
    }

    /**
     * add a right on the given users group/resource.
     *
     * @param string $group    the users group id
     * @param string $right     the key of the right
     * @param string $resource the id of a resource
     *
     * @return bool true if the right is set
     */
    public static function addRight($group, $right, $resource = '-')
    {
        $sbj = jDao::get('jacl2db~jacl2subject', 'jacl2_profile')->get($right);
        if (!$sbj) {
            return false;
        }

        if (empty($resource)) {
            $resource = '-';
        }

        //  add the new value
        $daoright = jDao::get('jacl2db~jacl2rights', 'jacl2_profile');
        $rightset = $daoright->get($right, $group, $resource);
        if (!$rightset) {
            $rightset = jDao::createRecord('jacl2db~jacl2rights', 'jacl2_profile');
            $rightset->id_aclsbj = $right;
            $rightset->id_aclgrp = $group;
            $rightset->id_aclres = $resource;
            $rightset->canceled = 0;
            $daoright->insert($rightset);
        } elseif ($rightset->canceled) {
            $rightset->canceled = false;
            $daoright->update($rightset);
        }
        jAcl2::clearCache();

        return true;
    }

    /**
     * remove a right on the given users group/resource. The given right for
     * this users group will then inherit from other groups if the user is in
     * multiple groups of users.
     *
     * @param string $group    the users group id
     * @param string $right     the key of the right
     * @param string $resource the id of a resource
     * @param bool   $canceled true if the removing is to cancel a right, instead of an inheritance
     */
    public static function removeRight($group, $right, $resource = '-', $canceled = false)
    {
        if (empty($resource)) {
            $resource = '-';
        }

        $daoright = jDao::get('jacl2db~jacl2rights', 'jacl2_profile');
        if ($canceled) {
            $rightset = $daoright->get($right, $group, $resource);
            if (!$rightset) {
                $rightset = jDao::createRecord('jacl2db~jacl2rights', 'jacl2_profile');
                $rightset->id_aclsbj = $right;
                $rightset->id_aclgrp = $group;
                $rightset->id_aclres = $resource;
                $rightset->canceled = $canceled;
                $daoright->insert($rightset);
            } elseif ($rightset->canceled != $canceled) {
                $rightset->canceled = $canceled;
                $daoright->update($rightset);
            }
        } else {
            $daoright->delete($right, $group, $resource);
        }
        jAcl2::clearCache();
    }

    /**
     * Set all rights on the given users group.
     *
     * Only given rights are changed.
     * Existing rights not given in parameters are deleted from the group (i.e: marked as inherited).
     *
     * Rights with resources are not changed.
     *
     * @param string $group  the users group id
     * @param array  $rights list of rights key=right key, value=false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)
     */
    public static function setRightsOnGroup($group, $rights)
    {
        $subjects = jDao::get('jacl2db~jacl2subject', 'jacl2_profile')->findAllSubject()->fetchAll();
        $dao = jDao::get('jacl2db~jacl2rights', 'jacl2_profile');

        // retrieve old rights.
        $oldrights = array();
        $rs = $dao->getRightsByGroup($group);
        foreach ($rs as $rec) {
            $oldrights[$rec->id_aclsbj] = ($rec->canceled ? 'n' : 'y');
        }

        $roots = array();
        foreach ($subjects as $subject) {
            $matches = array();
            if (preg_match('/(.*)(\.view)$/', $subject->id_aclsbj, $matches)) {
                $roots[] = $matches[1];
            }
        }
        $alreadyTreatedSbj = array();
        // set new rights.  we modify $oldrights in order to have
        // only deprecated rights in $oldrights
        foreach ($rights as $sbj => $val) {
                if ($val === '' || $val == false || in_array($sbj, $alreadyTreatedSbj)) {
                // remove
            } elseif ($val === true || $val == 'y') {
                foreach ($roots as $root) {
                    if (strpos($sbj, $root) === 0) {
                        $viewRight = $root.'.view';
                        self::addRight($group, $viewRight);
                        if (isset($oldrights[$viewRight])) {
                            unset($oldrights[$viewRight]);
                        }
                    }
                }
                self::addRight($group, $sbj);
                unset($oldrights[$sbj]);
            } elseif ($val == 'n') {
                // cancel
                $matches = array();
                if (preg_match('/(.*)(\.view)$/', $sbj, $matches)) {
                    foreach ($subjects as $subject) {
                        if (preg_match('/^('.$matches[1].'.)/', $sbj)) {
                            self::removeRight($group, $subject->id_aclsbj, '-', true);
                            $alreadyTreatedSbj[] = $subject->id_aclsbj;
                        }
                    }
                }
                if (isset($oldrights[$sbj])) {
                    unset($oldrights[$sbj]);
                }
                self::removeRight($group, $sbj, '', true);
            }
        }

        if (count($oldrights)) {
            // $oldrights contains now rights to remove
            $dao->deleteByGroupAndRights($group, array_keys($oldrights));
        }
        jAcl2::clearCache();
    }

    /**
     * remove the right on the given resource, for all users groups.
     *
     * @param string $right     the key of the right
     * @param string $resource the id of a resource
     */
    public static function removeResourceRight($right, $resource)
    {
        if (empty($resource)) {
            $resource = '-';
        }
        jDao::get('jacl2db~jacl2rights', 'jacl2_profile')->deleteByRightRes($right, $resource);
        jAcl2::clearCache();
    }

    /**
     * create a new right.
     *
     * @param string $right         the key of the right
     * @param string $label_key    the key of a locale which represents the label of the right
     * @param string $rightsGroup the id of the rights group where the right is attached to
     *
     * @since 1.7.6
     */
    public static function createRight($right, $label_key, $rightsGroup = null)
    {
        $dao = jDao::get('jacl2db~jacl2subject', 'jacl2_profile');
        if ($dao->get($right)) {
            return;
        }
        $subj = jDao::createRecord('jacl2db~jacl2subject', 'jacl2_profile');
        $subj->id_aclsbj = $right;
        $subj->label_key = $label_key;
        $subj->id_aclsbjgrp = $rightsGroup;
        $dao->insert($subj);
        jAcl2::clearCache();
    }

    /**
     * create a new right.
     *
     * @param string $right         the key of the right
     * @param string $label_key    the key of a locale which represents the label of the right
     * @param string $rightsGroup the id of the rights group where the right is attached to
     *
     * @deprecated
     * @see createRight()
     * @since 1.7
     */
    public static function addRole($right, $label_key, $rightsGroup = null)
    {
        self::createRight($right, $label_key, $rightsGroup);
    }

    /**
     * create a new right.
     *
     * @deprecated
     * @see createRight()
     *
     * @param string $right         the key of the right
     * @param string $label_key    the key of a locale which represents the label of the right
     * @param string $rightsGroup the id of the rights group where the right is attached to
     */
    public static function addSubject($right, $label_key, $rightsGroup = null)
    {
        self::createRight($right, $label_key, $rightsGroup);
    }

    /**
     * Delete the given right.
     *
     * It is deleted from the database, so it is not usable anymore.
     *
     * @param string $right the key of the right
     *
     * @since 1.7.6
     */
    public static function deleteRight($right)
    {
        jDao::get('jacl2db~jacl2rights', 'jacl2_profile')->deleteByRight($right);
        jDao::get('jacl2db~jacl2subject', 'jacl2_profile')->delete($right);
        jAcl2::clearCache();
    }

    /**
     * Delete the given right.
     *
     * @param string $right the key of the right
     *
     * @deprecated
     * @see deleteRight()
     * @since 1.7
     */
    public static function removeRole($right)
    {
        self::deleteRight($right);
    }

    /**
     * Delete the given right.
     *
     * @param string $right the key of the right
     *
     * @deprecated
     * @see deleteRight()
     */
    public static function removeSubject($right)
    {
        self::deleteRight($right);
    }

    /**
     * Set a right to users groups which have the given right
     *
     * It can be useful when creating a new right.
     *
     * @param string $sourceRight  the right that users groups have
     * @param mixed  $targetRight  the right to set on users groups having $sourceRight
     *
     * @since 1.7.6
     */
    public static function copyRightSettings($sourceRight, $targetRight)
    {
        $daoright = jDao::get('jacl2db~jacl2rights', 'jacl2_profile');

        $allRights = $daoright->getRightSettings($sourceRight);
        foreach ($allRights as $right) {
            $rightTo = $daoright->get($targetRight, $right->id_aclgrp, $right->id_aclres);
            if (!$rightTo) {
                $rightTo = jDao::createRecord('jacl2db~jacl2rights', 'jacl2_profile');
                $rightTo->id_aclsbj = $targetRight;
                $rightTo->id_aclgrp = $right->id_aclgrp;
                $rightTo->id_aclres = $right->id_aclres;
                $rightTo->canceled = $right->canceled;
                $daoright->insert($rightTo);
            } elseif ($right->canceled != $rightTo->canceled) {
                $rightTo->canceled = $right->canceled;
                $daoright->update($rightTo);
            }
        }

        jAcl2::clearCache();
    }

    /**
     * Set a right to users groups which have the given right
     *
     * It can be useful when creating a new right.
     *
     * @param string $sourceRight  the right that users groups have
     * @param mixed  $targetRight  the right to set on users groups having $sourceRight
     *
     * @deprecated
     * @see copyRightSettings()
     */
    public static function copyRoleRights($sourceRight, $targetRight)
    {
        self::copyRightSettings($sourceRight, $targetRight);
    }

    /**
     * Create a new right group.
     *
     * @param string $rightGroup the key of the right group
     * @param string $label_key the key of a locale which represents the label of the right group
     *
     * @since 1.7.6
     */
    public static function createRightGroup($rightGroup, $label_key)
    {
        $dao = jDao::get('jacl2db~jacl2subjectgroup', 'jacl2_profile');
        if ($dao->get($rightGroup)) {
            return;
        }
        $subj = jDao::createRecord('jacl2db~jacl2subjectgroup', 'jacl2_profile');
        $subj->id_aclsbjgrp = $rightGroup;
        $subj->label_key = $label_key;
        $dao->insert($subj);
        jAcl2::clearCache();
    }

    /**
     * Create a new right group.
     *
     * @param string $rightGroup the key of the right group
     * @param string $label_key the key of a locale which represents the label of the right group
     *
     * @deprecated see createRightGroup()
     * @since 1.7
     */
    public static function addRoleGroup($rightGroup, $label_key)
    {
        self::createRightGroup($rightGroup, $label_key);
    }

    /**
     * Create a new right group.
     *
     * @param string $rightGroup the key of the right group
     * @param string $label_key the key of a locale which represents the label of the right group
     *
     * @since 1.3
     * @deprecated see createRightGroup()
     */
    public static function addSubjectGroup($rightGroup, $label_key)
    {
        self::createRightGroup($rightGroup, $label_key);
    }

    /**
     * Delete the given right group.
     *
     * @param string $rightGroup the key of the right group
     *
     * @since 1.7
     */
    public static function deleteRightGroup($rightGroup)
    {
        jDao::get('jacl2db~jacl2subject', 'jacl2_profile')->removeRightsFromRightsGroup($rightGroup);
        jDao::get('jacl2db~jacl2subjectgroup', 'jacl2_profile')->delete($rightGroup);
        jAcl2::clearCache();
    }

    /**
     * Delete the given right group.
     *
     * @param string $rightGroup the key of the right group
     *
     * @since 1.7
     * @deprecated see deleteRightGroup
     */
    public static function removeRoleGroup($rightGroup)
    {
        self::deleteRightGroup($rightGroup);
    }

    /**
     * Delete the right group.
     *
     * @param string $rightGroup the key of the right group
     *
     * @since 1.3
     * @deprecated see deleteRightGroup
     */
    public static function removeSubjectGroup($rightGroup)
    {
        self::deleteRightGroup($rightGroup);
    }

    const ACL_ADMIN_RIGHTS_STILL_USED = 0;
    const ACL_ADMIN_RIGHTS_NOT_ASSIGNED = 1;
    const ACL_ADMIN_RIGHTS_SESSION_USER_LOOSE_THEM = 2;

    /**
     * Only given rights are considered changed.
     * Existing rights not given in parameters are considered as deleted.
     *
     * Rights with resources are not changed.
     *
     * @param array      $rightsChanges         array($id_aclgrp => array( $right => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)))
     * @param null|mixed $sessionUser
     * @param mixed      $setForAllPublicGroups
     * @param mixed      $setAllRightsInGroups
     * @param null|mixed $ignoredUser
     * @param null|mixed $ignoreUserInGroup
     *
     * @return int one of the ACL_ADMIN_RIGHTS_* const
     */
    public static function checkAclAdminRightsChanges(
        $rightsChanges,
        $sessionUser = null,
        $setForAllPublicGroups = true,
        $setAllRightsInGroups = true,
        $ignoredUser = null,
        $ignoreUserInGroup = null
    ) {
        $canceledRights = array();
        $assignedRights = array();
        $sessionUserGroups = array();
        $sessionCanceledRights = array();
        $sessionAssignedRights = array();

        $db = jDb::getConnection('jacl2_profile');
        if ($sessionUser) {
            $gp = jDao::get('jacl2db~jacl2usergroup', 'jacl2_profile')
                ->getGroupsUser($sessionUser)
            ;
            foreach ($gp as $g) {
                $sessionUserGroups[$g->id_aclgrp] = true;
            }
        }

        // get all acl admin rights, even all those in private groups
        $sql = 'SELECT id_aclsbj, r.id_aclgrp, canceled, g.grouptype
            FROM '.$db->prefixTable('jacl2_rights').' r 
            INNER JOIN '.$db->prefixTable('jacl2_group').' g 
            ON (r.id_aclgrp = g.id_aclgrp)
            WHERE id_aclsbj IN ('.implode(',', array_map(function ($right) use ($db) {
            return $db->quote($right);
        }, self::$ACL_ADMIN_RIGHTS)).') ';
        $rs = $db->query($sql);
        foreach ($rs as $rec) {
            if ($sessionUser && isset($sessionUserGroups[$rec->id_aclgrp])) {
                if ($rec->canceled != '0') {
                    $sessionCanceledRights[$rec->id_aclsbj] = true;
                } else {
                    $sessionAssignedRights[$rec->id_aclsbj] = true;
                }
            }
            if ($setForAllPublicGroups &&
                !isset($rightsChanges[$rec->id_aclgrp]) &&
                $rec->grouptype != jAcl2DbUserGroup::GROUPTYPE_PRIVATE
            ) {
                continue;
            }
            if ($rec->canceled != '0') {
                $canceledRights[$rec->id_aclgrp][$rec->id_aclsbj] = true;
            } else {
                $assignedRights[$rec->id_aclgrp][$rec->id_aclsbj] = true;
            }
        }

        $rightsStats = array_combine(self::$ACL_ADMIN_RIGHTS, array_fill(0, count(self::$ACL_ADMIN_RIGHTS), 0));

        // now apply changes
        foreach ($rightsChanges as $groupId => $changes) {
            if (!isset($assignedRights[$groupId])) {
                $assignedRights[$groupId] = array();
            }
            if (!isset($canceledRights[$groupId])) {
                $canceledRights[$groupId] = array();
            }
            $unassignedRights = array_combine(self::$ACL_ADMIN_RIGHTS, array_fill(0, count(self::$ACL_ADMIN_RIGHTS), true));
            foreach ($changes as $right => $rightAssignation) {
                if (!isset($rightsStats[$right])) {
                    continue;
                }
                unset($unassignedRights[$right]);
                if ($rightAssignation === false || $rightAssignation === '') {
                    // inherited
                    if (isset($assignedRights[$groupId][$right])) {
                        unset($assignedRights[$groupId][$right]);
                    }
                    if (isset($canceledRights[$groupId][$right])) {
                        unset($canceledRights[$groupId][$right]);
                    }
                } elseif ($rightAssignation == 'y' || $rightAssignation === true) {
                    if (isset($canceledRights[$groupId][$right])) {
                        unset($canceledRights[$groupId][$right]);
                    }
                    $assignedRights[$groupId][$right] = true;
                } elseif ($rightAssignation == 'n') {
                    if (isset($assignedRights[$groupId][$right])) {
                        unset($assignedRights[$groupId][$right]);
                    }
                    $canceledRights[$groupId][$right] = true;
                }
            }
            if ($setAllRightsInGroups) {
                foreach ($unassignedRights as $right => $ok) {
                    if (isset($assignedRights[$groupId][$right])) {
                        unset($assignedRights[$groupId][$right]);
                    }
                    if (isset($canceledRights[$groupId][$right])) {
                        unset($canceledRights[$groupId][$right]);
                    }
                }
            }
            if (count($assignedRights[$groupId]) == 0 && count($canceledRights[$groupId]) == 0) {
                unset($assignedRights[$groupId], $canceledRights[$groupId]);
            }
        }

        // get all users that are in groups having new acl admin rights
        $allGroups = array_unique(array_merge(array_keys($assignedRights), array_keys($canceledRights)));
        if (count($allGroups) === 0) {
            return self::ACL_ADMIN_RIGHTS_NOT_ASSIGNED;
        }

        $sql = 'SELECT login, id_aclgrp FROM '.$db->prefixTable('jacl2_user_group').'
            WHERE id_aclgrp IN ('.implode(',', array_map(function ($grp) use ($db) {
            return $db->quote($grp);
        }, $allGroups)).') ';

        $rs = $db->query($sql);
        $users = array();
        foreach ($rs as $rec) {
            if ($rec->login === $ignoredUser &&
                ($ignoreUserInGroup === null || $ignoreUserInGroup === $rec->id_aclgrp)) {
                continue;
            }
            if (!isset($users[$rec->login])) {
                $users[$rec->login] = array('canceled' => array(), 'rights' => array());
            }
            if (isset($assignedRights[$rec->id_aclgrp])) {
                $users[$rec->login]['rights'] = array_merge($users[$rec->login]['rights'], $assignedRights[$rec->id_aclgrp]);
            }
            if (isset($canceledRights[$rec->id_aclgrp])) {
                $users[$rec->login]['canceled'] = array_merge($users[$rec->login]['canceled'], $canceledRights[$rec->id_aclgrp]);
            }
        }

        // gets statistics
        $newSessionUserRights = array();
        foreach ($users as $login => $data) {
            if (count($data['canceled'])) {
                $data['rights'] = array_diff_key($data['rights'], $data['canceled']);
            }
            if ($login === $sessionUser) {
                $newSessionUserRights = $data['rights'];
            }
            foreach ($data['rights'] as $right => $ok) {
                ++$rightsStats[$right];
            }
        }

        if ($sessionUser) {
            foreach ($sessionAssignedRights as $right => $ok) {
                if (isset($sessionCanceledRights[$right])) {
                    continue;
                }
                if (!isset($newSessionUserRights[$right])) {
                    return self::ACL_ADMIN_RIGHTS_SESSION_USER_LOOSE_THEM;
                }
            }
        }

        foreach ($rightsStats as $count) {
            if ($count == 0) {
                return self::ACL_ADMIN_RIGHTS_NOT_ASSIGNED;
            }
        }

        return self::ACL_ADMIN_RIGHTS_STILL_USED;
    }
}
