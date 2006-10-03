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
* framework Copix 2.3dev. http://www.copix.org (jAuth)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteur initial : Laurent Jouanneau
* Adaptée pour Jelix par Laurent Jouanneau
*/

/**
 * interface for auth drivers
 * @package    jelix
 * @subpackage auth
 */
interface jIAuthDriver {
    /**
     * constructor
     * @param array $params driver parameters, written in the ini file of the auth plugin
     */
    function __construct($params);

    /**
     * creates a new user object, with some first datas..
     * @param string $login the user login
     * @param string $password the user password
     * @return jAuthUser|object the returned object depends on the driver
     */
    public function createUser($login, $password);

    /**
    * store a new user.
    *
    * should be call after a call of createUser and after settinfg some of its properties...
    * @param jAuthUser|object $user the user data container
    */
    public function saveNewUser($user);

    /**
     * Erase user datas of the user $login
     * @param string $login the login of the user to remove
     */
    public function removeUser($login);

    /**
    * save updated datas of a user
    * warning : should not save the password !
    * @param jAuthUser|object $user the user data container
    */
    public function updateUser($user);

    /**
     * return user data corresponding to the given login
     * @param string $login the login of the user
     * @return jAuthUser|object the user data container
     */
    public function getUser($login);

    /**
     * construct the user list
     * @param string $pattern '' for all users
     * @return array array of jAuthUser|object
     */
    public function getUserList($pattern);

    /**
     * change a user password
     *
     * @param string $login the login of the user
     * @param string $newpassword
     */
    public function changePassword($login, $newpassword);

    /**
     * verify that the password correspond to the login
     * @param string $login the login of the user
     * @param string $password the password to test
     * @return boolean
     */
    public function verifyPassword($login, $password);
}



/**
 * This is the main class for authentification process
 * @package    jelix
 * @subpackage auth
 */
class jAuth {

    protected static function  _getConfig(){
        static $config = null;
        if($config == null){
            global $gJCoord;
            $plugin = $gJCoord->getPlugin('auth');
            if($plugin === null){
                trigger_error(jLocale::get('jelix~auth.error.plugin.missing'), E_USER_ERROR);
                return null;
            }
            $config = & $plugin->config;
        }
        return $config;
    }


    protected static function _getDriver(){
        static $driver = null;
        if($driver == null){
              $config = self::_getConfig();
              $dname = 'jAuthDriver'.$config['driver'];
              require_once(JELIX_LIB_AUTH_PATH.$dname.'.class.php');
              $driver = new $dname($config[$config['driver']]);
        }
        return $driver;
    }

    /**
     *
     */
    public static function saveNewUser($user){
        $dr = self::_getDriver();
        if($dr->saveNewUser($user)){
            jEvent::notify ('AuthNewUser', array('user'=>$user));
        }
        return $user;
    }

    /**
     *
     */
    public static function removeUser($login){
        $dr = self::_getDriver();
        $eventresp = jEvent::notify ('AuthCanRemoveUser', array('login'=>$login));
        foreach($eventresp->getResponse() as $rep){
            if(!isset($rep['canremove']) || $rep['canremove'] === false){
                return false;
            }
        }
        jEvent::notify ('AuthRemoveUser', array('login'=>$login));
        return $dr->removeUser($login);
    }

    /**
     *
     */
    public static function updateUser(&$user){
        $dr = self::_getDriver();
        if($user = $dr->updateUser($user)){
            jEvent::notify ('AuthUpdateUser', array('user'=>$user));
        }
    }


    /**
     *
     */
    public static function getUser($login){
        $dr = self::_getDriver();
        return $dr->getUser($login);
    }

    /**
     *
     */
    public static function createUser($login,$password){
        $dr = self::_getDriver();
        return $dr->createUser($login,$password);
    }


    /**
     *
     */
    public static function getUserList($pattern = '%'){
        $dr = self::_getDriver();
        return $dr->getUserlist($pattern);
    }

    /**
     *
     */
    public static function changePassword($login, $newpassword){
        $dr = self::_getDriver();
        return $dr->changePassword($login, $newpassword);
    }

    /**
     *
     */
    public static function verifyPassword($login, $password){
        $dr = self::_getDriver();
        return $dr->verifyPassword($login, $password);
    }

    /**
     *
     */
    public static function login($login, $password){

        $dr = self::_getDriver();
        if($user = $dr->verifyPassword($login, $password)){

            $eventresp = jEvent::notify ('AuthCanLogin', array('login'=>$login, 'user'=>$user));
            foreach($eventresp->getResponse() as $rep){
                if(!isset($rep['canlogin']) || $rep['canlogin'] === false){
                    return false;
                }
            }

            $_SESSION['JELIX_USER'] = $user;
            jEvent::notify ('AuthLogin', array('login'=>$login));
            return true;
        }else
            return false;
    }

    /**
     *
     */
    public static function logout(){
        jEvent::notify ('AuthLogout', array('login'=>$_SESSION['JELIX_USER']->login));
        $_SESSION['JELIX_USER'] = new jAuthUser();
    }

    /**
     * Says if the user is connected
     */
    public static function isConnected(){
        return (isset($_SESSION['JELIX_USER']) && $_SESSION['JELIX_USER']->login != '');
    }

   /**
    * Récupération de l'objet utilisateur.
    */
    public static function getUserSession (){
      if (! isset ($_SESSION['JELIX_USER'])){
            $_SESSION['JELIX_USER'] = new jAuthUser();
      }
      return $_SESSION['JELIX_USER'];
    }

}
?>
