<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @copyright   2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(dirname(__FILE__).'/jkvstore.lib.php');

/**
* Tests API jKVStore
* @package     testapp
* @subpackage  jelix_tests module
*/

class UTjKVStoreMemcache extends UTjKVStore {

    protected $profile = 'usingmemcache';

    public function setUp (){
        $this->mmc = memcache_connect('localhost',11211);
        memcache_flush($this->mmc);
    }

    public function tearDown() {
        memcache_close($this->mmc);
        $this->mmc = null;
    }
}

?>