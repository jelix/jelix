<?php
/**
* @package     jelix-scripts
* @author      Jouanneau Laurent
* @contributor Julien Issler
* @copyright   2007-2008 Jouanneau laurent
* @copyright   2008 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class acl2groupCommand extends JelixScriptCommand {

    public  $name = 'acl2group';
    public  $allowed_options=array('-defaultgroup'=>false);
    public  $allowed_parameters=array('action'=>true,'...'=>false);

    public  $syntaxhelp = " ACTION [arg1 [arg2 ...]]";
    public  $help=array(
        'fr'=>"
jAcl2 : gestion des groupes d'utilisateurs

ACTION:
 * list
    liste les groupes d'utilisateurs
 * userslist  groupid
    liste les utilisateurs d'un groupe
 * alluserslist
    liste tout les utilisateurs inscrits
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
 * createuser login
    créé un utilisateur et son groupe privé
 * adduser groupid login
    ajoute un utilisateur dans un groupe
 * removeuser login groupid
    enlève un utilisateur d'un groupe
",
        'en'=>"
jAcl2: user group management

ACTION:
 * list    
    list users groups
 * userslist groupid
    list of users of a group
 * alluserslist
    list all users
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
 * createuser login
    add a user and and it's private group
 * adduser groupid login
    add a user in a group
 * removeuser groupid login
    remove a user from a group
",
    );

    protected $titles = array(
        'fr'=>array(
            'list'=>"Liste des groupes d'utilisateurs",
            'create'=>"Création d'un nouveau groupe",
            'setdefault'=>"Change la propriété 'defaut' d'un groupe",
            'changename'=>"Change le nom d'un groupe",
            'delete'=>"Efface un groupe d'utilisateurs",
            'userslist'=>"Liste des utilisateurs d'un groupe",
            'alluserslist'=>"Liste de tous les utilisateurs",
            'adduser'=>"Ajoute un utilisateur",
            'removeuser'=>"Enlève un utilisateur",
            'createuser'=>"Créé un user dans jAcl2",
            'destroyuser'=>"Enlève un user de jAcl2",
            ),
        'en'=>array(
            'list'=>"List of users groups",
            'create'=>"Create a new group",
            'setdefault'=>"Change the 'default' property of a group",
            'changename'=>"Change the name of a group",
            'delete'=>"Delete a group",
            'userslist'=>"List of user of a group",
            'alluserslist'=>"All registered users",
            'adduser'=>"Add a user in a group",
            'removeuser'=>"Remove a user from a group",
            'createuser'=>"Create a user in jAcl2",
            'destroyuser'=>"Remove a user from jAcl2",
            ),
    );


    public function run(){
        jxs_init_jelix_env();
        $action = $this->getParam('action');
        if(!in_array($action,array('list','create','setdefault','changename',
            'delete','userslist','alluserslist','adduser','removeuser','createuser','destroyuser'))){
            die("unknow subcommand\n");
        }

        $meth= 'cmd_'.$action;
        echo "----", $this->titles[MESSAGE_LANG][$action],"\n\n";
        $this->$meth();
    }


    protected function cmd_list(){
        $sql="SELECT id_aclgrp, name, grouptype FROM jacl2_group WHERE grouptype <2 ORDER BY name";
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

    protected function cmd_userslist(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 1)
            die("wrong parameter count\n");

        $id = $this->_getGrpId($params[0]);

        $cnx = jDb::getConnection(jAclDb::getProfil());
        $sql = "SELECT login FROM jacl2_user_group WHERE id_aclgrp =".$id;
        $rs = $cnx->query($sql);
        echo "Login\n-------------------------\n";
        foreach($rs as $rec){
            echo $rec->login,"\n";
        }
    }

    protected function cmd_alluserslist(){
        $sql="SELECT login, u.id_aclgrp, name FROM jacl2_user_group u, jacl2_group g 
            WHERE g.grouptype <2 AND u.id_aclgrp = g.id_aclgrp ORDER BY login";

        $cnx = jDb::getConnection(jAclDb::getProfil());
        $rs = $cnx->query($sql);
        echo "Login\t\tgroups\n--------------------------------------------------------\n";
        $login = '';
        foreach($rs as $rec){
            if($login != $rec->login) {
                echo "\n", $rec->login,"\t\t";
                $login = $rec->login;
            }
            echo $rec->name," ";
        }
        echo "\n\n";
    }


    protected function cmd_create(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 1)
            die("wrong parameter count\n");

        $cnx = jDb::getConnection(jAclDb::getProfil());

        $sql="INSERT into jacl2_group (name, grouptype, ownerlogin) VALUES (";
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

        if($params[0] != 0)
            $id = $this->_getGrpId($params[0]);

        $sql="DELETE FROM jacl2_rights WHERE id_aclgrp=";
        $sql.=intval($id);
        $cnx->exec($sql);

        $sql="DELETE FROM jacl2_user_group WHERE id_aclgrp=";
        $sql.=intval($id);
        $cnx->exec($sql);

        $sql="DELETE FROM jacl2_group WHERE id_aclgrp=";
        $sql.=intval($id);
        $cnx->exec($sql);

        echo "OK\n";
    }


    protected function cmd_setdefault(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) == 0 || count($params) > 2)
            die("wrong parameter count\n");

        $cnx = jDb::getConnection(jAclDb::getProfil());

        $id = $this->_getGrpId($params[0]);

        $def=1;
        if(isset($params[1])){
            if($params[1]=='false')
                $def=0;
            elseif($params[1]=='true')
                $def=1;
            else
                die("error: bad value for last parameter\n");
        }

        $sql="UPDATE jacl2_group SET grouptype=$def  WHERE id_aclgrp=".$id;
        $cnx->exec($sql);
        echo "OK\n";
    }

    protected function cmd_changename(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 2)
            die("wrong parameter count\n");

        $id = $this->_getGrpId($params[0]);

        $cnx = jDb::getConnection(jAclDb::getProfil());
        $sql="UPDATE jacl2_group SET name=".$cnx->quote($params[1])."  WHERE id_aclgrp=".$id;
        $cnx->exec($sql);
        echo "OK\n";
    }

    protected function cmd_adduser(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 2)
            die("wrong parameter count\n");

        $id = $this->_getGrpId($params[0]);

        $cnx = jDb::getConnection(jAclDb::getProfil());
        $sql = "SELECT * FROM jacl2_user_group WHERE login= ".$cnx->quote($params[1])." AND id_aclgrp = $id";
        $rs = $cnx->query($sql);
        if($rec = $rs->fetch()){
             die("Error: the user is already in this group\n");
        }

        $sql = "SELECT * FROM  jacl2_user_group u, jacl2_group g 
                WHERE u.id_aclgrp = g.id_aclgrp AND login= ".$cnx->quote($params[1])." AND grouptype = 2";
        $rs = $cnx->query($sql);
        if(! ($rec = $rs->fetch())){
             die("Error: the user doesn't exist\n");
        }

        $sql="INSERT INTO jacl2_user_group (login, id_aclgrp) VALUES(".$cnx->quote($params[1]).", ".$id.")";
        $cnx->exec($sql);
        echo "OK\n";
    }

    protected function cmd_removeuser(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 2)
            die("wrong parameter count\n");

        $id = $this->_getGrpId($params[0]);

        $cnx = jDb::getConnection(jAclDb::getProfil());

        $sql="DELETE FROM jacl2_user_group WHERE login=".$cnx->quote($params[1])." AND id_aclgrp=$id";
        $cnx->exec($sql);
        echo "OK\n";
    }

    protected function cmd_createuser(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 1)
            die("wrong parameter count\n");

        $cnx = jDb::getConnection(jAclDb::getProfil());
        $login = $cnx->quote($params[0]);

        $sql = "SELECT * FROM jacl2_user_group WHERE login = $login";
        $rs = $cnx->query($sql);
        if($rec = $rs->fetch()){
             die("Error: the user is already registered\n");
        }

        $sql="INSERT into jacl2_group (name, grouptype, ownerlogin) VALUES (";
        $sql.=$login.',2, '.$login.')';
        $cnx->exec($sql);
        $id = $cnx->lastInsertId();

        $sql="INSERT INTO jacl2_user_group (login, id_aclgrp) VALUES(".$login.", ".$id.")";
        $cnx->exec($sql);
        echo "OK\n";
    }

    protected function cmd_destroyuser(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 1)
            die("wrong parameter count\n");

        $cnx = jDb::getConnection(jAclDb::getProfil());

        $sql="DELETE FROM jacl2_group WHERE grouptype=2 and ownerlogin=".$cnx->quote($params[0]);
        $cnx->exec($sql);

        $sql="DELETE FROM jacl2_user_group WHERE login=".$cnx->quote($params[0]);
        $cnx->exec($sql);
        echo "OK\n";
    }

    private function _getGrpId($param){
        $cnx = jDb::getConnection(jAclDb::getProfil());
        if(is_numeric($param)){
            if(intval($param) <= 0)
                die('Error: invalid group id');
            $sql="SELECT id_aclgrp FROM jacl2_group WHERE grouptype <2 AND id_aclgrp = ".$param;
        }else{
            $sql="SELECT id_aclgrp FROM jacl2_group WHERE grouptype <2 AND name = ".$cnx->quote($param);
        }
        $rs = $cnx->query($sql);
        if($rec = $rs->fetch()){
            return $rec->id_aclgrp;
        }else{
            die("Error: this group doesn't exist or is private\n");
        }
    }

}
