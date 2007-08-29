<?php
/**
* @package     jelix-scripts
* @author      Jouanneau Laurent
* @contributor 
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class aclgroupCommand extends JelixScriptCommand {

    public  $name = 'aclgroup';
    public  $allowed_options=array('-defaultgroup'=>false);
    public  $allowed_parameters=array('action'=>true,'...'=>false);

    public  $syntaxhelp = " ACTION [arg1 [arg2 ...]]";
    public  $help=array(
        'fr'=>"
jAcl : gestion des groupes d'utilisateurs

ACTION:
 * list
    liste les groupes d'utilisateurs
 * [-defaultgroup] create  nom
     créer un groupe. Si il y a l'option -defaultgroup, ce nouveau
     groupe sera un groupe par defaut pour les nouveaux utilisateurs
 * setdefault groupid [true|false]
    fait du groupe indiqué un groupe par defaut (ou n'est plus
    un groupe par defaut si false est indiqué)
 * changename groupid nouveaunom
    change le nom d'un groupe
 * delete   groupid
    efface un groupe
",
        'en'=>"
jAcl: user group management

ACTION:
 * list    
    list users groups
 * [-defaultgroup] create name
     create a group. If there is -defaultgroup option, this new group
     become a defautl group for new users
 * setdefault groupid [true|false]
    the given group become a default group or not
    become a default group if false is given
 * changename groupid newname
    change a group name
 * delete   groupid
    delete a group
",
    );

    protected $titles = array(
        'fr'=>array(
            'list'=>"Liste des groupes d'utilisateurs",
            'create'=>"Création d'un nouveau groupe",
            'setdefault'=>"Change la propriété 'defaut' d'un groupe",
            'changename'=>"Change le nom d'un groupe",
            'delete'=>"Efface un groupe d'utilisateurs",
            ),
        'en'=>array(
            'list'=>"List of users groups",
            'create'=>"Create a new group",
            'setdefault'=>"Change the 'default' property of a group",
            'changename'=>"Change the name of a group",
            'delete'=>"Delete a group",
            ),
    );


    public function run(){
        jxs_init_jelix_env();
        $action = $this->getParam('action');
        if(!in_array($action,array('list','create','setdefault','changename','delete'))){
            die("unknow subcommand\n");
        }

        $meth= 'cmd_'.$action;
        echo "----", $this->titles[MESSAGE_LANG][$action],"\n\n";
        $this->$meth();
    }


    protected function cmd_list(){
        $sql="SELECT id_aclgrp, name, grouptype FROM jacl_group WHERE grouptype <2 ORDER BY name";
        $cnx = jDb::getConnection(jAclDb::getProfil());
        $rs = $cnx->query($sql);
        echo "id\tlabel name\t\tdefault\n--------------------------------------------------------\n";
        foreach($rs as $rec){
            if($rec->grouptype==1)
                $type='yes';
            else
                $type='';
            echo $rec->id_aclgrp,"\t",$rec->name,"\t\t",$type,"\n";
        }
    }

    protected function cmd_create(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 1)
            die("wrong parameter count\n");

        $cnx = jDb::getConnection(jAclDb::getProfil());

        $sql="INSERT into jacl_group (name, grouptype, ownerlogin) VALUES (";
        $sql.=$cnx->quote($params[0]).',';
        if($this->getOption('-defaultgroup'))
            $sql.='1, NULL)';
        else
            $sql.='0, NULL)';

        $cnx->exec($sql);
        $id = $cnx->lastInsertId();
        echo "OK. Group id is: ".$id."\n";
    }

    protected function cmd_delete(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 1)
            die("wrong parameter count\n");

        $cnx = jDb::getConnection(jAclDb::getProfil());

        $sql="SELECT id_aclgrp,  grouptype FROM jacl_group WHERE id_aclgrp=".intval($params[0]);
        $rs = $cnx->query($sql);
        if($rec = $rs->fetch()){
            if($rec->grouptype == 2)
                die("Error: can't delete this private group\n");
        }else{
            die("Error: this group doesn't exist\n");
        }


        $sql="DELETE FROM jacl_rights WHERE id_aclgrp=";
        $sql.=intval($params[0]);
        $cnx->exec($sql);

        $sql="DELETE FROM jacl_user_group WHERE id_aclgrp=";
        $sql.=intval($params[0]);
        $cnx->exec($sql);

        $sql="DELETE FROM jacl_group WHERE id_aclgrp=";
        $sql.=intval($params[0]);
        $cnx->exec($sql);

        echo "OK\n";
    }


    protected function cmd_setdefault(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) == 0 || count($params) > 2)
            die("wrong parameter count\n");

        $cnx = jDb::getConnection(jAclDb::getProfil());

        $sql="SELECT id_aclgrp,  grouptype FROM jacl_group WHERE id_aclgrp=".intval($params[0]);
        $rs = $cnx->query($sql);
        if($rec = $rs->fetch()){
            if($rec->grouptype == 2)
                die("Error: can't change this private group\n");
        }else{
            die("Error: this group doesn't exist\n");
        }

        $def=1;
        if(isset($params[1])){
            if($params[1]=='false')
                $def=0;
            elseif($params[1]=='true')
                $def=1;
            else
                die("error: bad value for last parameter\n");
        }

        $sql="UPDATE jacl_group SET grouptype=$def  WHERE id_aclgrp=".intval($params[0]);
        $cnx->exec($sql);
        echo "OK\n";
    }

    protected function cmd_changename(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 2)
            die("wrong parameter count\n");

        $cnx = jDb::getConnection(jAclDb::getProfil());

        $sql="SELECT id_aclgrp,  grouptype FROM jacl_group WHERE id_aclgrp=".intval($params[0]);
        $rs = $cnx->query($sql);
        if($rec = $rs->fetch()){
            if($rec->grouptype == 2)
                die("Error: can't change this private group\n");
        }else{
            die("Error: this group doesn't exist\n");
        }

        $sql="UPDATE jacl_group SET name=".$cnx->quote($params[1])."  WHERE id_aclgrp=".intval($params[0]);
        $cnx->exec($sql);
        echo "OK\n";
    }
}

?>