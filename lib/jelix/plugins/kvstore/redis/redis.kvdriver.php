<?php
/**
 * @package     jelix
 * @subpackage  kvstore
 * @author      Yannick Le Guédart
 * @copyright   2009 Yannick Le Guédart
 *
 * @link     http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

require_once(LIB_PATH . 'php5redis/Php5Redis.php');

class redisKVDriver extends jKVDriver implements jIKVIncrementable, jIKVSet {
  
    /**
	 * Connects to the redis server
	 *
	 * @return Php5Redis object
	 */
   	protected function _connect() {

        // A host is needed
        if (! isset($this->_profile['host'])) {
     		throw new jException(
                'jelix~db.error.driver.nohost', $this->_driverName);           
        }
 
        // A port is needed as well
        if (! isset($this->_profile['port'])) {
     		throw new jException(
                'jelix~db.error.driver.noport', $this->_driverName);           
        }

        // OK, let's connect now
        $cnx = new Php5Redis($this->_profile['host'], $this->_profile['port']);
        return $cnx;
	}

    /**
	 * Disconnect from the redis server
	 *
	 * @return Php5Redis object
	 */
   	protected function _disconnect() {
		$this->_connection->quit();
        $this->_connection->disconnect();
    }

    protected function _doGet($key) {
        return $this->_connection->get($key);
    }

    protected function _doSet($key, $value, $ttl) {
        $res = $this->_connection->set($key, $value);

        if ($res !== 'OK') {
            return false;
        }

        if ($timeout != 0) {
            return $this->_connection->expire($key, $ttl);
        }

        return true;
    }

    protected function _doDelete($key) {
        return $this->_connection->delete($key);
    }

    protected function _doFlush() {
        return $this->_connection->flushall();
    }
	
	/* jIKVIncrementable ---------------------------------------------------- */
	
    public function increment($key, $incvalue = 1) {
        return $this->_connection->incr($key, $incvalue);
    }

    public function decrement($key, $decvalue = 1) {
        return $this->_connection->decr($key, $decvalue);
    }
	
	/* jIKVSet ------------------------------------------------------------- */
	
    public function set_add($skey, $value) {
        return $this->_connection->sadd($skey, $value);
    }

    public function set_remove($skey, $value) {
        return $this->_connection->srem($skey, $decvalue);
    }

    public function set_count($skey) {
        return $this->_connection->scard($skey);
    }
	
	public function set_pop($skey) {
		return $this->_connection->spop($skey);
	}

}
