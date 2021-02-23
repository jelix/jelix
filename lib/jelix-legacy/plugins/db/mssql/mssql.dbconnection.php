<?php
/**
 * @package    jelix
 * @subpackage db_driver
 *
 * @author     Yann Lecommandoux
 * @contributor Laurent Jouanneau, Louis S.
 *
 * @copyright  2008 Yann Lecommandoux, 2011-2012 Laurent Jouanneau, Louis S.
 *
 * @see     http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
require_once __DIR__.'/mssql.dbresultset.php';

/**
 * @deprecated
 */
class mssqlDbConnection extends jDbConnection
{
    /**
     * Default constructor.
     *
     * @param array $profile profile de connexion
     *
     * @throws jException
     */
    public function __construct($profile)
    {
        if (!function_exists('mssql_connect')) {
            throw new jException('jelix~db.error.nofunction', 'mssql');
        }
        parent::__construct($profile);
    }

    /**
     * begin a transaction.
     */
    public function beginTransaction()
    {
        $this->_doExec('SET IMPLICIT_TRANSACTIONS OFF');
        $this->_doExec('BEGIN TRANSACTION');
    }

    /**
     * Commit since the last begin.
     */
    public function commit()
    {
        $this->_doExec('COMMIT TRANSACTION');
        $this->_doExec('SET IMPLICIT_TRANSACTIONS ON');
    }

    /**
     * Rollback since the last BEGIN.
     */
    public function rollback()
    {
        $this->_doExec('ROLLBACK TRANSACTION');
        $this->_doExec('SET IMPLICIT_TRANSACTIONS ON');
    }

    /**
     * @param mixed $query
     */
    public function prepare($query)
    {
        throw new jException('jelix~db.error.feature.unsupported', array('mssql', 'prepare'));
    }

    public function errorInfo()
    {
        return array('HY000', mssql_get_last_message());
    }

    public function errorCode()
    {
        return mssql_get_last_message();
    }

    /**
     * @inheritdoc
     */
    protected function _connect()
    {
        $funcconnect = ($this->profile['persistent'] ? 'mssql_pconnect' : 'mssql_connect');
        if ($cnx = @$funcconnect($this->profile['host'], $this->profile['user'], $this->profile['password'])) {
            return $cnx;
        }

        throw new jException('jelix~db.error.connection', $this->profile['host']);
    }

    /**
     * @inheritdoc
     */
    protected function _disconnect()
    {
        return mssql_close($this->_connection);
    }

    /**
     * @inheritdoc
     */
    protected function _doQuery($query)
    {
        if (!mssql_select_db($this->profile['database'], $this->_connection)) {
            if (mssql_get_last_message()) {
                throw new jException('jelix~db.error.database.unknown', $this->profile['database']);
            }

            throw new jException('jelix~db.error.connection.closed', $this->profile['name']);
        }

        if ($qI = mssql_query($query, $this->_connection)) {
            return new mssqlDbResultSet($qI);
        }

        throw new jException('jelix~db.error.query.bad', mssql_get_last_message());
    }

    /**
     * @see jDbConnection::_doExec()
     *
     * @param mixed $query
     */
    protected function _doExec($query)
    {
        if (!mssql_select_db($this->profile['database'], $this->_connection)) {
            throw new jException('jelix~db.error.database.unknown', $this->profile['database']);
        }

        if ($qI = mssql_query($query, $this->_connection)) {
            return mssql_rows_affected($this->_connection);
        }

        throw new jException('jelix~db.error.query.bad', mssql_get_last_message());
    }

    /**
     * @inheritdoc
     */
    protected function _doLimitQuery($queryString, $offset, $number)
    {

        // we suppress existing 'TOP XX'
        $queryString = preg_replace('/^SELECT TOP[ ]\d*\s*/i', 'SELECT ', trim($queryString));

        $distinct = false;

        // we retrieve the select part and the from part
        list($select, $from) = preg_split('/\sFROM\s/mi', $queryString, 2);

        $fields = preg_split('/\s*,\s*/', $select);
        $firstField = preg_replace('/^\s*SELECT\s+/', '', array_shift($fields));

        // is there a distinct?
        if (stripos($firstField, 'DISTINCT') !== false) {
            $firstField = preg_replace('/DISTINCT/i', '', $firstField);
            $distinct = true;
        }

        // is there an order by? if not, we order with the first field
        $orderby = stristr($from, 'ORDER BY');
        if ($orderby === false) {
            if (stripos($firstField, ' as ') !== false) {
                list($field, $key) = preg_split('/ as /', $firstField);
            } else {
                $key = $firstField;
            }

            $orderby = ' ORDER BY '.$key.' ASC';
            $from .= $orderby;
        }

        // first we select all records from the begining to the last record of the selection
        if (!$distinct) {
            $queryString = 'SELECT TOP ';
        } else {
            $queryString = 'SELECT DISTINCT TOP ';
        }

        $queryString .= ($number + $offset).' '.$firstField.','.implode(',', $fields).' FROM '.$from;

        // then we select the last $number records, by retrieving the first $number record in the reverse order
        $queryString = 'SELECT TOP '.$number.' * FROM ('.$queryString.') AS inner_tbl ';
        $order_inner = preg_replace(array('/\bASC\b/i', '/\bDESC\b/i'), array('_DESC', '_ASC'), $orderby);
        $order_inner = str_replace(array('_DESC', '_ASC'), array('DESC', 'ASC'), $order_inner);
        $queryString .= $order_inner;

        // finally, we retrieve the result in the expected order
        $queryString = 'SELECT TOP '.$number.' * FROM ('.$queryString.') AS outer_tbl '.$orderby;

        $this->lastQuery = $queryString;

        return $this->_doQuery($queryString);
    }

    /**
     * (non-PHPdoc)
     * 	return the last inserted ID incremented in database.
     *
     * @see lib/jelix/db/jDbConnection#lastInsertId()
     *
     * @param mixed $fromSequence
     */
    public function lastInsertId($fromSequence = '')
    {
        $queryString = 'SELECT @@IDENTITY AS id';

        return $this->_doQuery($queryString);
    }

    /**
     * tell mssql to be implicit commit or not.
     *
     * @param bool $state the state of the autocommit value
     */
    protected function _autoCommitNotify($state)
    {
        if ($state == 1) {
            $this->query('SET IMPLICIT_TRANSACTIONS ON');
        } else {
            $this->query('SET IMPLICIT_TRANSACTIONS OFF');
        }
    }

    /**
     * escape special characters.
     *
     * @todo support of binary strings
     *
     * @param mixed $text
     * @param mixed $binary
     */
    protected function _quote($text, $binary)
    {
        return str_replace("'", "''", $text);
    }

    /**
     * @param int $id the attribut id
     *
     * @return string the attribute value
     *
     * @see PDO::getAttribute()
     */
    public function getAttribute($id)
    {
        return '';
    }

    /**
     * @param int    $id    the attribut id
     * @param string $value the attribute value
     *
     * @see PDO::setAttribute()
     */
    public function setAttribute($id, $value)
    {
    }
}
