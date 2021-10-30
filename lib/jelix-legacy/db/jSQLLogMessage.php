<?php
/**
 * @package     jelix
 * @subpackage  db
 *
 * @author      Laurent Jouanneau
 * @copyright   2011-2021 Laurent Jouanneau
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use Jelix\Database\Log\QueryLoggerInterface;

/**
 * class that handles a sql query for a logger.
 */
class jSQLLogMessage extends \Jelix\Dao\Database\QueryLogger implements QueryLoggerInterface, jILogMessage
{
    public function getCategory()
    {
        return 'sql';
    }

    public function getMessage()
    {
        return $this->query;
    }
}
