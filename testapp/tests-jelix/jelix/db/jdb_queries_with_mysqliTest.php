<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Florian Lonqueu-Brochard
* @copyright   2012 Florian Lonqueu-Brochard
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
require_once(__DIR__.'/jdb.lib.php');
/**
 * same tests as UTjdb, but with a pdo connection
 */
class jdb_queries_with_mysqliTest extends jDb_queryBase {

    protected $dbProfile ='mysqli_profile';
}


