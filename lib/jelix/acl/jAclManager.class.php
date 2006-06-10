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
 * Classe pour modifier les droits
 */
class jAclManager {

    function setRight($group, $subject, $value , $resource=null){

       //  récupère le groupe de valeur correspondant au subject
       //  récupère la liste des valeurs du groupe de valeur
       //  fait un & avec $value
       //  met à jour la table jacl_rights

       $daosbj = jDao::get('acl~jaclsubject');
       $daorightval = jDao::get('acl~jaclrightvalues');
       $daoright = jDao::get('acl~jaclrights');

       $sbj = $daosbj->get($subject);
       if(!$sbj) return;

       $vallist = $daorightval->findByValGroup($sbj->id_aclvalgrp);
       $val = 0;
       foreach($vallist as $valgrp){
          $val |= $valgrp->value;
       }
       $value &= $val;
       if(!$value) return;

       $right = $daoright->get($subject,$group,$resource);
       if($right){
          $right->value = $value;
          $daoright->update($right);
       }else{
          $right = jDao::createRecord('acl~jaclrights');
          $right->id_aclsbj = $subject;
          $right->id_aclgrp = $group;
          $right->id_aclres = $resource;
          $right->value = $value;
          $daoright->insert($right);
       }
    }

    function removeResourceRight($subject, $resource){
        $daoright = jDao::get('acl~jaclrights');
        $daoright->deleteBySubjRes($subject, $resource);
    }


    function addSubject($subject, $id_aclvalgrp, $label_key){
         // ajoute un sujet dans la table jacl_subject
         $daosbj = jDao::get('acl~jaclsubject');
         $subj = jDao::createRecord('acl~jaclsubject');
         $subj->id_aclsbj=$subject;
         $subj->id_aclvalgrp=$id_aclvalgrp;
         $subj->label_key =$label_key;
         $daosbj->insert($subj);

    }

    function removeSubject($subject){
      // supprime dans jacl_rights
      // supprime dans jacl_subject
      $daoright = jDao::get('acl~jaclrights');
      $daoright->deleteBySubject($subject);
      $daosbj = jDao::get('acl~jaclsubject');
      $daosbj->delete($subject);
    }


}

?>