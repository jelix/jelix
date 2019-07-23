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
 * same tests as jdao_main_api_pdo, but with a sqlite3 connection
 */
class jdao_main_api_sqlite3Test extends jdao_main_api_base {

    static protected $productIdType = 'integer';
    static protected $productPriceType = 'float';
    static protected $productPromoType = 'integer';

    function setUp() {
        self::initJelixConfig();
        jApp::pushCurrentModule('jelix_tests');
        try {
            jProfiles::get('jdb', 'testapp_sqlite3', true);
        }
        catch(Exception $e) {
            $this->markTestSkipped(get_class($this).' cannot be run: undefined testapp_sqlite3 profile');
            return;
        }
        $this->dbProfile ='testapp_sqlite3';
    }

    function testInstanciation() {
        $dao = jDao::create ('products', $this->dbProfile);
        $this->assertInstanceOf('cDao_jelix_tests_Jx_products_Jx_sqlite', $dao);

        $dao = jDao::get ('products', $this->dbProfile);
        $this->assertInstanceOf('cDao_jelix_tests_Jx_products_Jx_sqlite', $dao);

        $daorec = jDao::createRecord ('products', $this->dbProfile);
        $this->assertInstanceOf('cDaoRecord_jelix_tests_Jx_products_Jx_sqlite', $daorec);

        $daorec = $dao->createRecord();
        $this->assertInstanceOf('cDaoRecord_jelix_tests_Jx_products_Jx_sqlite', $daorec);
    }
    function testBinaryField() {
        // FIXME sqlite3 driver does not support binary field
    }
}

