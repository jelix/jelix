<?php
/**
* @package     jelix-scripts
* @author      Jouanneau Laurent
* @contributor 
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class aclrightCommand extends JelixScriptCommand {

    public  $name = 'aclright';
    public  $allowed_options=array('-defaultgroup'=>false);
    public  $allowed_parameters=array('action'=>true,'...'=>false);

    public  $syntaxhelp = " ACTION [arg1 [arg2 ...]]";
    public  $help=array(
        'fr'=>"
jAcl : gestion des droits

ACTION:
 * list
 * add  groupid sujet value [resource]
 * [-allres] remove  groupid sujet value [resource]
 * subject_create subject labelkey valuegroup
 * subject_delete subject 
 * subject_list
",
        'en'=>"
jAcl: rights management

ACTION:
 * list
 * add  groupid sujet value [resource]
 * [-allres] remove  groupid sujet value [resource]
 * subject_create subject labelkey valuegroup
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
        $sql="SELECT r.id_aclgrp, r.id_aclsbj, r.id_aclres, r.value , name as grp, s.label_key as subject
                FROM jacl_rights r, jacl_group g, jacl_subject s
                WHERE 
                    r.id_aclgrp = g.id_aclgrp
                 AND r.id_aclsbj=s.id_aclsbj
                ORDER BY name, subject, value,id_aclres ";
        $cnx = jDb::getConnection(jAclDb::getProfil());
        $rs = $cnx->query($sql);
        echo "group\tsubject\tvalue\t\tresource\n---------------------------------------------------------------\n";
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
            echo "\t\t", $rec->value,"\t\t",$rec->id_aclres,"\n";
        }
    }

    protected function cmd_add(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) <3 || count($params) >4)
            die("wrong parameter count\n");

        $cnx = jDb::getConnection(jAclDb::getProfil());

        $group = intval($params[0]);
        $subject=$cnx->quote($params[1]);
        $value=$cnx->quote($params[2]);
        if(isset($params[3]))
            $resource = $cnx->quote($params[3]);
        else
            $resource = $cnx->quote('');

        $sql="SELECT * FROM jacl_rights 
                WHERE id_aclgrp=".$group."
                AND id_aclsbj=".$subject."
                AND id_aclres=".$resource."
                AND value=".$value;
        $rs = $cnx->query($sql);
        if($rs->fetch()){
            die("Error: right already set\n");
        }

        $sql="SELECT * FROM jacl_group WHERE id_aclgrp=".$group;
        $rs = $cnx->query($sql);
        if(!$rs->fetch()){
            die("Error: group is unknown\n");
        }
        $sql="SELECT * FROM jacl_subject WHERE id_aclsbj=".$subject;
        $rs = $cnx->query($sql);
        if(!($sbj = $rs->fetch())){
            die("Error: subject is unknown\n");
        }

        $sql='SELECT * FROM jacl_right_values
            WHERE id_aclvalgrp='.$sbj->id_aclvalgrp." AND value=".$value;
        $rs = $cnx->query($sql);
        if(!$rs->fetch()){
            die("Error: value is not allowed for this subject\n");
        }


        $sql="INSERT into jacl_rights (id_aclgrp, id_aclsbj, id_aclres, value) VALUES (";
        $sql.=$group.',';
        $sql.=$subject.',';
        $sql.=$resource.',';
        $sql.=$value.')';

        $cnx->exec($sql);
        echo "OK.\n";
    }

    protected function cmd_remove(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) <3 || count($params) >4)
            die("wrong parameter count\n");

         $cnx = jDb::getConnection(jAclDb::getProfil());

        $group = intval($params[0]);
        $subject=$cnx->quote($params[1]);
        $value=$cnx->quote($params[2]);
        if(isset($params[3]))
            $resource = $cnx->quote($params[3]);
        else
            $resource = '';

        $sql="SELECT * FROM jacl_rights 
                WHERE id_aclgrp=".$group."
                AND id_aclsbj=".$subject."
                AND value=".$value;
        if($resource)
            $sql.=" AND id_aclres=".$resource;

        $rs = $cnx->query($sql);
        if(!$rs->fetch()){
            die("Error: this right is not set\n");
        }

        $sql="DELETE FROM jacl_rights
             WHERE id_aclgrp=".$group."
                AND id_aclsbj=".$subject."
                AND value=".$value;
        if($resource)
            $sql.=" AND id_aclres=".$resource;
        $cnx->exec($sql);

        echo "OK\n";
    }

    protected function cmd_subject_list(){

        $sql="SELECT id_aclsbj, id_aclvalgrp, label_key FROM jacl_subject ORDER BY id_aclsbj";
        $cnx = jDb::getConnection(jAclDb::getProfil());
        $rs = $cnx->query($sql);
        echo "id\t\t\tlabel key\n--------------------------------------------------------\n";
        foreach($rs as $rec){
            echo $rec->id_aclsbj,"\t",$rec->label_key,"\n";
            $sqlval = "SELECT value FROM jacl_right_values WHERE id_aclvalgrp=".$rec->id_aclvalgrp." ORDER BY value";
            $rs2 = $cnx->query($sqlval);
            echo "\tpossible values: ";
            foreach($rs2 as $val){
                echo $val->value,' ';
            }
            echo "\n";
        }
    }

    protected function cmd_subject_create(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 3)
            die("wrong parameter count\n");

        $cnx = jDb::getConnection(jAclDb::getProfil());
 
        $sql="SELECT id_aclsbj FROM jacl_subject WHERE id_aclsbj=".$cnx->quote($params[0]);
        $rs = $cnx->query($sql);
        if($rs->fetch()){
            die("Error: this subject already exists\n");
        }

        $sql="SELECT * FROM jacl_right_values_group WHERE id_aclvalgrp=".intval($params[2]);
        $rs = $cnx->query($sql);
        if(!$rs->fetch()){
            die("Error: the given values group does not exist\n");
        }

        $sql="INSERT into jacl_subject (id_aclsbj, label_key, id_aclvalgrp) VALUES (";
        $sql.=$cnx->quote($params[0]).',';
        $sql.=$cnx->quote($params[1]).',';
        $sql.=intval($params[2]).')';
        $cnx->exec($sql);

        echo "OK.\n";
    }

    protected function cmd_subject_delete(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 1)
            die("wrong parameter count\n");

        $cnx = jDb::getConnection(jAclDb::getProfil());

        $sql="SELECT id_aclsbj FROM jacl_subject WHERE id_aclsbj=".$cnx->quote($params[0]);
        $rs = $cnx->query($sql);
        if(!$rs->fetch()){
            die("Error: this subject does not exists\n");
        }

        $sql="DELETE FROM jacl_rights WHERE id_aclsbj=";
        $sql.=$cnx->quote($params[0]);
        $cnx->exec($sql);

        $sql="DELETE FROM jacl_subject WHERE id_aclsbj=";
        $sql.=$cnx->quote($params[0]);
        $cnx->exec($sql);

        echo "OK\n";
    }

}

?>