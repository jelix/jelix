<?php
/**
* @package    jelix
* @subpackage ldap_driver
* @author     Tahina Ramaroson
* @contributor Sylvain de Vathaire
* @copyright  2009 Neov
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/


/**
* LDAP authentification driver for authentification information stored in LDAP server
* @package    jelix
* @subpackage auth_driver
*/
class ldapAuthDriver implements jIAuthDriver {

    /**
    * default user attributes list
    * @var array
    * @access proteced
    */
    protected $_default_attributes = array("cn","distinguishedName","name");


    protected $_params;

    function __construct($params){

        if (!extension_loaded('ldap')) {
            throw new jException('jelix~auth.ldap.extension.unloaded');
        }

        $this->_params = $params;

        if (!isset($this->_params['hostname']) || $this->_params['hostname'] == '') {
            $this->_params['hostname'] = 'localhost';
        }

        if (!isset($this->_params['port']) || $this->_params['port'] == '') {
            $this->_params['port'] = 389;
        }

        if (!isset($this->_params['ldapUser']) || $this->_params['ldapUser'] == '') {
            $this->_params['ldapUser'] = null;
        }

        if (!isset($this->_params['ldapPassword']) || $this->_params['ldapPassword'] == '') {
            $this->_params['ldapPassword'] = null;
        }

        if (!isset($this->_params['searchBaseDN']) || $this->_params['searchBaseDN'] == '') {
            throw new jException('jelix~auth.ldap.search.base.missing');
        }

        if (!isset($this->_params['searchFilter']) || $this->_params['searchFilter'] == '') {
            throw new jException('jelix~auth.ldap.search.filter.missing');
        }

        if (!isset($this->_params['searchAttributes']) || $this->_params['searchAttributes'] == '') {
            $this->_params['searchAttributes'] = $this->_default_attributes;
        } else {
            $this->_params['searchAttributes'] = explode(",", $this->_params['searchAttributes']);
        }

    }

    public function saveNewUser($user){

        if (!is_object($user) || !($user instanceof jAuthUserLDAP)) {
            throw new jException('jelix~auth.ldap.object.user.unknown');
        }

        if (!($user->login != '')) {
            throw new jException('jelix~auth.ldap.user.login.unset');
        }

        $entries = $this->getAttributesLDAP($user);

        $connect = ldap_connect($this->_params['hostname'],$this->_params['port']);
        $result = false;
        if($connect){
            ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);

            if(ldap_bind($connect, $this->_params['ldapUser'], $this->_params['ldapPassword'])) {
                $result = ldap_add($connect, 'cn='.$user->login.','.$this->_params['searchBaseDN'], $entries);
            }
            ldap_close($connect);
        }

