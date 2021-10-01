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
    const DB_TYPE_MYSQL = 'mysql';
    const DB_TYPE_SQLITE = 'sqlite';
    const DB_TYPE_PGSQL = 'pgsql';
    const DB_TYPE_SQLSERVER = 'sqlsrv';
    const DB_TYPE_ORACLE = 'oci';

    /**
     * @deprecated use \Jelix\Database\Connection::getTools() instead
     * @see \Jelix\Database\Connection::getTools()
     */
    public static function getTools($dbType, $connection = null)
    {
        $tools = \Jelix\Database\Connection::getTools($dbType, $connection);
        return $tools;
    }
}
