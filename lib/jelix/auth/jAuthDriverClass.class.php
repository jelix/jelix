<?php
/**
* @package    jelix
* @subpackage auth
* @author     Laurent Jouanneau
* @contributor Yannick Le Guédart (adaptation de jAuthDriverDb pour une classe quelconque)
* @copyright  2006 Laurent Jouanneau
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/


/**
* Driver for a class which implement an authentification
* @package    jelix
* @subpackage auth
* @see jAuth
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
        $user->password = $this->cryptPassword($password);
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
        return $class->updatePassword($login, $this->cryptPassword($newpassword));
    }

    public function verifyPassword($login, $password){
        $classuser = jClasses::create($this->_params['class']);

        $user = $classuser->getByLoginPassword($login, $this->cryptPassword($password));

        return ($user?$user:false);
    }

    protected function cryptPassword($password){
        if(isset($this->_params['password_crypt_function'])){
            $f=$this->_params['password_crypt_function'];
            if( $f != '')
               $password = $f($password);
        }
        return $password;
    }
}
?>