<?php
/**
 * @package    jelix
 * @subpackage dao
 *
 * @author      Laurent Jouanneau
 * @copyright   2021 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use Jelix\Dao\CustomRecordClassFileInterface;
use Jelix\Dao\DaoFileInterface;
use Jelix\Database\ConnectionInterface;
use Jelix\Database\Schema\SqlToolsInterface;

/**
 * Context for the DAO compiler
 *
 * @package  jelix
 * @subpackage dao
 */
class jDaoContext implements \Jelix\Dao\ContextInterface
{

    protected $profile;

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    function __construct($profile, ConnectionInterface $connection) {
        $this->profile = $profile;
        $this->connection = $connection;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnector()
    {
        return $this->connection;
    }

    /**
     * @return SqlToolsInterface
     */
    function getDbTools()
    {
        return $this->connection->tools();
    }

    /**
     * @param string $path
     *
     * @return DaoFileInterface
     */
    function resolveDaoPath($path)
    {
        return new \jSelectorDao($path, $this->profile);
    }

    /**
     * @param string $path
     *
     * @return CustomRecordClassFileInterface
     */
    function resolveCustomRecordClassPath($path)
    {
        return new \jSelectorDaoRecord($path);
    }

    function shouldCheckCompiledClassCache()
    {
        return \Jelix\Core\App::config()->compilation['checkCacheFiletime'];
    }
}
