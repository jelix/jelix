<?php
/**
 * @package    jelix
 * @subpackage dao
 *
 * @author      Laurent Jouanneau
 * @copyright   2021-2026 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\DaoUtils;

use jApp;
use Jelix\Dao\CustomRecordClassFileInterface;
use Jelix\Dao\DaoFileInterface;
use Jelix\Database\Connection;
use Jelix\Database\ConnectionInterface;
use Jelix\Database\Schema\SQLSyntaxHelpersInterface;
use Jelix\Database\Schema\SqlToolsInterface;
use Jelix\Dao\ContextInterface;
use Jelix\Dao\ContextInterface2;

/**
 * Context for the DAO compiler
 *
 * @package  jelix
 * @subpackage dao
 */
class DaoContext implements ContextInterface, ContextInterface2
{

    protected $profileName;
    protected $sqlType;

    /**
     * @var SQLSyntaxHelpersInterface
     */
    protected $syntaxHelpers;

    function __construct($profileName, $sqlType)
    {
        $this->profileName = $profileName;
        $this->sqlType = $sqlType;
        $this->syntaxHelpers = Connection::getSqlSyntaxHelpers($this->sqlType);
    }

    /**
     * @return ConnectionInterface
     * @deprecated
     */
    public function getConnector()
    {
        return null;
    }

    /**
     * @return SqlToolsInterface
     */
    function getDbTools()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getSqlType(): string
    {
        return $this->sqlType;
    }

    /**
     * @inheritDoc
     */
    public function getSqlSyntaxHelpers(): SQLSyntaxHelpersInterface
    {
        return $this->syntaxHelpers;
    }

    /**
     * @param string $path
     *
     * @return DaoFileInterface
     */
    function resolveDaoPath($path)
    {
        return new DaoSelector($path, $this->profileName);
    }

    /**
     * @param string $path
     *
     * @return CustomRecordClassFileInterface
     */
    function resolveCustomRecordClassPath($path)
    {
        return new DaoRecordSelector($path);
    }

    function shouldCheckCompiledClassCache()
    {
        return jApp::config()->compilation['checkCacheFiletime'];
    }
}
