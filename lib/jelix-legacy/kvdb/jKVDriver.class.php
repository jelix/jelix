<?php
/**
 * @package     jelix
 * @subpackage  kvdb
 *
 * @author      Yannick Le Guédart
 * @contributor Laurent Jouanneau
 *
 * @copyright   2009 Yannick Le Guédart, 2010-2014 Laurent Jouanneau
 *
 * @see     http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * interface for KV driver which store values in a persistent manner (in a file...).
 */
interface jIKVPersistent
{
    /**
     * synchronize the memory content with the persistent storage.
     */
    public function sync();
}

/**
 * interface for KV driver which support 'time to live' on values
 * useful to use the driver as a cache storage.
 */
interface jIKVttl
{
    /**
     * set a key/value with a ttl value.
     *
     * @param string $key   the key
     * @param string $value the value
     * @param int    $ttl   the time to live in seconds...
     *
     * @return bool false if failure, if the value is a resource...
     */
    public function setWithTtl($key, $value, $ttl);

    /**
     * delete all keys which are not any more valid.
     *
     * @return bool false if failure
     */
    public function garbage();
}

interface jIKVSet
{
    public function sAdd($skey, $value);

    public function sRemove($skey, $value);

    public function sCount($skey);

    public function sPop($skey);
}

abstract class jKVDriver
{
    /**
     * Profile for the connection in the kvdb INIfile.
     *
     * @var array
     */
    protected $_profile;

    /**
     * Name of the driver.
     *
     * @var string
     */
    protected $_driverName;

    /**
     * name of the profile.
     *
     * @var string
     */
    protected $_profileName;

    /**
     * Name of the driver.
     *
     * @var object|resource
     */
    protected $_connection;

    /**
     * Class constructor.
     *
     * Initialise profile data and create the main object
     *
     * @param array $profile
     */
    public function __construct($profile)
    {
        $this->_profile = &$profile;
        $this->_driverName = $profile['driver'];
        $this->_profileName = $profile['_name'];
        $this->_connection = $this->_connect();
    }

    /**
     * Class destructor.
     */
    public function __destruct()
    {
        if (!is_null($this->_connection)) {
            $this->_disconnect();
        }
    }

    /**
     * Gets one or several values;.
     *
     * @param array|string $key a key or an array of keys
     *
     * @return string or null if the key doesn't exist
     */
    abstract public function get($key);

    /**
     * Store a key/value.
     *
     * @param string $key   the key
     * @param string $value
     *
     * @return bool false if failure, if the value is a resource...
     */
    abstract public function set($key, $value);

    /**
     * Store a key/value. If the key already exist : error.
     *
     * @param string $key   the key
     * @param string $value
     *
     * @return bool false if failure
     */
    abstract public function insert($key, $value);

    /**
     * Store a key/value. The key should exists.
     *
     * @param string $key   the key
     * @param string $value
     *
     * @return bool false if failure
     */
    abstract public function replace($key, $value);

    /**
     * Deletes a key from the KVdb.
     *
     * @param string $key the key
     *
     * @return bool false if failure
     */
    abstract public function delete($key);

    /**
     * Flush the KVDb. Deletes all keys.
     *
     * @return bool true if it is a success
     */
    abstract public function flush();

    /**
     * append a string to an existing key value.
     *
     * @param string $key   the key of the value to modify
     * @param string $value the value to append to the current key value
     *
     * @return string the new value or false if failure
     */
    abstract public function append($key, $value);

    /**
     * prepend a string to an existing key value.
     *
     * @param string $key   the key of the value to modify
     * @param string $value the value to prepend to the current key value
     *
     * @return string the new value or false if failure
     */
    abstract public function prepend($key, $value);

    /**
     * increment a value by $incr. the key should exist and should be an integer.
     *
     * @param string $key  the key of the value
     * @param mixed  $incr the value to add to the current value
     *
     * @return int the result, or false if failure
     */
    abstract public function increment($key, $incr = 1);

    /**
     * decrement a value by $decr. the key should exist and should be an integer.
     *
     * @param string $key  the key of the value
     * @param mixed  $decr the value to substract to the current value
     *
     * @return int the result, or false if failure
     */
    abstract public function decrement($key, $decr = 1);

    abstract protected function _connect();

    abstract protected function _disconnect();

    protected function isResource($value)
    {
        if (function_exists('\\Jelix\\Core\\Utilities\\is_resource')) {
            return \Jelix\Core\Utilities\is_resource($value);
        }

        return is_resource($value);
    }
}
