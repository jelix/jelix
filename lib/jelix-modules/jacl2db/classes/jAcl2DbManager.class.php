<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Adrien Lagroy de Croutte
 *
 * @copyright   2006-2021 Laurent Jouanneau, 2020 Adrien Lagroy de Croutte
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
     * @param string $right    the key of the right
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
     * @param string $right    the key of the right
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
        $dao = jDao::get('jacl2db~jacl2rights', 'jacl2_profile');

        // retrieve old rights.
        $oldrights = array();
        $rs = $dao->getRightsByGroup($group);
        foreach ($rs as $rec) {
            $oldrights[$rec->id_aclsbj] = ($rec->canceled ? 'n' : 'y');
        }

        // set new rights.  we modify $oldrights in order to have
        // only deprecated rights in $oldrights
        foreach ($rights as $sbj => $val) {
            if ($val === '' || $val == false) {
                // remove
            } elseif ($val === true || $val == 'y') {
                self::addRight($group, $sbj);
                unset($oldrights[$sbj]);
            } elseif ($val == 'n') {
                // cancel
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
     * @param string $right    the key of the right
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
     * @param string $right       the key of the right
     * @param string $label_key   the key of a locale which represents the label of the right
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
     * @param string $right       the key of the right
     * @param string $label_key   the key of a locale which represents the label of the right
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
     * @param string $right       the key of the right
     * @param string $label_key   the key of a locale which represents the label of the right
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
     * Set a right to users groups which have the given right.
     *
     * It can be useful when creating a new right.
     *
     * @param string $sourceRight the right that users groups have
     * @param mixed  $targetRight the right to set on users groups having $sourceRight
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
     * Set a right to users groups which have the given right.
     *
     * It can be useful when creating a new right.
     *
     * @param string $sourceRight the right that users groups have
     * @param mixed  $targetRight the right to set on users groups having $sourceRight
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
     * @param string $label_key  the key of a locale which represents the label of the right group
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
     * @param string $label_key  the key of a locale which represents the label of the right group
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
     * @param string $label_key  the key of a locale which represents the label of the right group
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
     * Checks if given authorizations changes still allow to administrate rights
     * for at least one user.
     *
     * For each groups, only authorizations on given rights are considered changed.
     * Other existing authorizations are considered as deleted.
     *
     * Authorizations with resources are not changed.
     *
     * @param array  $authorizationsChanges array(<id_aclgrp> => array( <id_aclsbj> => false(inherit)/''(inherit)/true(add)/'y'(add)/'n'(remove)))
     * @param string $sessionUser           the login name of the user who initiate the change
     * @param int    $changeType            1 for group rights change, 2 for user rights change
     *
     * @return int one of the jAcl2DbAdminCheckAuthorizations::ACL_ADMIN_RIGHTS_* const
     */
    public static function checkAclAdminAuthorizationsChanges(
        $authorizationsChanges,
        $sessionUser,
        $changeType
    ) {
        $checker = new jAcl2DbAdminCheckAuthorizations($sessionUser);

        return $checker->checkAclAdminAuthorizationsChanges($authorizationsChanges, $changeType);
    }

    /**
     * check if the removing of the given user still allow to administrate authorizations
     * for at least one user.
     *
     * @param string $userToRemove
     * @param string $sessionUser  the login name of the user who initiate the change
     *
     * @return int one of ACL_ADMIN_RIGHTS_* constant
     */
    public static function checkAclAdminRightsToRemoveUser(
        $userToRemove,
        $sessionUser = null
    ) {
        $checker = new jAcl2DbAdminCheckAuthorizations($sessionUser);

        return $checker->checkAclAdminRightsToRemoveUser($userToRemove);
    }

    /**
     * check if the removing of the given user from a the given group still
     * allows to administrate rights for at least one user.
     *
     * @param string $userToRemoveFromTheGroup
     * @param string $groupFromWhichToRemoveTheUser
     * @param string $sessionUser                   the login name of the user who initiate the change
     *
     * @return int one of ACL_ADMIN_RIGHTS_* constant
     */
    public static function checkAclAdminRightsToRemoveUserFromGroup(
        $userToRemoveFromTheGroup,
        $groupFromWhichToRemoveTheUser,
        $sessionUser
    )
    {
        $checker = new jAcl2DbAdminCheckAuthorizations($sessionUser);

        return $checker->checkAclAdminRightsToRemoveUserFromGroup($userToRemoveFromTheGroup, $groupFromWhichToRemoveTheUser);
    }

    /**
     * check if the removing of the given group still
     * allows to administrate rights for at least one user.
     *
     * @param string $groupToRemove the group id to remove
     * @param string $sessionUser   the login name of the user who initiate the change
     *
     * @return int one of ACL_ADMIN_RIGHTS_* constant
     */
    public static function checkAclAdminRightsToRemoveGroup(
        $groupToRemove,
        $sessionUser
    ) {
        $checker = new jAcl2DbAdminCheckAuthorizations($sessionUser);

        return $checker->checkAclAdminRightsToRemoveGroup($groupToRemove);
    }

    /**
     * check if the adding of the given user to the the given group still
     * allows to administrate rights for at least one user.
     *
     * (because the group may forbid to administrate rights.)
     *
     * @param string $userToAdd              the user login
     * @param string $groupInWhichToAddAUser the group id
     * @param string $sessionUser            the login name of the user who initiate the change
     *
     * @return int one of ACL_ADMIN_RIGHTS_* constant
     */
    public static function checkAclAdminRightsToAddUserIntoGroup(
        $userToAdd,
        $groupInWhichToAddAUser,
        $sessionUser
    ) {
        $checker = new jAcl2DbAdminCheckAuthorizations($sessionUser);

        return $checker->checkAclAdminRightsToAddUserIntoGroup(
            $userToAdd,
            $groupInWhichToAddAUser
        );
    }
}
