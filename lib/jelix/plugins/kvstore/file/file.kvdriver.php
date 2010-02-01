<?php
/**
 * @package     jelix
 * @subpackage  kvstore
 * @author      Yannick Le Guédart
 * @contributor Laurent Jouanneau
 * @copyright   2009 Yannick Le Guédart, 2010 Laurent Jouanneau
 *
 * @link     http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

define ('TMP_DIR', (JELIX_APP_TEMP_PATH . 'filekv'));

class fileKVDriver extends jKVDriver {

    /**
	 * "Connects" to the fileServer
	 *
	 * @return fileServer object
	 *
	 * @access protected
	 */
   	protected function _connect() {
        $cnx = new fileServer();

        return $cnx;
	}

   	protected function _disconnect() {}

    protected function _doGet($key) {
        return $this->_connection->get($key);
    }

    protected function _doSet($key, $value, $ttl) {
        return $this->_connection->set(
            $key,
            $value,
            $ttl
        );
    }

    protected function _doDelete($key) {
        return $this->_connection->delete($key);
    }

    protected function _doFlush() {
        return $this->_connection->flush();
    }
}

class fileServer {
    public function __construct () {
        // Create temp kvFile directory if necessary

        if (! file_exists(TMP_DIR)) {
            jFile::createDir(TMP_DIR);
        }
    }

	/**
	* set
	*
	* @param $key	a key (unique name) to identify the cached info
	* @param $value	the value to cache
	* @param $ttl how many seconds will the info be cached
	*
	* @return boolean whether the action was successful or not
	*/
	public static function set($key, $value, $ttl) {
		$r = false;

		if ($fl = @fopen(TMP_DIR . '/.flock', 'w+')) {
			if (flock($fl, LOCK_EX)) {
				// mutex zone

				$md5 	= md5($key);
				$subdir = $md5[0].$md5[1];

                if (! file_exists(TMP_DIR . '/' . $subdir)) {
                    jFile::createDir(TMP_DIR . '/' . $subdir);
                }

				// write data to cache
                $fn = TMP_DIR . '/' . $subdir . '/' . $md5;
				if ($f = @gzopen($fn . '.tmp', 'w')) {
					// write temporary file
					fputs($f, base64_encode(serialize($value)));
					fclose($f);

					// change time of the file to the expiry time
					@touch("$fn.tmp", time() + $ttl);

					// rename the temporary file
					$r = @rename("$fn.tmp", $fn);
				}

				// end of mutex zone
				flock($fl, LOCK_UN);
			}
		}

		return $r;
	}

	/**
	* get
	*
	* @param $key	the key (unique name) that identify the cached info
	*
	* @return mixed false if the cached info does not exist or has expired
	*               or the data if the info exists and is valid
	*/
	public static function get($key) {
		$r = false;

		// the name of the file
		$md5    = md5($key);
		$subdir = $md5[0].$md5[1];

		$fn = TMP_DIR . '/' . $subdir . '/' . $md5;

		// file does not exists
		if (! file_exists($fn)) {
            return false;
        }

		//  data has expired => delete file and return false
        if (@filemtime($fn) < time()) {
            @unlink($fn);
            return false;
        }

		// date is valid
		if ($f = @gzopen($fn, 'rb')) {
			$r = '';

			while ($read = fread($f, 1024)) {
				$r .= $read;
			}

			fclose($f);
		}

		// return cached info
		return @unserialize(base64_decode($r));
	}

	/**
	* delete
	*
	* @param $key	a key (unique 0name) to identify the cached info
	*
	* @return boolean whether the action was successful or not
	*/
	public static function delete($key) {
 		// the name of the file
		$md5    = md5($key);
		$subdir = $md5[0].$md5[1];

		$fn = TMP_DIR . '/' . $subdir . '/' . $md5;

        return @unlink($fn);
    }

	/**
	* flush
	*
	* @return boolean whether the action was successful or not
	*/
	public static function flush() {
        return @unlink(TMP_DIR);
    }
}
