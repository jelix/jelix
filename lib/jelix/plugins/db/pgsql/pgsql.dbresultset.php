<?php
/**
 * @package    jelix
 * @subpackage db_driver
 *
 * @author     Gérald Croes, Laurent Jouanneau
 * @contributor Laurent Jouanneau
 *
 * @copyright  2001-2005 CopixTeam, 2005-2020 Laurent Jouanneau
 * This class was get originally from the Copix project (CopixDBResultSetPostgreSQL, Copix 2.3dev20050901, http://www.copix.org)
 * Few lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
 * Initial authors of this Copix class are Gerald Croes and Laurent Jouanneau,
 * and this class was adapted/improved for Jelix by Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * @package    jelix
 * @subpackage db_driver
 */
class pgsqlDbResultSet extends jDbResultSet
{
    protected $_stmtId;
    protected $_cnt;

    protected $parameterNames = array();

    public function __construct($idResult, $stmtId = null, $cnt = null, $parameterNames = array())
    {
        $this->_idResult = $idResult;
        $this->_stmtId = $stmtId;
        $this->_cnt = $cnt;
        $this->parameterNames = $parameterNames;
    }

    public function fetch()
    {
        if ($this->_fetchMode == jDbConnection::FETCH_CLASS) {
            if ($this->_fetchModeCtoArgs) {
                $res = pg_fetch_object($this->_idResult, null, $this->_fetchModeParam, $this->_fetchModeCtoArgs);
            } else {
                $res = pg_fetch_object($this->_idResult, null, $this->_fetchModeParam);
            }
        } elseif ($this->_fetchMode == jDbConnection::FETCH_INTO) {
            $res = pg_fetch_object($this->_idResult);
            if ($res) {
                $values = get_object_vars($res);
                $res = $this->_fetchModeParam;
                foreach ($values as $k => $value) {
                    $res->{$k} = $value;
                }
            }
        } else {
            $res = pg_fetch_object($this->_idResult);
        }

        if ($res) {
            $this->applyModifiers($res);
        }

        return $res;
    }

    protected function _fetch()
    {
    }

    protected function _free()
    {
        return pg_free_result($this->_idResult);
    }

    protected function _rewind()
    {
        return pg_result_seek($this->_idResult, 0);
    }

    public function rowCount()
    {
        return pg_num_rows($this->_idResult);
    }

    protected $boundParameters = array();

    public function bindColumn($column, &$param, $type = null)
    {
        throw new jException('jelix~db.error.feature.unsupported', array('pgsql', 'bindColumn'));
    }

    public function bindValue($parameter, $value, $dataType = PDO::PARAM_STR)
    {
        if (!$this->_stmtId) {
            throw new Exception('Not a prepared statement');
        }
        $this->boundParameters[$parameter] = $value;

        return true;
    }

    public function bindParam($parameter, &$variable, $dataType = PDO::PARAM_STR, $length = null, $driverOptions = null)
    {
        if (!$this->_stmtId) {
            throw new Exception('Not a prepared statement');
        }
        $this->boundParameters[$parameter] = &$variable;

        return true;
    }

    public function columnCount()
    {
        return pg_num_fields($this->_idResult);
    }

    public function execute($parameters = null)
    {
        if (!$this->_stmtId) {
            throw new Exception('Not a prepared statement');
        }

        if ($this->_idResult) {
            pg_free_result($this->_idResult);
            $this->_idResult = null;
        }

        if ($parameters === null && count($this->boundParameters)) {
            $parameters = &$this->boundParameters;
        }

        $params = array();
        foreach ($this->parameterNames as $name) {
            if (isset($parameters[$name])) {
                $params[] = &$parameters[$name];
            } else {
                $params[] = '';
            }
        }

        $this->_idResult = pg_execute($this->_cnt, $this->_stmtId, $params);

        return true;
    }

    public function unescapeBin($text)
    {
        return pg_unescape_bytea($text);
    }
}
