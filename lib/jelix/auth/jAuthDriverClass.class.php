<?php
/**
* @package    jelix
* @subpackage auth
* @version    $Id:$
* @author     Laurent Jouanneau
* @contributor Yannick Le Guédart (adaptation de jAuthDriverDb pour une classe quelconque)
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Classe orginellement issue d'une branche experimentale du
* framework Copix 2.3dev. http://www.copix.org (CopixAuthDriverDb)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteur initial : Laurent Jouanneau
* Adaptée pour Jelix par Laurent Jouanneau
*/


/**
* driver base de donnée pour l'authentification
*/
class jAuthDriverClass implements jIAuthDriver {

    protected $_params;

    function __construct($params){
        $this->_params = $params;
    }

    public function saveNewUser($user){
        $class = jClasses::create($this->_params['class']);
        $class->insert($user);
        return true;
    }

    public function removeUser($login){
        $class = jClasses::create($this->_params['class']);
        $class->deleteByLogin($login);
        return true;
    }

    public function updateUser($user){
        $class = jClasses::create($this->_params['class']);
        $class->update($user);
        return true;
    }

    public function getUser($login){
        $class = jClasses::create($this->_params['class']);
        return $class->getByLogin($login);
    }

    public function createUser($login,$password){
        $user = jClasses::createRecord($this->_params['class']);
        $user->login = $login;
        $user->password = $password;
        return $user;
    }

    public function getUserList($pattern){
        $class = jClasses::create($this->_params['class']);
        if($pattern == '%' || $pattern == ''){
            return $class->findAll();
        }else{
            return $class->findByLogin($pattern);
        }
    }

    public function changePassword($login, $newpassword){
        $class = jClasses::create($this->_params['class']);
        return $class->updatePassword($login, $newpassword);
    }

    public function verifyPassword($login, $password){
        $classuser = jClasses::create($this->_params['class']);

        $user = $classuser->getByLoginPassword($login, $password);

        return ($user?$user:false);
    }

}
?>