<?php
/**
* @package     jelix-modules
* @subpackage  acl
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class AclService {


    function getGroupRights($grpid){
        $rv_dao = jDao::get('acl~jaclrightvalues');

        $sql = 'SELECT s.id_aclsbj, s.id_aclvalgrp, s.label_key, r.value, r.id_aclres
                FROM jacl_subject s LEFT JOIN jacl_rights r
                ON (s.id_aclsbj = r.id_aclsbj AND r.id_aclgrp = '.intval($grpid).')';
        $cnx = jDb::getConnection();

        $list=array();
        $rs = $cnx->query($sql);
        foreach($rs as $right){
            if($right->label_key !='')
                $right->label= jLocale::get($right->label_key);
            else
                $right->label = $right->id_aclsbj;
            $right->value_label='';
            if($right->value){
                $values= $rv_dao->findByValGroup($right->id_aclvalgrp);
                $v=intval($right->value);
                foreach($values as $value){
                    $r=intval($value->value);
                    if(($r & $v) == $r){
                        $right->value_label.=' '.jLocale::get($value->label_key);
                    }
                }
            }
            $list[]=$right;
        }
        return $list;
     }


}

?>