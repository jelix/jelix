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
     */
    public function setFetchMode($mode, $param=null){
        $this->_fetchMode = $mode;
        return parent::setFetchMode($mode, $param);
    }
}
