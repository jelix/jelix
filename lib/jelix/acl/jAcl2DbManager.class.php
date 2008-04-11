<?php
/**
* @package     jelix
* @subpackage  acl
* @author      Laurent Jouanneau
* @copyright   2006-2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
* @since 1.1
*/


/**
 * This class is used to manage rights. Works only with db driver of jAcl2.
 * @package     jelix
 * @subpackage  acl
 * @static
 */
class jAcl2DbManager {

    /**
     * @internal The constructor is private, because all methods are static
     */
    private function __construct (){ }

    /**
     * add a right on the given subject/group/resource
     * @param int    $group the group id.
     * @param string $subject the key of the subject
     * @param string $resource the id of a resource
     * @return boolean  true if the right is set
     */
    public static function addRight($group, $subject, $resource=''){
        $profil = jAcl2Db::getProfil();
        $sbj = jDao::get('jelix~jacl2subject', $profil)->get($subject);
        if(!$sbj) return false;

        if($resource === null) $resource='';

        //  ajoute la nouvelle valeur
        $daoright = jDao::get('jelix~jacl2rights', $profil);
        $right = $daoright->get($subject,$group,$resource);
        if(!$right){
            $right = jDao::createRecord('jelix~jacl2rights', $profil);
            $right->id_aclsbj = $subject;
            $right->id_aclgrp = $group;
            $right->id_aclres = $resource;
            $daoright->insert($right);
        }
        jAcl2::clearCache();
        return true;
    }

    /**
     * remove a right on the given subject/group/resource
     * @param int    $group the group id.
     * @param string $subject the key of the subject
     * @param string $resource the id of a resource
     */
    public static function removeRight($group, $subject, $resource=''){
        if($resource === null) $resource='';
        jDao::get('jelix~jacl2rights', jAcl2Db::getProfil())
            ->delete($subject,$group,$resource);
        jAcl2::clearCache();
    }

    /**
     * set rights on the given group. old rights are removed
     * @param int    $group the group id.
     * @param array  $rights, list of rights key=subject, value=true
     */
    public static function setRightsOnGroup($group, $rights){
        $dao = jDao::get('jelix~jacl2rights', jAcl2Db::getProfil());
        $dao->deleteByGroup($group);
        foreach($rights as $sbj=>$val){
            if($val != '')
              self::addRight($group,$sbj);
        }
        jAcl2::clearCache();
    }

    /**
     * remove the right on the given subject/resource, for all groups
     * @param string  $subject the key of the subject
     * @param string $resource the id of a resource
     */
    public static function removeResourceRight($subject, $resource){
        jDao::get('jelix~jacl2rights', jAcl2Db::getProfil())->deleteBySubjRes($subject, $resource);
        jAcl2::clearCache();
    }

    /**
     * create a new subject
     * @param string  $subject the key of the subject
     * @param string $label_key the key of a locale which represents the label of the subject
     */
    public static function addSubject($subject, $label_key){
        // ajoute un sujet dans la table jacl_subject
        $p = jAcl2Db::getProfil();
        $subj = jDao::createRecord('jelix~jacl2subject',$p);
        $subj->id_aclsbj=$subject;
        $subj->label_key =$label_key;
        jDao::get('jelix~jacl2subject',$p)->insert($subj);
        jAcl2::clearCache();
    }

    /**
     * Delete the given subject
     * @param string  $subject the key of the subject
     */
    public static function removeSubject($subject){
        $p = jAcl2Db::getProfil();
        jDao::get('jelix~jacl2rights',$p)->deleteBySubject($subject);
        jDao::get('jelix~jacl2subject',$p)->delete($subject);
        jAcl2::clearCache();
    }
}

