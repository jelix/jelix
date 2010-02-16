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

/**
 * interface for KV driver which provides incremental storage
 */
interface jIKVIncrementable {
    public function increment($key, $incvalue = 1);
    public function decrement($key, $decvalue = 1);
}

/**
 * interface for KV driver which allow to access to values like a stack
 */
interface jIKVStack {
    public function pop($key);
    public function push($key, $value);
}

/**
 *
 */
interface jIKVSet {
    public function set_add($skey, $value);
    public function set_remove($skey, $value);
    public function set_count($skey);
	public function set_pop($skey);
}

abstract class jKVDriver {

    /**
	 * Profile for the connection in the kvstore INIfile.
	 *
	 * @var array
	 * @see KVSTORE_PROFILE_FILE
	 */
    protected $_profile;

    /**
	 * Name of the driver.
	 *
	 * @var string
	 */ 
    protected $_driverName;

    /**
	 * Name of the driver.
	 *
	 * @var object
	 */
    protected $_connection = null;
     
    /**
	 * Class constructor
	 *
	 * Initialise profile data and create the main object
	 *
	 * @param array $profile
	 * @return void
	 */
	public function __construct($profile) {
		$this->_profile     = &$profile;
		$this->_driverName  = $profile['driver'];
        
		$this->_connection = $this->_connect();
	}

    /**
	 * Class destructor
	 *
	 * @return void
	 */
	public function __destruct() {
		if (! is_null($this->_connection)) {
			$this->_disconnect();
		}
	}

    /**
	 * Gets a key value.
	 *
	 * @param string $key
	 * @return string
	 */    
	public function get($key) {
        return $this->_doGet($key);
	}
    
    /**
	 * Sets a key value.
	 *
	 * @param string $key
	 * @param string $value
	 * @param string $ttl
	 *
	 * @return boolean
	 */    
	public function set($key, $value, $ttl = 0) {
        return $this->_doSet($key, $value, $ttl);
	}
    
    /**
	 * Deletes a key from the KVStore.
	 *
	 * @param string $key
	 * @return boolean
	 */
    
	public function delete($key) {
        return $this->_doDelete($key);
	}

    /**
	 * Flush the KVStore.
	 *
	 * @return boolean
	 */    
	public function flush() {
        return $this->_doFlush();
	}
 
    abstract protected function _connect();
    abstract protected function _disconnect();
    abstract protected function _doGet($key);
    abstract protected function _doSet($key, $value, $timout);
	abstract protected function _doDelete($key);
	abstract protected function _doFlush();
}

