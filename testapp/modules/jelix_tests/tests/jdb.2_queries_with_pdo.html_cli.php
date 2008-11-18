<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
require_once(dirname(__FILE__).'/jdb.1_queries.html_cli.php');
/**
 * same tests as UTjdb, but with a pdo connection
 */
class UTjDb_pdo extends UTjDb {

    protected $dbProfile ='testapppdo';
    protected $needPDO = true;
}


?>