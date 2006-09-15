<?php
/**
* @package     jelix
* @subpackage  acl
* @version     $Id:$
* @author      Laurent Jouanneau
* @copyright   2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
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
     * @param string  $subject the key of the subject to check
     * @param int $value the value to test against
     * @param string $resource the id of a resource
     * @return boolean true if yes
     */
    public static function check($subject, $value, $resource=null){
        $val = self::getRight($subject, $resource);
        return ($val & $value)?true:false;
    }

    /**
     * return the value of the right on the given subject (and on the optional resource)
     * @param string  $subject the key of the subject
     * @param string $resource the id of a resource
     * @return int the value of the right
     */
    public static function getRight($subject, $resource=null){
        static $aclres = array();
        static $acl = array();

        if($resource === null){
            if(isset($acl[$subject])){
                return $acl[$subject];
            }
        }else{
            if(isset($aclres[$subject][$resource])){
                return $aclres[$subject][$resource];
            }
        }
        if(!jAuth::isConnected())
            return 0;

        $groups = self::getGroups();

        // recupère toutes les valeurs correspondant aux groupes auquel appartient le user,
        //   avec le sujet et ressource indiqué
        // droit = OU entre ces valeurs

        $dao = jDao::get('jxacl~jaclrights');
        if($resource === null){
            $list=$dao->getAllGroupRights($subject, $groups);
        }else{
            $list=$dao->getAllGroupRightsWithRes($subject, $groups, $resource);
        }
        $value=0;
        foreach($list as $right){
            $value |= intval($right->value);
        }

        if($resource === null){
            $aclres[$subject][$resource] =$value;
        }else{
            $acl[$subject] = $value;
        }
        return $value;
    }

    /**
     * retrieve the list of group the current user is member of
     * @return array list of group id
     */
    protected static function getGroups(){
        static $groups = null;

        if(!isset($_SESSION['JELIX_USER']->login))
            return array();

        // chargement des groupes
        if($groups === null){
            $dao = jDao::get('jxacl~jaclusergroup');
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