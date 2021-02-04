<?php
/**
 * @package    jelix
 * @subpackage db
 *
 * @author     Laurent Jouanneau
 * @contributor Gwendal Jouannic, Thomas, Julien Issler, Vincent Herr
 *
 * @copyright  2005-2012 Laurent Jouanneau
 * @copyright  2008 Gwendal Jouannic, 2009 Thomas
 * @copyright  2009 Julien Issler
 * @copyright  2011 Vincent Herr
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * A connection object based on PDO.
 *
 * @package  jelix
 * @subpackage db
 */
class jDbPDOConnection extends PDO
{
    private $_pgsqlCharsets = array('UTF-8' => 'UNICODE', 'ISO-8859-1' => 'LATIN1');

    /**
     * the profile the connection is using.
     *
     * @var array
     */
    public $profile;

    /**
     * The database type name (mysql, pgsql ...)
     * It is not the driver name. Several drivers could connect to the same database
     * type. This type name is often used to know whish SQL language we should use.
     *
     * @var string
     */
    public $dbms;

    /**
     * driver name.
     *
     * @var string
     */
    public $driverName = '';

    /**
     * Use a profile to do the connection.
     *
     * @param array $profile the profile data. Its content must be normalized by jDbParameters
     */
    public function __construct($profile)
    {
        $this->profile = $profile;
        $user = '';
        $password = '';
        $this->dbms = $profile['dbtype'];
        $this->driverName = $profile['driver'];

        $dsn = $profile['dsn'];
        if ($this->dbms == 'sqlite') {
            $dsn = 'sqlite:'.$this->_parseSqlitePath(substr($dsn, 7));
        }

        // we check user and password because some db like sqlite doesn't have user/password
        if (isset($profile['user'])) {
            $user = $profile['user'];
        }

        if (isset($profile['password'])) {
            $password = $profile['password'];
        }

        $pdoOptions = array();
        if ($profile['pdooptions'] != '') {
            foreach (explode(',', $profile['pdooptions']) as $optname) {
                $pdoOptions[$optname] = $profile[$optname];
            }
        }

        $initsql = '';
        if ($profile['force_encoding']) {
            $charset = jApp::config()->charset;
            if ($profile['pdodriver'] == 'mysql' ||
                $profile['pdodriver'] == 'mssql' ||
                $profile['pdodriver'] == 'sybase' ||
                $profile['pdodriver'] == 'oci') {
                $dsn .= ';charset='.$charset;
            } elseif ($this->dbms == 'pgsql' && isset($this->_pgsqlCharsets[$charset])) {
                $initsql = "SET client_encoding to '".$this->_pgsqlCharsets[$charset]."'";
            }
        }

        parent::__construct($dsn, $user, $password, $pdoOptions);

        if (version_compare(phpversion(), "8.0") < 0) {
            $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('jDbPDOResultSet7'));
        }
        else {
            $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('jDbPDOResultSet'));
        }
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // we cannot launch two queries at the same time with PDO ! except if
        // we use mysql with the attribute MYSQL_ATTR_USE_BUFFERED_QUERY
        // TODO check if PHP 5.3 or higher fixes this issue
        if ($this->dbms == 'mysql') {
            $this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        }

        // Oracle returns names of columns in upper case by default. so here
        // we force the case in lower.
        if ($this->dbms == 'oci') {
            $this->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
        }

        if ($initsql) {
            $this->exec($initsql);
        }
    }

    protected function _parseSqlitePath($path)
    {
        if (preg_match('/^(app|lib|var|temp|www)\:/', $path, $m)) {
            return jFile::parseJelixPath($path);
        }
        if (preg_match('!^[a-z]\\:(\\\\|/)[a-z]!i', $path) || // windows path
                 $path[0] == '/' // *nix path
                ) {
            if (file_exists($path) || file_exists(dirname($path))) {
                return $path;
            }

            throw new Exception('jDbPDOConnection, sqlite: unknown database path scheme');
        }

        return jApp::varPath('db/sqlite/'.$path);
    }

    public function getProfileName()
    {
        return $this->profile['_name'];
    }

    /**
     * @internal the implementation of Iterator on PDOStatement doesn't call
     * fetch method of classes which inherit of PDOStatement.
     * so, we cannot indicate to fetch object directly in jDbPDOResultSet::fetch().
     * So we overload query() to do it.
     * TODO check if this is still the case in PHP 8.1+
     * @return jDbPDOResultSet|PDOStatement
     */
    public function query($queryString, $fetchmode = PDO::FETCH_OBJ, ...$fetchModeArgs)
    {

        if (count($fetchModeArgs) === 0) {
            $rs = parent::query($queryString);
            $rs->setFetchMode($fetchmode);
            return $rs;
        }

        if (count($fetchModeArgs) === 1 || $fetchModeArgs[1] === array()) {
            return parent::query($queryString, $fetchmode, $fetchModeArgs[0]);
        }
        return parent::query($queryString, $fetchmode, $fetchModeArgs[0], $fetchModeArgs[1]);
    }

    /**
     * Launch a SQL Query with limit parameter (so only a subset of a result).
     *
     * @param string $queryString the SQL query
     * @param int    $limitOffset the offset of the first row to return
     * @param int    $limitCount  the maximum of number of rows to return
     *
     * @return bool|jDbPDOResultSet SQL Select. False if the query has failed.
     */
    public function limitQuery($queryString, $limitOffset = null, $limitCount = null)
    {
        if ($limitOffset !== null && $limitCount !== null) {
            if ($this->dbms == 'mysql' || $this->dbms == 'sqlite') {
                $queryString .= ' LIMIT '.intval($limitOffset).','.intval($limitCount);
            } elseif ($this->dbms == 'pgsql') {
                $queryString .= ' LIMIT '.intval($limitCount).' OFFSET '.intval($limitOffset);
            } elseif ($this->dbms == 'oci') {
                $limitOffset = $limitOffset + 1; // rnum begins at 1
                $queryString = 'SELECT * FROM ( SELECT ocilimit.*, rownum rnum FROM ('.$queryString.') ocilimit WHERE
                    rownum<'.(intval($limitOffset) + intval($limitCount)).'  ) WHERE rnum >='.intval($limitOffset);
            } elseif ($this->dbms == 'sqlsrv') {
                $queryString = $this->limitQuerySqlsrv($queryString, $limitOffset, $limitCount);
            }
        }

        return $this->query($queryString);
    }

    /**
     * Create a limitQuery for the SQL server dbms.
     *
     * @param string $queryString the SQL query
     * @param int    $limitOffset the offset of the first row to return
     * @param int    $limitCount  the maximum of number of rows to return
     *
     * @return string SQL Select
     */
    protected function limitQuerySqlsrv($queryString, $limitOffset = null, $limitCount = null)
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

            $orderby = ' ORDER BY '.strstr(strstr($key, '.'), '[').' ASC';
            $from .= $orderby;
        } else {
            if (strpos($orderby, '.', 8)) {
                $orderby = ' ORDER BY '.substr($orderby, strpos($orderby, '.') + 1);
            }
        }

        // first we select all records from the begining to the last record of the selection
        if (!$distinct) {
            $queryString = 'SELECT TOP ';
        } else {
            $queryString = 'SELECT DISTINCT TOP ';
        }

        $queryString .= ($limitCount + $limitOffset).' '.$firstField.','.implode(',', $fields).' FROM '.$from;

        // then we select the last $number records, by retrieving the first $number record in the reverse order
        $queryString = 'SELECT TOP '.$limitCount.' * FROM ('.$queryString.') AS inner_tbl ';
        $order_inner = preg_replace(array('/\bASC\b/i', '/\bDESC\b/i'), array('_DESC', '_ASC'), $orderby);
        $order_inner = str_replace(array('_DESC', '_ASC'), array('DESC', 'ASC'), $order_inner);
        $queryString .= $order_inner;

        // finally, we retrieve the result in the expected order
        return 'SELECT TOP '.$limitCount.' * FROM ('.$queryString.') AS outer_tbl '.$orderby;
    }

    public function prepare($query, $driverOptions = array())
    {
        $result = parent::prepare($query, $driverOptions);
        if ($result) {
            $result->setFetchMode(\PDO::FETCH_OBJ);
        }

        return $result;
    }

    /**
     * sets the autocommit state.
     *
     * @param bool $state the status of autocommit
     */
    public function setAutoCommit($state = true)
    {
        $this->setAttribute(PDO::ATTR_AUTOCOMMIT, $state);
    }

    /**
     * return the maximum value of the given primary key in a table.
     *
     * @param string $fieldName the name of the primary key
     * @param string $tableName the name of the table
     *
     * @return int the maximum value
     */
    public function lastIdInTable($fieldName, $tableName)
    {
        $rs = $this->query('SELECT MAX('.$fieldName.') as ID FROM '.$tableName);
        if (($rs !== null) && $r = $rs->fetch()) {
            return $r->ID;
        }

        return 0;
    }

    /**
     * Prefix the given table with the prefix specified in the connection's profile
     * If there's no prefix for the connection's profile, return the table's name unchanged.
     *
     * @param string $table      the table's name
     * @param mixed  $table_name
     *
     * @return string the prefixed table's name
     *
     * @author Julien Issler
     *
     * @since 1.0
     */
    public function prefixTable($table_name)
    {
        return $this->profile['table_prefix'].$table_name;
    }

    /**
     * Check if the current connection has a table prefix set.
     *
     * @return bool
     *
     * @author Julien Issler
     *
     * @since 1.0
     */
    public function hasTablePrefix()
    {
        return $this->profile['table_prefix'] != '';
    }

    /**
     * enclose the field name.
     *
     * @param string $fieldName the field name
     *
     * @return string the enclosed field name
     *
     * @since 1.1.2
     */
    public function encloseName($fieldName)
    {
        switch ($this->dbms) {
            case 'mysql': return '`'.$fieldName.'`';
            case 'pgsql': return '"'.$fieldName.'"';
            default: return $fieldName;
        }
    }

    /**
     * Escape and quotes strings. if null, will only return the text "NULL".
     *
     * @param string $text      string to quote
     * @param bool   $checknull if true, check if $text is a null value, and then return NULL
     * @param bool   $binary    set to true if $text contains a binary string
     *
     * @return string escaped string
     *
     * @since 1.2
     *
     * @todo $binary parameter is not really supported, check if PDOConnection::quote supports binary strings
     */
    public function quote2($text, $checknull = true, $binary = false)
    {
        if ($checknull) {
            return is_null($text) ? 'NULL' : $this->quote($text);
        }

        return $this->quote($text);
    }

    /**
     * @throws jException
     *
     * @return jDbTools
     */
    public function tools()
    {
        return jDbUtils::getTools($this->dbms, $this);
    }

    /**
     * @var jDbSchema
     */
    protected $_schema;

    /**
     * @return jDbSchema
     */
    public function schema()
    {
        if (!$this->_schema) {
            $this->_schema = jApp::loadPlugin($this->driverName, 'db', '.dbschema.php', $this->driverName.'DbSchema', $this);
            if (is_null($this->_schema)) {
                throw new jException('jelix~db.error.driver.notfound', $this->driverName);
            }
        }

        return $this->_schema;
    }

    /**
     * Get the ID of the last inserted row
     * Mssql pdo driver does not support this feature.
     * so, we use a custom query.
     *
     * @param string $fromSequence the sequence name, if needed
     *
     * @return string
     */
    public function lastInsertId($fromSequence = null)
    {
        if ($this->dbms == 'mssql') {
            $res = $this->query('SELECT SCOPE_IDENTITY()');

            return (int) $res->fetchColumn();
        }

        return parent::lastInsertId($fromSequence);
    }
}
