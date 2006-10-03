<?php
/**
* @package    jelix
* @subpackage auth
* @version    $Id:$
* @author     Laurent Jouanneau
* @contributor
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
* @package    jelix
* @subpackage auth
*/
class jAuthDriverDb implements jIAuthDriver {

    protected $_params;

    function __construct($params){
        $this->_params = $params;
    }

    public function saveNewUser($user){
        $dao = jDao::get($this->_params['dao']);
        $dao->insert($user);
        return true;
    }

    public function removeUser($login){
        $dao = jDao::get($this->_params['dao']);
        $dao->deleteByLogin($login);
        return true;
    }

    public function updateUser($user){
        $dao = jDao::get($this->_params['dao']);
        $dao->update($user);
        return true;
    }

    public function getUser($login){
        $dao = jDao::get($this->_params['dao']);
        return $dao->getByLogin($login);
    }

    public function createUser($login,$password){
        $user = jDao::createRecord($this->_params['dao']);
        $user->login = $login;
        $user->password = $this->cryptPassword($password);
        return $user;
    }

    public function getUserList($pattern){
        $dao = jDao::get($this->_params['dao']);
        if($pattern == '%' || $pattern == ''){
            return $dao->findAll();
        }else{
            return $dao->findByLogin($pattern);
        }
    }

    public function changePassword($login, $newpassword){
        $dao = jDao::get($this->_params['dao']);
        return $dao->updatePassword($login, $this->cryptPassword($newpassword));
    }

    public function verifyPassword($login, $password){
        $daouser = jDao::get($this->_params['dao']);
        $user = $daouser->getByLoginPassword($login, $this->cryptPassword($password));
        return ($user?$user:false);
    }


    protected function cryptPassword($password){
        $f=$this->_params['password_crypt_function'];
        if( $f != '')
           $password = $f($password);
        return $password;
    }
}
?>