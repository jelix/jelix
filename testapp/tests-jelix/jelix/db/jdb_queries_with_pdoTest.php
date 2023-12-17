<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
require_once(__DIR__.'/jdb.lib.php');
/**
 * same tests as UTjdb, but with a pdo connection
 */
class jdb_queries_with_pdoTest extends jDb_queryBase {

    protected $dbProfile ='testapppdo';
    protected $needPDO = true;

    function setUp() : void
    {
        parent::setUp();
        if (version_compare(PHP_VERSION, '8.1', '>=')) {
            static::$productPriceType = 'float';
        }

    }


}


