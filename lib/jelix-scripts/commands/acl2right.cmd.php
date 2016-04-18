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
 * subject_create subject labelkey [grouplabelkey [subjectlabel]]
    if subjectlabel is given, it will be stored in the properties file
    indicated by the labelkey.
 * subject_delete subject
 * subject_list
 * subject_group_list
 * subject_group_create group labelkey
 * subject_group_delete group labelkey

 labelkey is a jLocale selector of a label.
",
    );

    protected $titles = array(
        'en'=>array(
            'subject_create'=>"Create a subject",
            'subject_delete'=>"Delete a subject",
            'subject_list'=>"List of subjects",
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

    protected function cmd_subject_list(){

        $cnx = jDb::getConnection('jacl2_profile');
        $sql="SELECT id_aclsbj, s.label_key, s.id_aclsbjgrp, g.label_key as group_label_key FROM "
           .$cnx->prefixTable('jacl2_subject')." s
           LEFT JOIN ".$cnx->prefixTable('jacl2_subject_group')." g
           ON (s.id_aclsbjgrp = g.id_aclsbjgrp) ORDER BY s.id_aclsbjgrp, id_aclsbj";
        $rs = $cnx->query($sql);
        $group = '';
        echo "subject group\n\tid\t\t\tlabel key\n--------------------------------------------------------\n";
        foreach($rs as $rec){
            if ($rec->id_aclsbjgrp != $group) {
                echo $rec->id_aclsbjgrp."\n";
                $group = $rec->id_aclsbjgrp;
            }
            echo "\t".$rec->id_aclsbj,"\t",$rec->label_key,"\n";
        }
    }

    protected function cmd_subject_create(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) > 4  || count($params) < 2)
            throw new Exception("wrong parameter count");

        $cnx = jDb::getConnection('jacl2_profile');

        $sql="SELECT id_aclsbj FROM ".$cnx->prefixTable('jacl2_subject')
            ." WHERE id_aclsbj=".$cnx->quote($params[0]);
        $rs = $cnx->query($sql);
        if($rs->fetch()){
            throw new Exception("This subject already exists");
        }

        $sql="INSERT into ".$cnx->prefixTable('jacl2_subject')." (id_aclsbj, label_key, id_aclsbjgrp) VALUES (";
        $sql.=$cnx->quote($params[0]).',';
        $sql.=$cnx->quote($params[1]);
        if (isset($params[2]) && $params[2] != 'null')
            $sql.=','.$cnx->quote($params[2]);
        else
            $sql.=", NULL";
        $sql .= ')';
        $cnx->exec($sql);

        if ($this->verbose())
            echo "Rights: subject ".$params[0]." is created.";

        if (isset($params[3]) && preg_match("/^([a-zA-Z0-9_\.]+)~([a-zA-Z0-9_]+)\.([a-zA-Z0-9_\.]+)$/", $params[1], $m)) {
            $localestring = "\n".$m[3].'='.$params[3];
            $path = $this->getModulePath($m[1]);
            $file = $path.'locales/'.jApp::config()->locale.'/'.$m[2].'.'.jApp::config()->charset.'.properties';
            if (file_exists($file)) {
                $localestring = file_get_contents($file).$localestring;
            }
            file_put_contents($file, $localestring);
            if ($this->verbose())
                echo " and locale string ".$m[3]." is created into ".$file."\n";
        }
        else if ($this->verbose())
            echo "\n";
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

        if ($this->verbose())
            echo "Rights: subject ".$params[0]." is deleted\n";
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
