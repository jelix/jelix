<?php
/**
* @package    jelix
* @subpackage db
* @author     Laurent Jouanneau
* @contributor Gwendal Jouannic, Thomas, Julien Issler
* @copyright  2005-2010 Laurent Jouanneau
* @copyright  2008 Gwendal Jouannic, 2009 Thomas
* @copyright  2009 Julien Issler
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * a resultset based on PDOStatement
 * @package  jelix
 * @subpackage db
 */
class jDbPDOResultSet extends PDOStatement {

    const FETCH_CLASS = 8;

    protected $_fetchMode = 0;

    public function fetch ($fetch_style = PDO::FETCH_BOTH, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0) {
        $rec = parent::fetch();
        if ($rec && count($this->modifier)) {
            foreach($this->modifier as $m)
                call_user_func_array($m, array($rec, $this));
        }
        return $rec;
    }

    /**
     * return all results from the statement.
     * Arguments are ignored. JDb don't care about it (fetch always as classes or objects)
     * But there are here because of the compatibility of internal methods of PDOStatement
     * @param integer $fetch_style ignored
     * @param integer $column_index
     * @param array $ctor_arg  (ignored)
     * @return array list of object which contain all rows
     */
    public function fetchAll ($fetch_style = PDO::FETCH_OBJ, $column_index=0, $ctor_arg=null) {
        if ($this->_fetchMode) {
            if ($this->_fetchMode != PDO::FETCH_COLUMN)
                return parent::fetchAll($this->_fetchMode);
            else
                return parent::fetchAll($this->_fetchMode, $column_index);
        }
        else {
            return parent::fetchAll(PDO::FETCH_OBJ);
        }
    }

    /**
     * Set the fetch mode.
     * @param int $mode  the mode, a PDO::FETCH_* constant
     * @param mixed $arg1 a parameter for the given mode
     * @param mixed $arg2 a parameter for the given mode
     */
    public function setFetchMode($mode, $arg1=null , $arg2=null){
        $this->_fetchMode = $mode;
        // depending the mode, original setFetchMode throw an error if wrong arguments
        // are given, even if there are null
        if ($arg1 === null)
            return parent::setFetchMode($mode);
        else if ($arg2 === null)
            return parent::setFetchMode($mode, $arg1);
        return parent::setFetchMode($mode, $arg1, $arg2);
    }

    /**
     * @param string $text a binary string to unescape
     * @since 1.1.6
     */
    public function unescapeBin($text) {
        return $text;
    }

    /**
     * a callback function which will modify on the fly record's value
     * @var array of callback
     * @since 1.1.6
     */
    protected $modifier = array();

    /**
     * @param callback $function a callback function
     *     the function should accept in parameter the record,
     *     and the resulset object
     * @since 1.1.6
     */
    public function addModifier($function) {
        $this->modifier[] = $function;
    }
}
