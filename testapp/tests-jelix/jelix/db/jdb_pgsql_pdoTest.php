<?php
/**
 * @package     testapp
 * @subpackage  jelix_tests module
 * @author      Laurent Jouanneau
 * @copyright   2026 Laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
require_once(__DIR__.'/jdb_pgsqlTest.php');
class jdb_pgsql_pdoTest extends jDb_PgsqlTest
{

    protected $dbProfile ='testapp_pgsql_pdo';
    protected $needPDO = true;

}