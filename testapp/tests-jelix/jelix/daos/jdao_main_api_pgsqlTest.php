<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2019-2025 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

use Jelix\Core\Profiles;

require_once(__DIR__.'/jdao_main_api.lib.php');
/**
 * same tests as jdao_main_api_pdo, but with a pgsql connection
 */
class jdao_main_api_pgsqlTest extends jdao_main_api_base {
    static protected $trueValue = 't';
    static protected $falseValue = 'f';

    function setUp() : void  {
        parent::setUp();
        try {
            Profiles::get('jdb', 'testapp_pgsql', true);
        }
        catch(Exception $e) {
            $this->markTestSkipped(get_class($this).' cannot be run: undefined testapp_pgsql profile');
            return;
        }
        $this->dbProfile ='testapp_pgsql';
    }

    function testInstanciation() {
        $dao = jDao::create ('products', $this->dbProfile);
        $this->assertInstanceOf('\Jelix\BuiltComponents\Daos\Jelix_tests\ProductsPgsqlFactory', $dao);

        $dao = jDao::get ('products', $this->dbProfile);
        $this->assertInstanceOf('\Jelix\BuiltComponents\Daos\Jelix_tests\ProductsPgsqlFactory', $dao);

        $daorec = jDao::createRecord ('products', $this->dbProfile);
        $this->assertInstanceOf('\Jelix\BuiltComponents\Daos\Jelix_tests\ProductsPgsqlRecord', $daorec);

        $daorec = $dao->createRecord();
        $this->assertInstanceOf('\Jelix\BuiltComponents\Daos\Jelix_tests\ProductsPgsqlRecord', $daorec);
    }

}