        return $result;

    }

    public function removeUser($login){

        $connect = ldap_connect($this->_params['hostname'], $this->_params['port']);
        $result = false;
        if ($connect) {

            ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);

            if (ldap_bind($connect, $this->_params['ldapUser'], $this->_params['ldapPassword'])) {
                $result = ldap_delete($connect, 'cn='.$user->login.','.$this->_params['searchBaseDN']);
            }
            ldap_close($connect);
        }
        return $result;
    }

    public function updateUser($user){

        if (!is_object($user) || !($user instanceof jAuthUserLDAP)) {
            throw new jException('jelix~auth.ldap.object.user.unknown');
        }

        if (!($user->login != '')) {
            throw new jException('jelix~auth.ldap.user.login.unset');
        }

        $entries=$this->getAttributesLDAP($user,true);

        $connect = ldap_connect($this->_params['hostname'], $this->_params['port']);
        $result = false;
        if ($connect) {
            ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);

            if (ldap_bind($connect,$this->_params['ldapUser'], $this->_params['ldapPassword'])) {
                $result = ldap_modify($connect, 'cn='.$user->login.','.$this->_params['searchBaseDN'], $entries);
            }
            ldap_close($connect);
        }

        return $result;
    }

    public function getUser($login){

        $connect = ldap_connect($this->_params['hostname'], $this->_params['port']);

        if($connect){
            ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);

            if(ldap_bind($connect, $this->_params['ldapUser'], $this->_params['ldapPassword'])) {
                if (($search = ldap_search($connect, $this->_params['searchBaseDN'], "cn=".$login,$this->_params['searchAttributes']))) {
                    if (($entry = ldap_first_entry($connect, $search))) {
                        $attributes = ldap_get_attributes($connect, $entry);
                        if($attributes['count']>0){
                            $user = new jAuthUserLDAP();
                            $this->setAttributesLDAP($user, $attributes);
                            $user->login = $login;
                            $user->password = '';
                            ldap_close($connect);
                            return $user;
                        }
                    }
                }
            }
            ldap_close($connect);
        }

        return false;
    }

    public function createUserObject($login,$password){

        $user = new jAuthUserLDAP();

        $user->login = $login;
        $user->password = $this->cryptPassword($password);
        foreach ($this->_params['searchAttributes'] as $property) {
            $user->$property = '';
        }

        return $user;
    }

    public function getUserList($pattern){

        $users = array();

        $connect = ldap_connect($this->_params['hostname'], $this->_params['port']);

        if ($connect) {
            ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);

            if (ldap_bind($connect, $this->_params['ldapUser'], $this->_params['ldapPassword'])) {
                $filter = ($pattern != '' && $pattern != '%') ? "(&".$this->_params['searchFilter'] . "(cn={$pattern}))" : $this->_params['searchFilter'] ;
                
                if (($search = ldap_search($connect, $this->_params['searchBaseDN'], $filter, $this->_params['searchAttributes']))) {
                    ldap_sort($connect, $search,"cn");
                    $entry = ldap_first_entry($connect, $search);
                    while ($entry) {
                        $attributes = ldap_get_attributes($connect, $entry);
                        if ($attributes['count']>0) {
                            $user = new jAuthUserLDAP();
                            $this->setAttributesLDAP($user, $attributes);
                            $user->password = '';
                            $users[] = $user;
                        }
                        $entry = ldap_next_entry($connect, $entry);
                    }
                }
            }
            ldap_close($connect);
        }

        return $users;
    }

    public function changePassword($login, $newpassword) {

        $entries = array();
        $entries["userpassword"][0] = $this->cryptPassword($newpassword);

        $connect = ldap_connect($this->_params['hostname'], $this->_params['port']);
        $result = false;
        if ($connect) {
            ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);

            if (ldap_bind($connect,$this->_params['ldapUser'], $this->_params['ldapPassword'])) {
                $result = ldap_mod_replace($connect, 'cn='.$user->login.','.$this->_params['searchBaseDN'], $entries);
            }
            ldap_close($connect);
        }

        return $result;
    }

    public function verifyPassword($login, $password) {

        $connect = ldap_connect($this->_params['hostname'], $this->_params['port']);

        if ($connect) {

            ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);

            //authenticate user
            $bind = @ldap_bind($connect, "CN=".$login.",".$this->_params['searchBaseDN'], $this->cryptPassword($password));

            if ($bind) {
                //get connected user infos
                if (ldap_bind($connect,$this->_params['ldapUser'], $this->_params['ldapPassword'])) {
                    if (($search = ldap_search($connect, $this->_params['searchBaseDN'], "cn=".$login,$this->_params['searchAttributes']))) {
                        if (($entry = ldap_first_entry($connect,$search))) {
                            $attributes = ldap_get_attributes($connect,$entry);
                            if($attributes['count']>0){
                                $user = new jAuthUserLDAP();
                                $this->setAttributesLDAP($user, $attributes);
                                $user->login = $login;
                                $user->password = '';
                                ldap_close($connect);
                                return $user;
                            }
                        }
                    }
                }
            }
            ldap_close($connect);
        }
        return false;
    }

    protected function getAttributesLDAP($user, $update=false) {

        $entries = array();
        $entries["objectclass"][0] = "user";
        $properties = get_object_vars($user);
        foreach ($properties as $property=>$value) {
            switch(strtolower($property)) {
                case 'login':
                    if (!$update) {
                        $entries["cn"][0] = $value;
                        $entries["name"][0] = $value;
                    }
                    break;
                case 'password':
                    if ($value != '') {
                        $entries["userpassword"][0] = $value;
                    }
                    break;
                case 'email':
                    if ($value != '') {
                        $entries["mail"][0] = $value;
                    }
                    break;
                default:
                    if ($value != '') {
                        $entries[$property][0] = $value;
                    }
                    break;
            }
        }
        return $entries;
    }

    protected function setAttributesLDAP(&$user, $attributes) {

        foreach($this->_params['searchAttributes'] as $attribute) {
            if (isset($attributes[$attribute])) {
                array_shift($attributes[$attribute]);
                switch(strtolower($attribute)) {
                    case 'mail':
                        $user->email = $attributes[$attribute];
                        break;
                    case 'cn':
                        $user->login = $attributes[$attribute];
                    default:
                        $user->$attribute = $attributes[$attribute];
                        break;
                }
            }
        }
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
