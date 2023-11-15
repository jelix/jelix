<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 * @contributor Christophe Thiriot
 *
 * @copyright   2005-2023 Laurent Jouanneau
 * @copyright   2008 Christophe Thiriot
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core;

/**
 * This object is responsible to include and instancy some classes stored in the classes directory of modules.
 *
 * @static
 */
class ModuleClass
{
    protected static $_instances = array();

    protected static $_bindings = array();

    private function __construct()
    {
    }

    /**
     * include the given class and return an instance.
     *
     * @param string $selector the jelix selector correponding to the class
     *
     * @return object an instance of the classe
     */
    public static function create($selector)
    {
        $sel = new Selector\ClassSelector($selector);

        require_once $sel->getPath();
        $class = $sel->className;

        return new $class();
    }

    /**
     * alias of create method.
     *
     * @see ModuleClass::create()
     *
     * @param mixed $selector
     */
    public static function createInstance($selector)
    {
        return self::create($selector);
    }

    /**
     * include the given class and return always the same instance.
     *
     * @param string $selector the jelix selector correponding to the class
     *
     * @return object an instance of the classe
     */
    public static function getService($selector)
    {
        $sel = new Selector\ClassSelector($selector);
        $s = $sel->toString();
        if (isset(self::$_instances[$s])) {
            return self::$_instances[$s];
        }
        $o = self::create($selector);
        self::$_instances[$s] = $o;

        return $o;
    }

    /**
     * only include a class.
     *
     * @param string $selector the jelix selector correponding to the class
     */
    public static function inc($selector)
    {
        $sel = new Selector\ClassSelector($selector);

        require_once $sel->getPath();
    }

    /**
     * include an interface.
     *
     * @param string $selector the jelix selector correponding to the interface
     *
     * @since 1.0b2
     */
    public static function incIface($selector)
    {
        $sel = new Selector\InterfaceSelector($selector);

        require_once $sel->getPath();
    }
}
