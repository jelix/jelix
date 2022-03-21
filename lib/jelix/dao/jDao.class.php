<?php
/**
 * @package    jelix
 * @subpackage dao
 *
 * @author     Laurent Jouanneau
 * @copyright   2005-2014 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
require_once JELIX_LIB_PATH.'db/jDb.class.php';

require_once JELIX_LIB_PATH.'dao/jDaoRecordBase.class.php';

require_once JELIX_LIB_PATH.'dao/jDaoFactoryBase.class.php';

/**
 * Factory to create DAO objects.
 *
 * @package  jelix
 * @subpackage dao
 */
class jDao
{
    /**
     * creates a new instance of a DAO.
     * If no dao is founded, try to compile a DAO from the dao xml file.
     *
     * @param jSelectorDao|string $Daoid   the dao selector
     * @param string              $profile the db profile name to use for the connection.
     *                                     If empty, use the default profile
     * @param mixed               $DaoId
     *
     * @return jDaoFactoryBase the dao object
     */
    public static function create($DaoId, $profile = '')
    {
        if (is_string($DaoId)) {
            $DaoId = new jSelectorDao($DaoId, $profile);
        }

        $c = $DaoId->getDaoClass();
        if (!class_exists($c, false)) {
            jIncluder::inc($DaoId);
        }
        $conn = jDb::getConnection($profile);

        return new $c($conn);
    }

    protected static $_daoSingleton = array();

    /**
     * return a DAO instance. It Handles a singleton of the DAO.
     * If no dao is founded, try to compile a DAO from the dao xml file.
     *
     * @param jSelectorDao|string $Daoid   the dao selector
     * @param string              $profile the db profile name to use for the connection.
     *                                     If empty, use the default profile
     * @param mixed               $DaoId
     *
     * @return jDaoFactoryBase the dao object
     */
    public static function get($DaoId, $profile = '')
    {
        $sel = new jSelectorDao($DaoId, $profile);
        $DaoId = $sel->toString().'#'.$profile;

        if (!isset(self::$_daoSingleton[$DaoId])) {
            self::$_daoSingleton[$DaoId] = self::create($sel, $profile);
        }

        return self::$_daoSingleton[$DaoId];
    }

    /**
     * Release dao singleton own by jDao. Internal use.
     *
     * @internal
     *
     * @since 1.3
     */
    public static function releaseAll()
    {
        self::$_daoSingleton = array();
    }

    /**
     * creates a record object for the given dao.
     *
     * See also jDaoFactoryBase::createRecord()
     *
     * @param string $Daoid   the dao selector
     * @param string $profile the db profile name to use for the connection.
     *                        If empty, use the default profile
     * @param mixed  $DaoId
     *
     * @return jDaoRecordBase a dao record object
     */
    public static function createRecord($DaoId, $profile = '')
    {
        $sel = new jSelectorDao($DaoId, $profile);
        $c = $sel->getDaoClass();
        if (!class_exists($c, false)) {
            jIncluder::inc($sel);
        }
        $c = $sel->getDaoRecordClass();
        /** @var jDaoRecordBase $rec */
        $rec = new $c();
        $rec->setDbProfile($profile);

        return $rec;
    }

    /**
     * return an instance of a jDaoConditions object, to use with
     * a findby method of a jDaoFactoryBase object.
     *
     * @param string $glueOp value should be AND or OR
     *
     * @return jDaoConditions
     *
     * @see jDaoFactoryBase::findby
     */
    public static function createConditions($glueOp = 'AND')
    {
        return new jDaoConditions($glueOp);
    }
}
