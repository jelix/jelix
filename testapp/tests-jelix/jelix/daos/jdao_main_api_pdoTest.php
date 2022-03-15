<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jdao_main_api.lib.php');
/**
 * same tests as jdao_main_api_pdo, but with a pdo connection
 */
class jdao_main_api_pdoTest extends jdao_main_api_base {

    function setUp() {
        parent::setUp();
        $this->dbProfile ='testapppdo';
        $this->needPDO =  true;
        if (version_compare(PHP_VERSION, '8.1', '>=')) {
            static::$productIdType = 'integer';
            static::$productPriceType = 'float';
            static::$productPromoType = 'integer';
        }
    }
}

