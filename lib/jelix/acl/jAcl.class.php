<?php
/**
* @package     jelix
* @subpackage  acl
* @author      Laurent Jouanneau
* @copyright   2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
* @since 1.0a3
*/

/**
 * Main class to query the acl system, and to know value of a right
 *
 * you should call this class (all method are static) when you want to know if
 * the current user have a right, or to know if he is a member of a group
 * This class needs the acl module.
 * @package jelix
 * @subpackage acl
 * @static
 */
class jAcl {

    /**
     * @internal The constructor is private, because all methods are static
     */
    private function __construct (){ }

    /**
     * Says if the current user is a member of the given user group
     * @param int $groupid The id of a group
     * @return boolean true if it's ok
     */
    public static function isMemberOfGroup ($groupid){
        $groups = self::getGroups();
        return in_array($groupid, $groups);
    }

    /**
     * call this method to know if the current user has the right with the given value
     * @param string $subject the key of the subject to check
     * @param string $value the value to test against
     * @param string $resource the id of a resource
     * @return boolean true if yes
     */
    public static function check($subject, $value, $resource=null){
        $val = self::getRight($subject, $resource);
        return in_array($value,$val);
    }

    /**
     * return the value of the right on the given subject (and on the optional resource)
     * @param string $subject the key of the subject
     * @param string $resource the id of a resource
     * @return array list of values corresponding to the right
     */
    public static function getRight($subject, $resource=null){
        static $aclres = array();
        static $acl = array();

        if($resource === null && isset($acl[$subject])){
            return $acl[$subject];
        }elseif(isset($aclres[$subject][$resource])){
            return $aclres[$subject][$resource];
        }

        if(!jAuth::isConnected()) // not authificated = no rights
            return array();

        $groups = self::getGroups();

        // recupère toutes les valeurs correspondant aux groupes auquel appartient le user,
        //   avec le sujet et ressource indiqué
        $values= array();
        $dao = jDao::get('jelix~jaclrights');
        $list = $dao->getAllGroupRights($subject, $groups);
        foreach($list as $right){
            $values [] = $right->value;
        }
        $acl[$subject] = $values;

        if($resource !== null){
            $list = $dao->getAllGroupRightsWithRes($subject, $groups, $resource);
            foreach($list as $right){
                $values [] = $right->value;
            }
            $aclres[$subject][$resource] = $values = array_unique($values);
        }

        return $values;
    }

    /**
     * retrieve the list of group the current user is member of
     * @return array list of group id
     */
    protected static function getGroups(){
        static $groups = null;

        if(!jAuth::isConnected())
            return array();

        // chargement des groupes
        if($groups === null){
            $dao = jDao::get('jelix~jaclusergroup');
            $gp = $dao->getGroupsUser($_SESSION['JELIX_USER']->login);
            $groups = array();
            foreach($gp as $g){
                $groups[]=intval($g->id_aclgrp);
            }
        }
        return $groups;
    }
}

?>