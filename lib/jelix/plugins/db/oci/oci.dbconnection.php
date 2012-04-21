<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Gérald Croes, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @contributor Yannick Le Guédart
* @contributor Laurent Raufaste
* @contributor Julien Issler
* @copyright  2001-2005 CopixTeam, 2005-2010 Laurent Jouanneau, 2007-2008 Laurent Raufaste
* @copyright  2009 Julien Issler
* This class was get originally from the Copix project (CopixDBConnectionPostgreSQL, Copix 2.3dev20050901, http://www.copix.org)
* Few lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix class are Gerald Croes and Laurent Jouanneau,
* and this class was adapted/improved for Jelix by Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
// require_once(dirname(__FILE__).'/pgsql.dbresultset.php');

/**
 *
 * @package    jelix
 * @subpackage db_driver
 */
class ociDbConnection extends jDbConnection {

	protected $_lastErrorData = null;

    function __construct($profile){
        if(!function_exists('oci_connect')){
            throw new jException('jelix~db.error.nofunction','oci');
        }
        parent::__construct($profile);
        if(isset($this->profile['single_transaction']) && ($this->profile['single_transaction'])){
            $this->beginTransaction();
			$this->setAutoCommit(false);
		}
		else
		{
			$this->setAutoCommit(true);
        }
    }

    /**
     * enclose the field name
     * @param string $fieldName the field name
     * @return string the enclosed field name
     * @since 1.1.1
     */
    public function encloseName($fieldName){
        return '"'.$fieldName.'"';
    }

    function __destruct(){
        if(isset($this->profile['single_transaction']) && ($this->profile['single_transaction'])){
            $this->commit();
        }
        parent::__destruct();
    }

    public function beginTransaction (){
        return true;
    }

    public function commit (){
        return true;
    }

    public function rollback (){
        return true;
    }

	public function prepare($query) {
		$res = oci_parse($this->_connection, $query);
		
		return $res;
	}
	
    /**
     * @return string the last error description
     */
    public function errorInfo() {
		return oci_error($this->_connection);
	}

    /**
     * @return integer the last error code
     */
    public function errorCode() {
		$errorData = oci_error($this->_connection);
		if($errorData === false) {
			return 0;
		}
		return $errorData['code'];
	}

    /**
     * return the id value of the last inserted row.
     * Some driver need a sequence name, so give it at first parameter
     * @param string $fromSequence the sequence name
     * @return integer the id value
     */
    public function lastInsertId($fromSequence='') {
        if($seqname == ''){
            trigger_error(get_class($this) . '::lastInstertId invalide sequence name', E_USER_WARNING);
            return false;
        }
		
        $cur = $this->query("SELECT $seqname.CURRVAL AS ID FROM DUAL");
        if($cur){
            $res=$cur->fetch();
            if($res)
                return $res->id;
            else
                return false;
        }else{
            trigger_error(get_class($this).'::lastInstertId invalide sequence name',E_USER_WARNING);
            return false;
        }
	}

    protected function _connect (){
        $funcconnect= (isset($this->profile['persistent']) && $this->profile['persistent'] ? 'oci_pconnect':'oci_connect');

        $str = '';

        if($this->profile['host'] != '')
            $str .= '//' . $this->profile['host'];

        if (isset($this->profile['port'])) {
            $str .= ':' . $this->profile['port'];
        }

        if ($this->profile['instance_name'] != '') {
            $str .= '/' . $this->profile['instance_name'];
        }

        // let's do the connection
        if ($cnx = @$funcconnect ($this->profile['user'], $this->profile['password'], $str)) {
        }
		else {
            throw new jException('jelix~db.error.connection',$this->profile['host']);
        }
		
        return $cnx;
    }

    protected function _disconnect () {
        return oci_close ($this->_connection);
    }

    /**
    * do a query which return results
    * @return jDbResultSet/boolean
    */
    protected function _doQuery ($queryString) {
		if ($res = @oci_parse ($this->_connection, $queryString)) {
            $rs = new ociDbResultSet ($res);
            $rs->_connector = $this;
		} else {
            $rs = false;
			$errorData = oci_error($this->_connection);
            throw new jException('jelix~db.error.query.bad',  $errorData['message'] . '(' . $queryString . ')');
		}
		return $rs;
	}

    protected function _doExec($query){
        if($rs = $this->_doQuery($query)){
            return oci_num_rows($rs->statement());
        }else
            return 0;
    }

    /**
    * do a query which return a limited number of results
    * @return jDbResultSet/boolean
    */
    protected function _doLimitQuery ($queryString, $offset, $number)
	{
	}

    /**
    * Notify the changes on autocommit
    * @param boolean $state the new state of autocommit
    */
    protected function _autoCommitNotify ($state){

    }

    /**
     *
     * @param integer $id the attribut id
     * @return string the attribute value
     * @see PDO::getAttribute()
     */
    public function getAttribute($id) {
        switch($id) {
            case self::ATTR_CLIENT_VERSION:
                return oci_client_version();
				break;
            case self::ATTR_SERVER_VERSION:
                return oci_server_version($this->_connection);
                break;
        }
        return "";
    }

    /**
     * 
     * @param integer $id the attribut id
     * @param string $value the attribute value
     * @see PDO::setAttribute()
     */
    public function setAttribute($id, $value) {
    }

}

