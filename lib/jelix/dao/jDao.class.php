<?php
/**
* @package    jelix
* @subpackage dao
* @author     Laurent Jouanneau
* @contributor
* @copyright   2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 *
 */
require_once(JELIX_LIB_PATH.'db/jDb.class.php');
require_once(JELIX_LIB_PATH.'dao/jDaoRecordBase.class.php');
require_once(JELIX_LIB_PATH.'dao/jDaoFactoryBase.class.php');

/**
 * Factory to create DAO objects
 * @package  jelix
 * @subpackage dao
 */
class jDao {

    /**
    * creates a new instance of a DAO.
    * If no dao is founded, try to compile a DAO from the dao xml file
    * @param string|jSelectorDao $Daoid the dao selector
    * @param string $profil the db profil name to use for the connection. 
    *   If empty, use the default profil
    * @return jDaoFactoryBase  the dao object
    */
    public static function create ($DaoId, $profil=''){
        if(!is_object($DaoId))
            $sel = new jSelectorDao($DaoId, $profil);
        else $sel = $DaoId;

        $c = $sel->getDaoClass();
        if(!class_exists($c,false)){
            jIncluder::inc($sel);
        }
        $conn = jDb::getConnection ($profil);
        $obj = new $c ($conn);
        return $obj;
    }

    /**
    * return a DAO instance. It Handles a singleton of the DAO.
    * If no dao is founded, try to compile a DAO from the dao xml file
    * @param string|jSelectorDao $Daoid the dao selector
    * @param string $profil the db profil name to use for the connection. 
    *   If empty, use the default profil
    * @return jDaoFactoryBase  the dao object
    */
    public static function get ($DaoId, $profil='') {
       static $_daoSingleton=array();

       $sel = new jSelectorDao($DaoId, $profil);
       $DaoId = $sel->toString ();

        if (! isset ($_daoSingleton[$DaoId])){
            $_daoSingleton[$DaoId] = self::create ($sel,$profil);
        }
        return $_daoSingleton[$DaoId];
    }

    /**
    * creates a record object for the given dao
    * @param string $Daoid the dao selector
    * @param string $profil the db profil name to use for the connection. 
    *   If empty, use the default profil
    * @return jDaoRecordBase  a dao record object
    */
    public static function createRecord ($DaoId, $profil=''){
        $sel = new jSelectorDao($DaoId, $profil);
        $c = $sel->getDaoClass();
        if(!class_exists($c,false)){
            jIncluder::inc($sel);
        }
        $c = $sel->getDaoRecordClass();
        $obj = new $c();
        return $obj;
    }

    /**
     * return an instance of a jDaoConditions object, to use with
     * a findby method of a jDaoFactoryBase object.
     * @param string $glueOp value should be AND or OR
     * @return jDaoConditions
     * @see jDaoFactoryBase::findby
     */
    public static function createConditions ($glueOp = 'AND'){
        $obj = new jDaoConditions ($glueOp);
        return $obj;
    }
}
?>