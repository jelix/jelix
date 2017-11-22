<?php
/**
 * @package     jelix_admin_modules
 * @subpackage  jacl2db_admin
 * @author      Laurent Jouanneau
 * @contributor Julien Issler, Olivier Demah
 * @copyright   2008-2017 Laurent Jouanneau
 * @copyright   2009 Julien Issler
 * @copyright   2010 Olivier Demah
 * @link        http://jelix.org
 * @licence     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
 */


class AclAdminUIManager {

    protected function getLabel($id, $labelKey) {
        if ($labelKey) {
            try {
                return jLocale::get($labelKey);
            }
            catch(Exception $e) { }
        }
        return $id;
    }

    /**
     * @return array
     *      'groups' : list of jacl2group objects (id_aclgrp, name, grouptype, ownerlogin)
     *      'rights' : array( <subject> => array( <id_aclgrp> => 'y' or 'n' or ''))
     *      'sbjgroups_localized' : list of labels of each subject groups
     *      'subjects' : array( <subject> => array( 'grp' => <id_aclsbjgrp>, 'label' => <label>))
     *      'rightsWithResources':  array(<subject> => array( <id_aclgrp> => <number of rights>))
     */
    public function getGroupRights() {
        $gid = array('__anonymous');
        $o = new StdClass;
        $o->id_aclgrp = '__anonymous';
        try {
            $o->name = jLocale::get('jacl2db_admin~acl2.anonymous.group.name');
        }
        catch(Exception $e) {
            $o->name = 'Anonymous';
        }
        $o->grouptype = jAcl2DbUserGroup::GROUPTYPE_NORMAL;
        $o->ownerlogin = NULL;

        $daorights = jDao::get('jacl2db~jacl2rights','jacl2_profile');
        $rightsWithResources = array();
        $hasRightsOnResources = false;

        // retrieve the list of groups and the number of existing rights with
        // resource for each groups
        $groups=array($o);
        $grouprights=array('__anonymous'=>false);
        foreach(jAcl2DbUserGroup::getGroupList() as $grp) {
            $gid[]=$grp->id_aclgrp;
            $groups[]=$grp;
            $grouprights[$grp->id_aclgrp]='';

            $rs = $daorights->getRightsHavingRes($grp->id_aclgrp);
            foreach($rs as $rec){
                if (!isset($rightsWithResources[$rec->id_aclsbj]))
                    $rightsWithResources[$rec->id_aclsbj] = array();
                if (!isset($rightsWithResources[$rec->id_aclsbj][$grp->id_aclgrp]))
                    $rightsWithResources[$rec->id_aclsbj][$grp->id_aclgrp] = 0;
                $rightsWithResources[$rec->id_aclsbj][$grp->id_aclgrp] ++;
            }
        }

        // retrieve the number of existing rights with
        // resource for the anonymous group
        $rs = $daorights->getRightsHavingRes('__anonymous');
        foreach($rs as $rec){
            if (!isset($rightsWithResources[$rec->id_aclsbj]))
                $rightsWithResources[$rec->id_aclsbj] = array();
            if (!isset($rightsWithResources[$rec->id_aclsbj]['__anonymous']))
                $rightsWithResources[$rec->id_aclsbj]['__anonymous'] = 0;
            $rightsWithResources[$rec->id_aclsbj]['__anonymous'] ++;
        }

        // create the list of subjects and their labels
        $rights=array();
        $sbjgroups_localized = array();
        $subjects = array();
        $rs = jDao::get('jacl2db~jacl2subject','jacl2_profile')->findAllSubject();
        foreach($rs as $rec){
            $rights[$rec->id_aclsbj] = $grouprights;
            $subjects[$rec->id_aclsbj] = array(
                'grp'=>$rec->id_aclsbjgrp,
                'label'=>$this->getLabel($rec->id_aclsbj, $rec->label_key)
            );
            if ($rec->id_aclsbjgrp && !isset($sbjgroups_localized[$rec->id_aclsbjgrp])) {
                $sbjgroups_localized[$rec->id_aclsbjgrp] = $this->getLabel($rec->id_aclsbjgrp, $rec->label_group_key);
            }
            if (!isset($rightsWithResources[$rec->id_aclsbj]))
                $rightsWithResources[$rec->id_aclsbj] = array();
        }

        // retrieve existing rights
        $rs = jDao::get('jacl2db~jacl2rights','jacl2_profile')->getRightsByGroups($gid);
        foreach($rs as $rec){
            $rights[$rec->id_aclsbj][$rec->id_aclgrp] = ($rec->canceled?'n':'y');
        }

        return compact('groups', 'rights', 'sbjgroups_localized', 'subjects', 'rightsWithResources');
    }

    /**
     * @return array
     *      'subjects_localized' : list of labels of each subject
     *      'rightsWithResources':  array(<subject> => array( <jacl2rights objects (id_aclsbj, id_aclgrp, id_aclres, canceled>))
     *      'hasRightsOnResources' : true if there are some resources
     */
    public function getGroupRightsWithResources($groupid) {
        $rightsWithResources = array();
        $daorights = jDao::get('jacl2db~jacl2rights','jacl2_profile');

        $rs = $daorights->getRightsHavingRes($groupid);
        $hasRightsOnResources = false;
        foreach($rs as $rec){
            if (!isset($rightsWithResources[$rec->id_aclsbj])) {
                $rightsWithResources[$rec->id_aclsbj] = array();
            }
            $rightsWithResources[$rec->id_aclsbj][] = $rec;
            $hasRightsOnResources = true;
        }
        $subjects_localized = array();
        if(!empty($rightsWithResources)){
            $conditions = jDao::createConditions();
            $conditions->addCondition('id_aclsbj', 'in', array_keys($rightsWithResources));
            foreach(jDao::get('jacl2db~jacl2subject','jacl2_profile')->findBy($conditions) as $rec)
                $subjects_localized[$rec->id_aclsbj] = $this->getLabel($rec->id_aclsbj, $rec->label_key);
        }
        return compact('subjects_localized', 'rightsWithResources', 'hasRightsOnResources');
    }


    /**
     * @param array $rights
     *      array(<id_aclgrp> => array( <id_aclsbj> => (bool, 'y', 'n' or '')))
     */
    public function saveGroupRights($rights) {

        foreach(jAcl2DbUserGroup::getGroupList() as $grp) {
            $id = $grp->id_aclgrp;
            jAcl2DbManager::setRightsOnGroup($id, (isset($rights[$id])?$rights[$id]:array()));
        }

        jAcl2DbManager::setRightsOnGroup('__anonymous', (isset($rights['__anonymous'])?$rights['__anonymous']:array()));
    }

    /**
     * @param string $groupid
     * @param array $subjects array( <id_aclsbj> => (bool, 'y', 'n' or ''))
     */
    public function removeGroupRightsWithResources($groupid, $subjects) {
        $subjectsToRemove = array();

        foreach($subjects as $sbj=>$val) {
            if ($val != '' || $val == true) {
                $subjectsToRemove[] = $sbj;
            }
        }

        jDao::get('jacl2db~jacl2rights', 'jacl2_profile')
            ->deleteRightsOnResource($groupid, $subjectsToRemove);
    }
}
