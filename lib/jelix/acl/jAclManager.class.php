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
 * This class is used to manage rights
 * It needs the acl module.
 * @package     jelix
 * @subpackage  acl
 * @static
 */
class jAclManager {

    /**
     * @internal The constructor is private, because all methods are static
     */
    private function __construct (){ }

    /**
     * specify the value of a right on the given subject/group/resource
     * @return boolean  true if the right is set
     */
    public static function setRight($group, $subject, $value , $resource=''){

       //  récupère le groupe de valeur correspondant au subject

       $daosbj = jDao::get('jxacl~jaclsubject');
       $daorightval = jDao::get('jxacl~jaclrightvalues');
       $daoright = jDao::get('jxacl~jaclrights');

       $sbj = $daosbj->get($subject);
       if(!$sbj) return false;

       //  récupère la liste des valeurs du groupe de valeur
       $vallist = $daorightval->findByValGroup($sbj->id_aclvalgrp);

       // fait un & avec $value, pour être sûr que la valeur correspondent bien
       // à une valeur possible
       $val = 0;
       foreach($vallist as $valgrp){
          $val |= $valgrp->value;
       }
       $value &= $val;
       if(!$value) return false;

       if($resource === null) $resource='';
       //  met à jour la table jacl_rights
       $right = $daoright->get($subject,$group,$resource);
       if($right){
          $right->value = $value;
          $daoright->update($right);
       }else{
          $right = jDao::createRecord('jxacl~jaclrights');
          $right->id_aclsbj = $subject;
          $right->id_aclgrp = $group;
          $right->id_aclres = $resource;
          $right->value = $value;
          $daoright->insert($right);
       }
       return true;
    }

    /**
     * remove the right on the given subject/resource, for all groups
     * @param string  $subject the key of the subject
     * @param string $resource the id of a resource
     */
    public static function removeResourceRight($subject, $resource){
        $daoright = jDao::get('jxacl~jaclrights');
        $daoright->deleteBySubjRes($subject, $resource);
    }

    /**
     * create a new subject
     * @param string  $subject the key of the subject
     * @param int $id_aclvalgrp the id of the values group with which the right can be set
     * @param string $label_key the key of a locale which represents the label of the subject
     */
    public static function addSubject($subject, $id_aclvalgrp, $label_key){
         // ajoute un sujet dans la table jacl_subject
         $daosbj = jDao::get('jxacl~jaclsubject');
         $subj = jDao::createRecord('jxacl~jaclsubject');
         $subj->id_aclsbj=$subject;
         $subj->id_aclvalgrp=$id_aclvalgrp;
         $subj->label_key =$label_key;
         $daosbj->insert($subj);

    }

    /**
     * Delete the given subject
     * @param string  $subject the key of the subject
     */
    public static function removeSubject($subject){
      // supprime dans jacl_rights
      // supprime dans jacl_subject
      $daoright = jDao::get('jxacl~jaclrights');
      $daoright->deleteBySubject($subject);
      $daosbj = jDao::get('jxacl~jaclsubject');
      $daosbj->delete($subject);
    }


}

?>