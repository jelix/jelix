<?php
/**
 * @package     jelix
 * @subpackage  dao
 *
 * @author      Laurent Jouanneau
 * @copyright   2017-2025 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use Jelix\Dao\DbMapper;
use Jelix\Database\Schema\TableInterface;


/**
 * It allows to create tables corresponding to a dao file.
 *
 * @since 1.6.16
 * @deprecated use Jelix\Dao\DbMapper instead
 */
class jDaoDbMapper
{
    /**
     * @var jDbConnection
     */
    protected $connection;

    protected $profile;

    protected DbMapper $dbMapper;
    /**
     * jDaoDbMapper constructor.
     *
     * @param string $profile the jdb profile
     */
    public function __construct($profile = '')
    {
        $this->connection = jDb::getConnection($profile);
        $this->profile = $profile;
        $cnt = jDb::getConnection($profile);
        $this->dbMapper = new DbMapper(new jDaoContext($profile, $cnt));
    }

    /**
     * Create a table from a jDao file.
     *
     * @param string $selector    the selector of the DAO file
     * @param mixed  $selectorStr
     *
     * @return TableInterface|jDbTable
     */
    public function createTableFromDao($selectorStr)
    {
        $selector = new jSelectorDao($selectorStr, $this->profile);
        return $this->dbMapper->createTableFromDao($selector);
    }

    /**
     * @param string    $selectorStr the dao for which we want to insert data
     * @param string[]  $properties  list of properties for which data are given
     * @param mixed[][] $data        the data. each row is an array of values.
     *                               Values are in the same order as $properties
     * @param int       $option      one of jDbTools::IBD_* const
     *
     * @return int number of records inserted/updated
     */
    public function insertDaoData($selectorStr, $properties, $data, $option)
    {
        $selector = new jSelectorDao($selectorStr, $this->profile);
        return $this->dbMapper->insertDaoData($selector, $properties, $data, $option);
    }

}
