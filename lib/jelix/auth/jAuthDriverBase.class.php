<?php
/**
* @package    jelix
* @subpackage auth_driver
* @author      Laurent Jouanneau
* @contributor Florian Lonqueu-Brochard
* @copyright   2011 Laurent Jouanneau, 2011 Florian Lonqueu-Brochard
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * base class for some jAuth drivers
 */
class jAuthDriverBase {

    protected $_params;

    function __construct($params){
        $this->_params = $params;
    }

    /**
     * crypt the password
     */
    public function cryptPassword($password) {
        if (isset($this->_params['password_crypt_function'])) {
            $f = $this->_params['password_crypt_function'];
            if ($f != '') {
                if ($f[1] == ':') {
                    $t = $f[0];
                    $f = substr($f, 2);
                    if ($t == '1') {
                        return $f((isset($this->_params['password_salt'])?$this->_params['password_salt']:''), $password);
                    }
                    else if ($t == '2') {
                        return $f($this->_params, $password);
                    }
                }
                return $f($password);
            }
        }
        return $password;
    }
}


/**
 * function to use to crypt password. use the password_salt value in the config
 * file of the plugin.
 */
function sha1WithSalt($salt, $password) {
    return sha1($salt.':'.$password);
}

/**
 * hash password with blowfish algorithm. use the password_salt value in the config file of the plugin
 */
function bcrypt($salt, $password, $iteration_count = 12) {
    
    if (CRYPT_BLOWFISH != 1)
        throw new jException('jelix~auth.error.bcrypt.inexistant');
    
    if(empty($salt) || !ctype_alnum($salt) || strlen($salt) != 22)
        throw new jException('jelix~auth.error.bcrypt.bad.salt');

    $hash = crypt($password, '$2a$'.$iteration_count.'$'.$salt.'$');
    
    return substr($hash, strrpos($hash, '$')+strlen($salt));
 
}
