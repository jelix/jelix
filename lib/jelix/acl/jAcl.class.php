<?php
/**
* @package     jelix
* @subpackage  acl
* @version     $Id:$
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * Permet de connaître les droits
 */
class jAcl {

   private function __construct (){ }

   public static function isMemberOfGroup ($groupid){
      $groups = self::getGroups();
      return in_array($groupid, $groups);
   }

   public static function check($subject, $value, $resource=null){
      $val = self::getRight($subject, $resource);
      return ($val & $value)?true:false;
   }

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
      if(!isset($_SESSION['JELIX_USER']->login))
         return 0;

      $groups = self::getGroups();

      // recupère toutes les valeurs correspondant aux groupes auquel appartient le user,
      //   avec le sujet et ressource indiqué
      // droit = OU entre ces valeurs

      $dao = jDao::get('acl~jaclrights');
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


  protected static function getGroups(){
      static $groups = null;

      if(!isset($_SESSION['JELIX_USER']->login))
         return array();

      // chargement des groupes
      if($groups === null){
         $dao = jDao::get('acl~jaclusergroup');
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