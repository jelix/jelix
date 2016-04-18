<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @contributor Loic Mathaud
* @copyright   2007-2011 Laurent Jouanneau
* @copyright   2008 Julien Issler
* @copyright   2008 Loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class acl2groupCommand extends JelixScriptCommand {

    public  $name = 'acl2group';
    public  $allowed_options=array('-defaultgroup'=>false);
    public  $allowed_parameters=array('action'=>true,'...'=>false);

    public  $syntaxhelp = " ACTION [arg1 [arg2 ...]]";
    public  $help=array(
        'en'=>"
jAcl2: user group management

ACTION:
 * createuser login
    add a user and its private group
 * adduser groupid login
    add a user in a group
 * removeuser groupid login
    remove a user from a group
",
    );

    protected $titles = array(
        'en'=>array(
            'adduser'=>"Add a user in a group",
            'removeuser'=>"Remove a user from a group",
            'createuser'=>"Create a user in jAcl2",
            'destroyuser'=>"Remove a user from jAcl2",
            ),
    );


    public function run(){
        $this->loadAppConfig();
        $action = $this->getParam('action');
        if(!in_array($action,array('list','create','setdefault','changename',
            'delete','userslist','alluserslist','adduser','removeuser','createuser','destroyuser'))){
            throw new Exception("unknown subcommand");
        }

        $meth= 'cmd_'.$action;
        echo "----", $this->titles[$this->config->helpLang][$action],"\n\n";
        $this->$meth();
    }

    protected function cmd_adduser(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 2)
            throw new Exception("wrong parameter count");

        $id = $this->_getGrpId($params[0]);

        $cnx = jDb::getConnection('jacl2_profile');

        $sql = "SELECT * FROM ".$cnx->prefixTable('jacl2_user_group')
            ." WHERE login= ".$cnx->quote($params[1])." AND id_aclgrp = $id";
        $rs = $cnx->query($sql);
        if($rec = $rs->fetch()){
             throw new Exception("The user is already in this group");
        }

        $sql = "SELECT * FROM  ".$cnx->prefixTable('jacl2_user_group')." u, "
                .$cnx->prefixTable('jacl2_group')." g
                WHERE u.id_aclgrp = g.id_aclgrp AND login= ".$cnx->quote($params[1])." AND grouptype = 2";
        $rs = $cnx->query($sql);
        if(! ($rec = $rs->fetch())){
             throw new Exception("The user doesn't exist");
        }

        $sql="INSERT INTO ".$cnx->prefixTable('jacl2_user_group')
            ." (login, id_aclgrp) VALUES(".$cnx->quote($params[1]).", ".$id.")";
        $cnx->exec($sql);
        if ($this->verbose())
            echo "Rights: user ".$params[1]." is added to the group $id\n";
    }

    protected function cmd_removeuser(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 2)
            throw new Exception("wrong parameter count");

        $id = $this->_getGrpId($params[0]);

        $cnx = jDb::getConnection('jacl2_profile');

        $sql="DELETE FROM ".$cnx->prefixTable('jacl2_user_group')
            ." WHERE login=".$cnx->quote($params[1])." AND id_aclgrp=$id";
        $cnx->exec($sql);
        if ($this->verbose())
            echo "Rights: user ".$params[1]." is removed from the group $id\n";
    }

    protected function cmd_createuser(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 1)
            throw new Exception("wrong parameter count");

        $cnx = jDb::getConnection('jacl2_profile');
        $login = $cnx->quote($params[0]);

        $sql = "SELECT * FROM ".$cnx->prefixTable('jacl2_user_group')
            ." WHERE login = $login";
        $rs = $cnx->query($sql);
        if($rec = $rs->fetch()){
            throw new Exception("the user is already registered");
        }

        $groupid = $cnx->quote('__priv_'.$params[0]);

        $sql = "INSERT into ".$cnx->prefixTable('jacl2_group')
            ." (id_aclgrp, name, grouptype, ownerlogin) VALUES (";
        $sql.= $groupid.','.$login.',2, '.$login.')';
        $cnx->exec($sql);

        $sql="INSERT INTO ".$cnx->prefixTable('jacl2_user_group')
            ." (login, id_aclgrp) VALUES(".$login.", ".$groupid.")";
        $cnx->exec($sql);
        if ($this->verbose())
            echo "Rights: user $login is added into rights system and has a private group $groupid\n";
    }

    protected function cmd_destroyuser(){
        $params = $this->getParam('...');
        if(!is_array($params) || count($params) != 1)
            throw new Exception("wrong parameter count");

        $cnx = jDb::getConnection('jacl2_profile');

        $sql="DELETE FROM ".$cnx->prefixTable('jacl2_group')
            ." WHERE grouptype=2 and ownerlogin=".$cnx->quote($params[0]);
        $cnx->exec($sql);

        $sql="DELETE FROM ".$cnx->prefixTable('jacl2_user_group')
            ." WHERE login=".$cnx->quote($params[0]);
        $cnx->exec($sql);
        if ($this->verbose())
            echo "Rights: user $login is removed from rights system.\n";
    }

    private function _getGrpId($param){
        $cnx = jDb::getConnection('jacl2_profile');
        $sql="SELECT id_aclgrp FROM ".$cnx->prefixTable('jacl2_group')
                ." WHERE grouptype <2 AND id_aclgrp = ".$cnx->quote($param);
        $rs = $cnx->query($sql);
        if($rec = $rs->fetch()){
            return $cnx->quote($rec->id_aclgrp);
        }else{
            throw new Exception("this group doesn't exist or is private");
        }
    }
}
