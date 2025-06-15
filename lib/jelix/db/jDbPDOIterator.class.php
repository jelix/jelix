<?php

/**
 * @package    jelix
 * @subpackage db
 *
 * @author     Laurent Jouanneau
 *
 * @copyright  2025 Laurent Jouanneau
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * An iterator for jDbPDOResultSet
 *
 * @package  jelix
 * @subpackage db
 */
class jDbPDOIterator implements Iterator
{

    protected $_currentRecord = false;
    protected $_recordIndex = 0;

    /**
     * @var jDbPDOResultSet|jDbPDOResultSet7
     */
    protected $resultSet;

    public function __construct($resultSet)
    {
        $this->resultSet = $resultSet;
    }

    //--------------- interface Iterator
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->_currentRecord;
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->_recordIndex;
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->_currentRecord = $this->resultSet->fetch();
        if ($this->_currentRecord) {
            ++$this->_recordIndex;
        }
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->_recordIndex = 0;
        $this->_currentRecord = $this->resultSet->fetch();
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return $this->_currentRecord != false;
    }

}