<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2019 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

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
            jProfiles::get('jdb', 'testapp_pgsql', true);
        }
        catch(Exception $e) {
            $this->markTestSkipped(get_class($this).' cannot be run: undefined testapp_pgsql profile');
            return;
        }
        $this->dbProfile ='testapp_pgsql';
    }

    function testInstanciation() {
        $dao = jDao::create ('products', $this->dbProfile);
        $this->assertInstanceOf('cDao_jelix_tests_Jx_products_Jx_pgsql', $dao);

        $dao = jDao::get ('products', $this->dbProfile);
        $this->assertInstanceOf('cDao_jelix_tests_Jx_products_Jx_pgsql', $dao);

        $daorec = jDao::createRecord ('products', $this->dbProfile);
        $this->assertInstanceOf('cDaoRecord_jelix_tests_Jx_products_Jx_pgsql', $daorec);

        $daorec = $dao->createRecord();
        $this->assertInstanceOf('cDaoRecord_jelix_tests_Jx_products_Jx_pgsql', $daorec);
    }

}

