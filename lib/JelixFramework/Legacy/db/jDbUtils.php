<?php
/**
 * @package    jelix
 * @subpackage db
 *
 * @author     Laurent Jouanneau
 * @copyright  2019-2021 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * @deprecated use \Jelix\Database\Connection::getTools() instead
 * @see \Jelix\Database\Connection::getTools()
 */
class jDbUtils
{
    /** @deprecated use \Jelix\Database\Connection::DB_TYPE_MYSQL instead */
    const DB_TYPE_MYSQL = 'mysql';
    /** @deprecated use \Jelix\Database\Connection::DB_TYPE_SQLITE instead */
    const DB_TYPE_SQLITE = 'sqlite';
    /** @deprecated use \Jelix\Database\Connection::DB_TYPE_PGSQL instead */
    const DB_TYPE_PGSQL = 'pgsql';
    /** @deprecated use \Jelix\Database\Connection::DB_TYPE_SQLSERVER instead */
    const DB_TYPE_SQLSERVER = 'sqlsrv';
    /** @deprecated use \Jelix\Database\Connection::DB_TYPE_ORACLE instead */
    const DB_TYPE_ORACLE = 'oci';

    /**
     * @deprecated use \Jelix\Database\Connection::getTools() instead
     * @see \Jelix\Database\Connection::getTools()
     */
    public static function getTools($dbType, $connection = null)
    {
        return \Jelix\Database\Connection::getTools($dbType, $connection);
    }
}
