<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Tahina Ramaroson
* @contributor Sylvain de Vathaire
* @contributor Laurent Jouanneau
* @copyright   NEOV 2009, 2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jcache.lib.php');
require_once(__DIR__. '/../../../vendor/jelix/php-redis/lib/Redis.php');

/**
* Tests API jCache
* @package     testapp
* @subpackage  jelix_tests module
*/

class jCache_RedisTest extends jCacheAPITest {

    protected $redis;

    function setUp ()  : void {
        $this->profile = 'usingredis';
        parent::setUp();
        $this->redis = new \PhpRedis\Redis(TESTAPP_REDIS_HOST,6379);
        $this->redis->flushall();
    }

    public function tearDown() : void  {
        if ($this->redis) {
            //$this->redis->quit();
            $this->redis->disconnect();
        }
    }

}
