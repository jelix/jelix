<?php

/**
 * @package     jelix
 * @subpackage  jpref
 *
 * @author    Florian Lonqueu-Brochard
 * @copyright 2012 Florian Lonqueu-Brochard
 *
 * @see      http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
class jPref
{
    protected function __construct()
    {
    }

    protected static $_connection;

    protected static $_prefs;

    protected static $_prefix = 'jpref_';

    protected static function _getConnection()
    {
        if (!self::$_connection) {
            self::$_connection = jKVDb::getConnection('jpref');
        }

        return self::$_connection;
    }

    /**
     * Get a system preference.
     *
     * @param string $key The corresponding to the preference
     *
     * @return mixed preference value
     */
    public static function get($key)
    {
        if (isset(self::$_prefs[$key])) {
            return self::$_prefs[$key];
        }

        $cnx = self::_getConnection();
        $result = $cnx->get(self::$_prefix.$key);

        if (!$result) {
            self::$_prefs[$key] = null;

            return;
        }

        $type = $result[0];
        if (strlen($result) > 2) { // check, else we'll have false has result
            $value = substr($result, 2);
        } else {
            $value = '';
        }

        if ($type == 'i') {
            //integer
            $value = (int) $value;
        } elseif ($type == 'b') { //boolean
            $value = (bool) $value;
        } elseif ($type == 'd') { // decimal
            $value = (float) $value;
        }

        self::$_prefs[$key] = $value;

        return $value;
    }

    /**
     * Set a system preference.
     *
     * @param string $key   The corresponding to the preference
     * @param mixed  $value preference value
     */
    public static function set($key, $value)
    {
        self::$_prefs[$key] = $value;

        $cnx = self::_getConnection();

        if (is_int($value)) {
            $prefix = 'i';
        } elseif (is_bool($value)) {
            $prefix = 'b';
            if (!$value) {
                $value = '0';
            }
        } elseif (is_float($value)) {
            $prefix = 'd';
        } else {
            $prefix = 's';
        }

        $prefix .= '|';

        $cnx->set(self::$_prefix.$key, $prefix.$value);
    }

    /**
     * Clear the local cache.
     */
    public static function clearCache()
    {
        self::$_prefs = null;
    }
}
