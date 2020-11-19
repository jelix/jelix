<?php
/**
 * @package    jelix
 * @subpackage db
 *
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
class jDbUtils
{
    const DB_TYPE_MYSQL = 'mysql';
    const DB_TYPE_SQLITE = 'sqlite';
    const DB_TYPE_PGSQL = 'pgsql';
    const DB_TYPE_SQLSERVER = 'sqlsrv';
    const DB_TYPE_ORACLE = 'oci';

    public static function getTools($dbType, $connection = null)
    {
        switch ($dbType) {
            case self::DB_TYPE_MYSQL:
                require_once __DIR__.'/tools/jDbMysqlTools.php';
                $tools = new jDbMysqlTools($connection);

                break;
            case self::DB_TYPE_SQLITE:
                require_once __DIR__.'/tools/jDbSqliteTools.php';
                $tools = new jDbSqliteTools($connection);

                break;
            case self::DB_TYPE_PGSQL:
                require_once __DIR__.'/tools/jDbPgsqlTools.php';
                $tools = new jDbPgsqlTools($connection);

                break;
            case self::DB_TYPE_SQLSERVER:
                require_once __DIR__.'/tools/jDbSqlsrvTools.php';
                $tools = new jDbSqlsrvTools($connection);

                break;
            case self::DB_TYPE_ORACLE:
                require_once __DIR__.'/tools/jDbOciTools.php';
                $tools = new jDbOciTools($connection);

                break;
            default:
                // legacy tools
                $tools = jApp::loadPlugin($dbType, 'db', '.dbtools.php', $dbType.'DbTools', $connection);
        }

        return $tools;
    }
}
