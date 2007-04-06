<?php
/**
* @package     jelix
* @subpackage  utils
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2005-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* This object is responsible to include and instancy some classes stored in the classes directory of modules.
* @package     jelix
* @subpackage  utils
* @static
*/
class jClasses {

    static protected $_instances = array();

    private function __construct(){}

    /**
     * include the given class and return an instance
     * @param string $selector the jelix selector correponding to the class
     * @return object an instance of the classe
     */
    static public function create($selector){
        $sel = new jSelectorClass($selector);
        require_once($sel->getPath());
        $class = $sel->className;
        return new $class ();
    }

    /**
     * alias of create method
     * @see jClasses::create()
     */
    static public function createInstance($selector){
        return self::create($selector);
    }

    /**
     * include the given class and return always the same instance
     *
     * @param string $selector the jelix selector correponding to the class
     * @return object an instance of the classe
     */
    static public function getService($selector){
        $sel = new jSelectorClass($selector);
        $s = $sel->toString();
        if (isset(self::$_instances[$s])) {
            return self::$_instances[$s];
        } else {
            $o = self::create($selector);
            self::$_instances[$s]=$o;
            return $o;
        }
    }

    /**
     * only include a class
     * @param string $selector the jelix selector correponding to the class
     */
    static public function inc($selector) {
        $sel = new jSelectorClass($selector);
        require_once($sel->getPath());
    }

    /**
     * include an interface
     * @param string $selector the jelix selector correponding to the interface
     * @since 1.0b2
     */
    static public function incIface($selector) {
        $sel = new jSelectorInterface($selector);
        require_once($sel->getPath());
    }
}

?>
