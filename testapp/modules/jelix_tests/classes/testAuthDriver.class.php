<?php

class testAuthDriverUser extends jAuthUser {
   public $password;
}


class testAuthDriver implements jIAuthDriverClass {
    /**
    * save a new user
    * @param object $user user informations
    */
    public function insert($user) {
        
    }

    /**
    * delete a user
    * @param string $login login of the user to delete
    */
    public function deleteByLogin($login) {
        
    }

    /**
    * update user informations
    * @param object $user user informations
    */
    public function update($user) {
        
    }

    /**
    * get user informations
    * @param string $login login of the user on which we want to get informations
    * @return object user informations
    */
    public function getByLogin($login) {
        $user = new testAuthDriverUser();
        $user->login = $login;
        $user->password = md5('foo');
        return $user;
    }

    /**
    * create an empty object which will contains user informations
    * @return object user informations (empty)
    */
    public function createUserObject() {
        return new testAuthDriverUser();
    }

    /**
    * gets all users
    * @return array list of users
    */
    public function findAll() {
        return array();
    }

    /**
    * gets all users for which the login corresponds to the given pattern
    * @param string $pattern the pattern
    * @return array list of users
    */
    public function findByLoginPattern($pattern) {
        return array();
    }

    /**
    * change the password of a user
    * @param string $login the user login
    * @param string $password the new encrypted password
    */
    public function updatePassword($login, $cryptedpassword) {
        
    }

    /**
    * get the user corresponding to the given login and encrypted password
    * @param string $login the user login
    * @param string $password the new encrypted password
    * @return object user informations
    */
    public function getByLoginPassword($login, $cryptedpassword) {
        $user = new testAuthDriverUser();
        $user->login = $login;
        $user->password = $cryptedpassword;
        return $user;
    }
}
