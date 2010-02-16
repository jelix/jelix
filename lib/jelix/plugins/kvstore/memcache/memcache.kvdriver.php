<?php
/**
 * @package     jelix
 * @subpackage  kvstore
 * @author      Yannick Le Guédart
 * @copyright   2009 Yannick Le Guédart
 *
 * @link     http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 *
 * @see http://fr2.php.net/manual/en/book.memcache.php
 */

class memcacheKVDriver extends jKVDriver implements jIKVIncrementable {

    /**
	 * Array of StdClass objects that contains host/port attributes for the
	 * memcache servers. Used only during _connection.
	 *
	 * @var array
	 */
    private $_servers = array();

    /**
	 * Should the data be compressed ? This feature is implemented sometimes
	 * in memcached drivers, and works sometimes...
	 *
	 * @var boolean
	 * @access protected
	 */
    protected $_compress = false;

    /**
	 * Connects to the memcache server
	 *
	 * The host list is in the host profile value, in the form of :
	 *
	 * host=server1:port1;server2:port2;server3;port3;...
	 *
	 * @return Memcache object
	 */
   	protected function _connect() {
        /* A host is needed */
        if (! isset($this->_profile['host'])) {
     		throw new jException(
                'jelix~db.error.driver.nohost', $this->_driverName);
        }

		/* There are 3 way to define memcache servers
		 *
		 * host=memcache_server
		 * port=memcache_port
		 *
		 * ... or...
		 *
		 * host=memcache_server1:memcache_port1[;memcache_server2:memcache_port2]*
		 *
		 * ... or...
		 *
		 * host[]=memcache_server1:memcache_port1
		 * host[]=memcache_server2:memcache_port2
		 * ...
		 */

        if (is_string($this->_profile['host'])) {
			// Case 1 : if there's a port value and no ':' in the host string

			if (isset($this->_profile['port'])
				and
				strpos($this->_profile['host'], ':') === false)
			{
				$server 		= new stdClass();
				$server->host 	= $this->_profile['host'];
				$server->port 	= $this->_profile['port'];

				$this->_servers[] = $server;
			}
			else { // Case 2 : no port => concatened string

				foreach (split(';', $this->_profile['host']) as $host_port) {
					$hp = split(':', $host_port);

					$server 		= new stdClass();
					$server->host 	= $hp[0];
					$server->port 	= $hp[1];

					$this->_servers[] = $server;
				}
			}
		}

		// Case 3 : array of host:port string
		elseif (is_array($this->_profile['host'])) {
			foreach ($this->_profile['host'] as $host_port) {
				$hp = split(':', $host_port);
				$server 		= new stdClass();
				$server->host 	= $hp[0];
				$server->port 	= $hp[1];
				$this->_servers[] = $server;
			}
		}

        /* OK, let's connect now */

        $cnx = new Memcache();

		$oneServerAvalaible = false;

		foreach ($this->_servers as $s) {
			$result = @$cnx->connect($s->host, $s->port);

			if (! $oneServerAvalaible and $result) {
				$oneServerAvalaible = true;
			}
		}

		if (! $oneServerAvalaible) {
     		throw new jException(
                'jelix~db.error.connection', $this->_driverName);
		}

        /* Setting the $_compress flag */
        if (isset($this->_profile['compress'])
                and ($this->_profile['compress'] == 1)) {
            $this->_compress = true;
        }

        return $cnx;
	}

    /**
	 * Disconnect from the memcache server
	 *
	 * @return Memcache object
	 */

   	protected function _disconnect() {
        $this->_connection->close();
    }

    protected function _doGet($key) {
        return $this->_connection->get($key);
    }

    protected function _doSet($key, $value, $ttl) {
        return $this->_connection->set(
            $key,
            $value,
            (($this->_compress) ? MEMCACHE_COMPRESSED : 0),
            $ttl
        );
    }

    protected function _doDelete($key) {
        return $this->_connection->delete($key);
    }

    protected function _doFlush() {
        return $this->_connection->flush();
    }

	/* jIKVIncrementable ---------------------------------------------------- */

    public function increment($key, $incvalue = 1) {
        return $this->_connection->increment($key, $incvalue);
    }

    public function decrement($key, $decvalue = 1) {
        return $this->_connection->decrement($key, $decvalue);
    }
}
