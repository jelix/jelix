<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2007-2008 Laurent Jouanneau, 2008 Loic Mathaud
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
 * add groupid sujet [resource]
 * [-allres] remove groupid sujet [resource]
 * subject_create subject labelkey
 * subject_delete subject 
 * subject_list
",
        'en'=>"
jAcl2: rights management

ACTION:
 * list
 * add  groupid subject [resource]
 * [-allres] remove groupid subject [resource]
 * subject_create subject labelkey
 * subject_delete subject 
 * subject_list
",
    );

    protected $titles = array(
        'fr'=>array(
            'list'=>"Liste des droits",
            'add'=>"Ajout d'un droit",
            'remove'=>"Retire un droit",
            'subject_create'=>"Création d'un sujet",
            'subject_delete'=>"Effacement d'un sujet",
            'subject_list'=>"Liste des sujets",
            ),
        'en'=>array(
            'list'=>"Rights list",
            'add'=>"Add a right",
            'remove'=>"Remove a right",
            'subject_create'=>"Create a subject",
            'subject_delete'=>"Delete a subject",
            'subject_list'=>"List of subjects",
            ),
    );


    public function run(){
        jxs_init_jelix_env();
        $action = $this->getParam('action');
        if(!in_array($action,array('list','add','remove','subject_create','subject_delete','subject_list'))){
            throw new Exception("unknown subcommand");
        }

        $meth= 'cmd_'.$action;
        echo "----", $this->titles[MESSAGE_LANG][$action],"\n\n";
        $this->$meth();
    }


    protected function cmd_list(){
        echo "group\tsubject\t\tresource\n---------------------------------------------------------------\n";
        echo "- anonymous group\n";

        $cnx = jDb::getConnection('jacl2_profile');

        $sql="SELECT r.id_aclgrp, r.id_aclsbj, r.id_aclres, s.label_key as subject
                FROM ".$cnx->prefixTable('jacl2_rights')." r,
                ".$cnx->prefixTable('jacl2_subject')." s
                WHERE r.id_aclgrp = 0 AND r.id_aclsbj=s.id_aclsbj
                ORDER BY subject, id_aclres ";
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
                FROM ".$cnx->prefixTable('jacl2_rights')." r,
                ".$cnx->prefixTable('jacl2_group')." g,
                ".$cnx->prefixTable('jacl2_subject')." s
                WHERE r.id_aclgrp = g.id_aclgrp AND r.id_aclsbj=s.id_aclsbj
                ORDER BY grp, subject, id_aclres ";

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
            throw new Exception("wrong parameter count");

        $cnx = jDb::getConnection('jacl2_profile');

        $group = $this->_getGrpId($params[0]);

        $subject=$cnx->quote($params[1]);
        if(isset($params[2]))
            $resource = $cnx->quote($params[2]);
        else
            $resource = $cnx->quote('');

        $sql="SELECT * FROM ".$cnx->prefixTable('jacl2_rights')."
                WHERE id_aclgrp=".$group."
                AND id_aclsbj=".$subject."
                AND id_aclres=".$resource;
        $rs = $cnx->query($sql);
        if($rs->fetch()){
            throw new Exception("right already sets");
        }

        $sql="SELECT * FROM ".$cnx->prefixTable('jacl2_subject')." WHERE id_aclsbj=".$subject;
        $rs = $cnx->query($sql);
        if(!($sbj = $rs->fetch())){
            throw new Exception("subject is unknown");
        }

        $sql="INSERT into ".$cnx->prefixTable('jacl2_rights')
            ." (id_aclgrp, id_aclsbj, id_aclres) VALUES (";
        $sql.=$group.',';
        $sql.=$subject.',';
        $sql.=$resource.')';

        $cnx->exec($sql);
        echo "OK.\n";
    }

    protected function cmd_remove(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) <2 || count($params) >3)
            throw new Exception("wrong parameter count");

         $cnx = jDb::getConnection('jacl2_profile');

        $group = $this->_getGrpId($params[0]);
        $subject=$cnx->quote($params[1]);
        if(isset($params[2]))
            $resource = $cnx->quote($params[2]);
        else
            $resource = '';

        $sql="SELECT * FROM ".$cnx->prefixTable('jacl2_rights')."
                WHERE id_aclgrp=".$group."
                AND id_aclsbj=".$subject;
        if($resource)
            $sql.=" AND id_aclres=".$resource;

        $rs = $cnx->query($sql);
        if(!$rs->fetch()){
            throw new Exception("Error: this right is not set");
        }

        $sql="DELETE FROM ".$cnx->prefixTable('jacl2_rights')."
             WHERE id_aclgrp=".$group."
                AND id_aclsbj=".$subject;
        if($resource)
            $sql.=" AND id_aclres=".$resource;
        $cnx->exec($sql);

        echo "OK\n";
    }

    protected function cmd_subject_list(){

        $cnx = jDb::getConnection('jacl2_profile');
        $sql="SELECT id_aclsbj, label_key FROM "
           .$cnx->prefixTable('jacl2_subject')." ORDER BY id_aclsbj";
        $rs = $cnx->query($sql);
        echo "id\t\t\tlabel key\n--------------------------------------------------------\n";
        foreach($rs as $rec){
            echo $rec->id_aclsbj,"\t",$rec->label_key,"\n";
        }
    }

    protected function cmd_subject_create(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 2)
            throw new Exception("wrong parameter count");

        $cnx = jDb::getConnection('jacl2_profile');
 
        $sql="SELECT id_aclsbj FROM ".$cnx->prefixTable('jacl2_subject')
            ." WHERE id_aclsbj=".$cnx->quote($params[0]);
        $rs = $cnx->query($sql);
        if($rs->fetch()){
            throw new Exception("This subject already exists");
        }

        $sql="INSERT into ".$cnx->prefixTable('jacl2_subject')." (id_aclsbj, label_key) VALUES (";
        $sql.=$cnx->quote($params[0]).',';
        $sql.=$cnx->quote($params[1]).')';
        $cnx->exec($sql);

        echo "OK.\n";
    }

    protected function cmd_subject_delete(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 1)
            throw new Exception("wrong parameter count");

        $cnx = jDb::getConnection('jacl2_profile');

        $sql="SELECT id_aclsbj FROM ".$cnx->prefixTable('jacl2_subject')
            ." WHERE id_aclsbj=".$cnx->quote($params[0]);
        $rs = $cnx->query($sql);
        if (!$rs->fetch()) {
            throw new Exception("This subject does not exist");
        }

        $sql="DELETE FROM ".$cnx->prefixTable('jacl2_rights')." WHERE id_aclsbj=";
        $sql.=$cnx->quote($params[0]);
        $cnx->exec($sql);

        $sql="DELETE FROM ".$cnx->prefixTable('jacl2_subject')." WHERE id_aclsbj=";
        $sql.=$cnx->quote($params[0]);
        $cnx->exec($sql);

        echo "OK\n";
    }

    private function _getGrpId($param, $onlypublic=false){
        if($onlypublic)
            $c = ' grouptype <2 AND ';
        else $c='';

        $cnx = jDb::getConnection('jacl2_profile');
        $sql="SELECT id_aclgrp FROM ".$cnx->prefixTable('jacl2_group')." WHERE $c ";
        if (is_numeric($param)) {
            if($param == '0')
                return 0;
            $sql .= " id_aclgrp = ".$param;
        } else {
            if($param =='anonymous')
                return 0;
            $sql .= " name = ".$cnx->quote($param);
        }
        $rs = $cnx->query($sql);
        if($rec = $rs->fetch()){
            return $rec->id_aclgrp;
        }else{
            throw new Exception("this group doesn't exist or is private");
        }
    }

}

