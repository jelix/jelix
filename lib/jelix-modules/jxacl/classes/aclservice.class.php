<?php
/**
* @package     jelix-modules
* @subpackage  jxacl
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class AclService {


    function getGroupRights($grpid){
        $rv_dao = jDao::get('jelix~jaclrightvalues');
        $sql = 'SELECT s.id_aclsbj, s.id_aclvalgrp, s.label_key, r.value as right_value, r.id_aclres, 
                        rv.label_key label_value, rv.value value
                FROM jacl_right_values rv
                   INNER JOIN jacl_subject s  ON rv.id_aclvalgrp = s.id_aclvalgrp 
                   LEFT JOIN jacl_rights r
                    ON (s.id_aclsbj = r.id_aclsbj AND r.id_aclgrp ='.intval($grpid).' AND rv.value=r.value)
                ORDER BY s.id_aclsbj';
        $cnx = jDb::getConnection();

        $list=array();
        $rs = $cnx->query($sql);
        $currentSubject=null;
        foreach($rs as $right){
            if($currentSubject==null || $right->id_aclsbj != $currentSubject->id_aclsbj){
                $currentSubject = new stdClass();
                $currentSubject->id_aclsbj = $right->id_aclsbj;
                $currentSubject->id_aclvalgrp = $right->id_aclvalgrp;

                if($right->label_key !='')
                    $currentSubject->label= jLocale::get($right->label_key);
                else
                    $currentSubject->label = $right->id_aclsbj;
                $currentSubject->rights=array();
                $list[]=$currentSubject;
            }

            $r = new stdClass();
            $r->value = $right->value;
            $r->label = jLocale::get($right->label_value);
            $r->id_aclres = $right->id_aclres;
            $r->enabled = ($right->value == $right->right_value?'true':'false');

            $currentSubject->rights[] = $r;
        }

        return $list;
     }


}

?>