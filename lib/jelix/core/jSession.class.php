<?php
/**
* @package    jelix
* @subpackage core
* @author     Julien Issler
* @contributor
* @copyright  2007 Julien Issler
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.0
*/

/**
 * session management class of the jelix core
 *
 * @package  jelix
 * @subpackage core
 * @since 1.0
 */
class jSession {

    protected static $_params;

    /**
     * start a session
     */
    public static function start(){
        $params = $GLOBALS['gJConfig']->sessions;

        if(!isset($params['storage'])){
            session_start();
            return true;
        }

        switch($params['storage']){

            case 'dao':
                session_set_save_handler(
                    array(__CLASS__,'daoOpen'),
                    array(__CLASS__,'daoClose'),
                    array(__CLASS__,'daoRead'),
                    array(__CLASS__,'daoWrite'),
                    array(__CLASS__,'daoDestroy'),
                    array(__CLASS__,'daoGarbageCollector')
                );
                self::$_params = $params;
                break;

            case 'files':
                $path = str_replace(array('lib:','app:'), array(LIB_PATH, JELIX_APP_PATH), $params['files_path']);
                session_save_path($path);
                break;

            default:
                break;
        }

        session_start();
        return true;
    }

    /**
     * end a session
     */
    public static function end(){
        session_write_close();
        return true;
    }


    protected static function _getDao(){
        if(isset(self::$_params['dao_db_profile']) && self::$_params['dao_db_profile']){
            $dao = jDao::get(self::$_params['dao_selector'], self::$_params['dao_db_profile']);
        }
        else{
            $dao = jDao::get(self::$_params['dao_selector']);
        }
        return $dao;
    }

    /**
     * dao handler for session stored in database
     */
    public static function daoOpen ($save_path, $session_name) {
        return true;
    }

    /**
     * dao handler for session stored in database
     */
    public static function daoClose() {
        return true;
    }

    /**
     * dao handler for session stored in database
     */
    public static function daoRead ($id) {
        $session = self::_getDao()->get($id);

        if(!$session){
            return '';
        }

        return $session->data;
    }

    /**
     * dao handler for session stored in database
     */
    public static function daoWrite ($id, $data) {
        $dao = self::_getDao();

        $session = $dao->get($id);
        if(!$session){
            $session = jDao::createRecord(self::$_params['dao_selector']);
            $session->id = $id;
            $session->data = $data;
            $dao->insert($session);
        }
        else{
            $session->data = $data;
            $dao->update($session);
        }

        return true;
    }

    /**
     * dao handler for session stored in database
     */
    public static function daoDestroy ($id) {
        if (isset($_COOKIE[session_name()])) {
           setcookie(session_name(), '', time()-42000, '/');
        }

        self::_getDao()->delete($id);
        return true;
    }

    /**
     * dao handler for session stored in database
     */
    public static function daoGarbageCollector ($maxlifetime) {
        $date = new jDateTime();
        $date->now();
        $date->sub(0,0,0,0,0,$maxlifetime);
        self::_getDao()->deleteExpired($date->toString(jDateTime::BD_DTFORMAT));
        return true;
    }

}
?>