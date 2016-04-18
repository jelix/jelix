<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2007-2012 Laurent Jouanneau, 2008 Loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class acl2rightCommand extends JelixScriptCommand {

    public  $name = 'aclright';
    public  $allowed_options=array('-defaultgroup'=>false);
    public  $allowed_parameters=array('action'=>true,'...'=>false);

    public  $help=array(
        'en'=>"
jAcl2: rights management

ACTION:
 * subject_group_list
 * subject_group_create group labelkey
 * subject_group_delete group labelkey

 labelkey is a jLocale selector of a label.
",
    );

    protected $titles = array(
        'en'=>array(
            'subject_group_list'=>"List of subject groups",
            'subject_group_create'=>"Add a subject group",
            'subject_group_delete'=>"Delete a subject group",
            ),
    );


    public function run(){
        $this->loadAppConfig();
        $action = $this->getParam('action');
        if(!in_array($action,array('list','add','remove',
                                   'subject_create','subject_delete','subject_list',
                                   'subject_group_create','subject_group_delete','subject_group_list',
                                   ))){
            throw new Exception("unknown subcommand");
        }

        $meth= 'cmd_'.$action;
        echo "----", $this->titles[$this->config->helpLang][$action],"\n\n";
        $this->$meth();
    }


    protected function cmd_subject_group_list(){
        $cnx = jDb::getConnection('jacl2_profile');
        $sql="SELECT id_aclsbjgrp, label_key FROM "
           .$cnx->prefixTable('jacl2_subject_group')." ORDER BY id_aclsbjgrp";
        $rs = $cnx->query($sql);
        $group = '';
        echo "id\t\t\tlabel key\n--------------------------------------------------------\n";
        foreach($rs as $rec){
            echo $rec->id_aclsbjgrp,"\t",$rec->label_key,"\n";
        }
    }

    protected function cmd_subject_group_create(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 2)
            throw new Exception("wrong parameter count");

        $cnx = jDb::getConnection('jacl2_profile');

        $sql="SELECT id_aclsbjgrp FROM ".$cnx->prefixTable('jacl2_subject_group')
            ." WHERE id_aclsbjgrp=".$cnx->quote($params[0]);
        $rs = $cnx->query($sql);
        if($rs->fetch()){
            throw new Exception("This subject group already exists");
        }

        $sql="INSERT into ".$cnx->prefixTable('jacl2_subject_group')." (id_aclsbjgrp, label_key) VALUES (";
        $sql.=$cnx->quote($params[0]).',';
        $sql.=$cnx->quote($params[1]);
        $sql .= ')';
        $cnx->exec($sql);

        if ($this->verbose())
            echo "Rights: group '".$params[0]."' of subjects is created.\n";
    }

    protected function cmd_subject_group_delete(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 1)
            throw new Exception("wrong parameter count");

        $cnx = jDb::getConnection('jacl2_profile');

        $sql="SELECT id_aclsbjgrp FROM ".$cnx->prefixTable('jacl2_subject_group')
            ." WHERE id_aclsbjgrp=".$cnx->quote($params[0]);
        $rs = $cnx->query($sql);
        if (!$rs->fetch()) {
            throw new Exception("This subject group does not exist");
        }

        $sql="UDPATE ".$cnx->prefixTable('jacl2_rights')." SET id_aclsbjgrp=NULL WHERE id_aclsbjgrp=";
        $sql.=$cnx->quote($params[0]);
        $cnx->exec($sql);

        $sql="DELETE FROM ".$cnx->prefixTable('jacl2_subject_group')." WHERE id_aclsbjgrp=";
        $sql.=$cnx->quote($params[0]);
        $cnx->exec($sql);

        if ($this->verbose())
            echo "Rights: group '".$params[0]."' of subjects is deleted.\n";
    }



    private function _getGrpId($param, $onlypublic=false){
        if ($param == '__anonymous')
            return $param;

        if($onlypublic) {
            $c = ' grouptype <2 AND ';
        }
        else $c='';

        $cnx = jDb::getConnection('jacl2_profile');
        $sql="SELECT id_aclgrp FROM ".$cnx->prefixTable('jacl2_group')." WHERE $c ";
        $sql .= " id_aclgrp = ".$cnx->quote($param);
        $rs = $cnx->query($sql);
        if($rec = $rs->fetch()){
            return $rec->id_aclgrp;
        }else{
            throw new Exception("this group doesn't exist or is private");
        }
    }

}
