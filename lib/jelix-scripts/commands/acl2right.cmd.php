<?php
/**
* @package     jelix-scripts
* @author      Jouanneau Laurent
* @contributor 
* @copyright   2007-2008 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class acl2rightCommand extends JelixScriptCommand {

    public  $name = 'aclright';
    public  $allowed_options=array('-defaultgroup'=>false);
    public  $allowed_parameters=array('action'=>true,'...'=>false);

    public  $syntaxhelp = " ACTION [arg1 [arg2 ...]]";
    public  $help=array(
        'fr'=>"
jAcl2 : gestion des droits

ACTION:
 * list
 * add  groupid sujet [resource]
 * [-allres] remove  groupid sujet [resource]
 * subject_create subject labelkey
 * subject_delete subject 
 * subject_list
",
        'en'=>"
jAcl2: rights management

ACTION:
 * list
 * add  groupid sujet [resource]
 * [-allres] remove  groupid sujet [resource]
 * subject_create subject labelkey
 * subject_delete subject 
 * subject_list
",
    );

    protected $titles = array(
        'fr'=>array(
            'list'=>"Liste des droits",
            'add'=>"ajout d'un droit",
            'remove'=>"retire un droit",
            'subject_create'=>"Création d'un sujet",
            'subject_delete'=>"Effacement d'un sujet",
            'subject_list'=>"Liste des sujets",
            ),
        'en'=>array(
            'list'=>"rights list",
            'add'=>"add a right",
            'remove'=>"remove a right",
            'subject_create'=>"Create a subject",
            'subject_delete'=>"Delete a subject",
            'subject_list'=>"List of subjects",
            ),
    );


    public function run(){
        jxs_init_jelix_env();
        $action = $this->getParam('action');
        if(!in_array($action,array('list','add','remove','subject_create','subject_delete','subject_list'))){
            die("unknow subcommand\n");
        }

        $meth= 'cmd_'.$action;
        echo "----", $this->titles[MESSAGE_LANG][$action],"\n\n";
        $this->$meth();
    }


    protected function cmd_list(){
        echo "group\tsubject\t\tresource\n---------------------------------------------------------------\n";
        echo "- anonymous group\n";
        $sql="SELECT r.id_aclgrp, r.id_aclsbj, r.id_aclres, s.label_key as subject
                FROM jacl2_rights r, jacl2_subject s
                WHERE r.id_aclgrp = 0 AND r.id_aclsbj=s.id_aclsbj
                ORDER BY subject, id_aclres ";
        $cnx = jDb::getConnection(jAclDb::getProfil());
        $rs = $cnx->query($sql);
        $sbj =-1;
        foreach($rs as $rec){
            if($sbj !=$rec->id_aclsbj){
                $sbj = $rec->id_aclsbj;
                echo "\t",$rec->id_aclsbj,"\n";
            }
            echo "\t\t",$rec->id_aclres,"\n";
        }

        $sql="SELECT r.id_aclgrp, r.id_aclsbj, r.id_aclres, name as grp, s.label_key as subject
                FROM jacl2_rights r, jacl2_group g, jacl2_subject s
                WHERE r.id_aclgrp = g.id_aclgrp AND r.id_aclsbj=s.id_aclsbj
                ORDER BY grp, subject, id_aclres ";
        $cnx = jDb::getConnection(jAclDb::getProfil());
        $rs = $cnx->query($sql);
        $grp=-1;
        $sbj =-1;
        foreach($rs as $rec){
            if($grp != $rec->id_aclgrp){
                echo "- group ", $rec->grp, ' (', $rec->id_aclgrp,")\n";
                $grp = $rec->id_aclgrp;
                $sbj = -1;
            }

            if($sbj !=$rec->id_aclsbj){
                $sbj = $rec->id_aclsbj;
                echo "\t",$rec->id_aclsbj,"\n";
            }
            echo "\t\t",$rec->id_aclres,"\n";
        }
    }

    protected function cmd_add(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) <2 || count($params) >3)
            die("wrong parameter count\n");

        $cnx = jDb::getConnection(jAclDb::getProfil());

        $group = $this->_getGrpId($params[0]);

        $subject=$cnx->quote($params[1]);
        if(isset($params[2]))
            $resource = $cnx->quote($params[2]);
        else
            $resource = $cnx->quote('');

        $sql="SELECT * FROM jacl2_rights 
                WHERE id_aclgrp=".$group."
                AND id_aclsbj=".$subject."
                AND id_aclres=".$resource;
        $rs = $cnx->query($sql);
        if($rs->fetch()){
            die("Error: right already set\n");
        }

        $sql="SELECT * FROM jacl2_subject WHERE id_aclsbj=".$subject;
        $rs = $cnx->query($sql);
        if(!($sbj = $rs->fetch())){
            die("Error: subject is unknown\n");
        }

        $sql="INSERT into jacl2_rights (id_aclgrp, id_aclsbj, id_aclres) VALUES (";
        $sql.=$group.',';
        $sql.=$subject.',';
        $sql.=$resource.')';

        $cnx->exec($sql);
        echo "OK.\n";
    }

    protected function cmd_remove(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) <2 || count($params) >3)
            die("wrong parameter count\n");

         $cnx = jDb::getConnection(jAclDb::getProfil());

        $group = $this->_getGrpId($params[0]);
        $subject=$cnx->quote($params[1]);
        if(isset($params[2]))
            $resource = $cnx->quote($params[2]);
        else
            $resource = '';

        $sql="SELECT * FROM jacl2_rights 
                WHERE id_aclgrp=".$group."
                AND id_aclsbj=".$subject;
        if($resource)
            $sql.=" AND id_aclres=".$resource;

        $rs = $cnx->query($sql);
        if(!$rs->fetch()){
            die("Error: this right is not set\n");
        }

        $sql="DELETE FROM jacl_rights
             WHERE id_aclgrp=".$group."
                AND id_aclsbj=".$subject;
        if($resource)
            $sql.=" AND id_aclres=".$resource;
        $cnx->exec($sql);

        echo "OK\n";
    }

    protected function cmd_subject_list(){

        $sql="SELECT id_aclsbj, label_key FROM jacl2_subject ORDER BY id_aclsbj";
        $cnx = jDb::getConnection(jAclDb::getProfil());
        $rs = $cnx->query($sql);
        echo "id\t\t\tlabel key\n--------------------------------------------------------\n";
        foreach($rs as $rec){
            echo $rec->id_aclsbj,"\t",$rec->label_key,"\n";
        }
    }

    protected function cmd_subject_create(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 2)
            die("wrong parameter count\n");

        $cnx = jDb::getConnection(jAclDb::getProfil());
 
        $sql="SELECT id_aclsbj FROM jacl2_subject WHERE id_aclsbj=".$cnx->quote($params[0]);
        $rs = $cnx->query($sql);
        if($rs->fetch()){
            die("Error: this subject already exists\n");
        }


        $sql="INSERT into jacl2_subject (id_aclsbj, label_key) VALUES (";
        $sql.=$cnx->quote($params[0]).',';
        $sql.=$cnx->quote($params[1]).')';
        $cnx->exec($sql);

        echo "OK.\n";
    }

    protected function cmd_subject_delete(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 1)
            die("wrong parameter count\n");

        $cnx = jDb::getConnection(jAclDb::getProfil());

        $sql="SELECT id_aclsbj FROM jacl2_subject WHERE id_aclsbj=".$cnx->quote($params[0]);
        $rs = $cnx->query($sql);
        if(!$rs->fetch()){
            die("Error: this subject does not exists\n");
        }

        $sql="DELETE FROM jacl2_rights WHERE id_aclsbj=";
        $sql.=$cnx->quote($params[0]);
        $cnx->exec($sql);

        $sql="DELETE FROM jacl2_subject WHERE id_aclsbj=";
        $sql.=$cnx->quote($params[0]);
        $cnx->exec($sql);

        echo "OK\n";
    }

    private function _getGrpId($param, $onlypublic=false){
        if($onlypublic)
            $c = ' grouptype <2 AND ';
        else $c='';

        $cnx = jDb::getConnection(jAclDb::getProfil());
        if(is_numeric($param)){
            if($param == '0')
                return 0;
            $sql="SELECT id_aclgrp FROM jacl2_group WHERE $c id_aclgrp = ".$param;
        }else{
            if($param =='anonymous')
                return 0;
            $sql="SELECT id_aclgrp FROM jacl2_group WHERE $c name = ".$cnx->quote($param);
        }
        $rs = $cnx->query($sql);
        if($rec = $rs->fetch()){
            return $rec->id_aclgrp;
        }else{
            die("Error: this group doesn't exist or is private\n");
        }
    }

}

