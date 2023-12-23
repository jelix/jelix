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

/**
* Tests API jCache
* @package     testapp
* @subpackage  jelix_tests module
*/

class jCache_Redis_extTest extends AbstractCacheAPI {

    protected $redis;

    function setUp () : void  {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('jCache_Redis_extTest cannot be run because redis extension is not installed');
            return;
        }
        $this->profile = 'usingredis_ext';
        parent::setUp();

        $this->redis = new jRedis();
        $this->redis->connect(TESTAPP_REDIS_HOST,6379);
        $this->redis->flushAll();
    }

    public function tearDown() : void  {
        if ($this->redis) {
            $this->redis->close();
        }
    }

}
