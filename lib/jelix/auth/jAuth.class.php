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
 * interface pour les classes de drivers d'authentification
 */

interface jIAuthDriver {

    function __construct($params);
    public function createUser($login, $password);
    public function removeUser($login);
    public function updateUser($user);
    public function getUser($login);
    public function getUserList($pattern);
    public function changePassword($login, $newpassword);
    public function verifyPassword($login, $password);
}



/**
* This is the main class for authentification process
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
    public static function createUser($login, $password){
        $dr = self::_getDriver();
        if($user = $dr->createUser($login, $password)){
            jEvent::notify ('AuthAddUser', array('login'=>$login));
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
    public function getUserList($pattern = '%'){
        $dr = self::_getDriver();
        return $dr->getUserlist($pattern);
    }

    /**
     *
     */
    public static function changePassword($login, $newpassword){
        $newpassword = self::cryptPassword($newpassword);
        $dr = self::_getDriver();
        return $dr->changePassword($login, $newpassword);
    }

    /**
     *
     */
    public static function verifyPassword($login, $password){
        $password = self::cryptPassword($password);
        $dr = self::_getDriver();
        return $dr->verifyPassword($login, $password);
    }

    /**
     *
     */
    public static function login($login, $password){

        $dr = self::_getDriver();
        $password = self::cryptPassword($password);
        if($user = $dr->verifyPassword($login, $password)){

            $eventresp = jEvent::notify ('AuthCanLogin', array('login'=>$login));
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
        $_SESSION['JELIX_USER'] = new jUser();
    }

    /**
     * Says if the user is connected
     */
    public static function isConnected(){
        return (isset($_SESSION['JELIX_USER']) && $_SESSION['JELIX_USER']->login != '');
    }

    public static function cryptPassword($password){
        $conf = self::_getConfig();
        $f=$conf['password_crypt_function'];
        if( $f != '')
           $password = $f($password);
        return $password;
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
