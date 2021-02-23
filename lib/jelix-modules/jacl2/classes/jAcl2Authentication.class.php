<?php
/**
 * @package     jelix_modules
 * @subpackage  jacl2
 *
 * @author      Laurent Jouanneau
 * @copyright   2020 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
 */

/**
 * Makes the glue between jAcl2 and an authentication library.
 *
 * @since 1.7.6
 */
class jAcl2Authentication
{
    /**
     * @var jAcl2AuthAdapterInterface
     */
    protected static $adapter;

    /**
     * return the object that brings support to the current authentication system.
     *
     * @throws Exception
     *
     * @return jAcl2AuthAdapterInterface
     */
    public static function getAdapter()
    {
        if (!self::$adapter) {
            $class = jApp::config()->acl2['authAdapterClass'];
            if (!class_exists($class)) {
                throw new Exception('jAcl2: class indicated into authAdapterClass does not exist');
            }
            self::$adapter = new $class();
        }

        return self::$adapter;
    }
}
