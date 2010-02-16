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
require_once(LIB_PATH . 'php5redis/Php5Redis.php');
/**
* Tests API jKVDb
* @package     testapp
* @subpackage  jelix_tests module
*/

class UTjKVDbRedis extends UTjKVDb {

    protected $profile = 'usingredis';

    protected $redis;

    public function setUp (){
        $this->redis = new Php5Redis('localhost',6379);
        $this->redis->flushall();
    }

    public function tearDown() {
        //$this->redis->quit();
        $this->redis->disconnect();
    }
}

?>