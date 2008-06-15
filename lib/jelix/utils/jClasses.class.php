<?php
/**
* @package     jelix
* @subpackage  utils
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @contributor Christophe Thiriot
* @copyright   2005-2007 Laurent Jouanneau
* @copyright   2008 Christophe Thiriot
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

    static protected $_bindings = array();

    private function __construct(){}

    /**
     * include the given class and return an instance
     * @param string $selector the jelix selector correponding to the class
     * @return object an instance of the classe
     */
    static public function create($selector) {
        $sel = new jSelectorClass($selector);
        require_once($sel->getPath());
        $class = $sel->className;
        return new $class ();
    }

    /**
     * Shortcut to corresponding jBinding::getInstance() but without singleton
     * The binding is recreated each time (be careful about performance)
     * 
     * @param string $selector  Selector to a bindable class|interface
     * @return object           Corresponding instance
     * @since 1.1
     */
    static public function createBinded($selector) {
        return self::getBinding($selector)->getInstance(false);
    }

    /**
     * alias of create method
     * @see jClasses::create()
     */
    static public function createInstance($selector) {
        return self::create($selector);
    }

    /**
     * include the given class and return always the same instance
     *
     * @param string $selector the jelix selector correponding to the class
     * @return object an instance of the classe
     */
    static public function getService($selector) {
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
     * Shortcut to corresponding jBinding::getInstance() 
     * 
     * @param string $selector  Selector to a bindable class|interface
     * @return object           Corresponding instance
     * @since 1.1
     */
    static public function getBindedService($selector) {
        return self::getBinding($selector)->getInstance();
    }

    /**
     * Alias of self::getBinding method. Better for use like this : jClasses::bind('selector').to('classselector')
     * 
     * @param  string $selector 
     * @return jBinding
     * @see jClasses::bind
     * @since 1.1
     */
    static public function bind($selector) {
        return self::getBinding($selector);
    }

    /**
     * Get the binding corresponding to the specified selector.
     * Better for use like this : jClasses::getBinding($selector)->getClassName()
     * 
     * @param string $selector
     * @param bool   $singleton if this binding should be a singleton or not
     * @return jBinding
     * @see jClasses::bind
     * @since 1.1
     */
    static public function getBinding($selector) {
        $osel = jSelectorFactory::create($selector, 'iface');
        $s    = $osel->toString(true);

        if (!isset(self::$_bindings[$s])) {
            self::$_bindings[$s] = new jBinding($osel);
        }

        return self::$_bindings[$s];
    }

    /**
     * Reset the defined bindings (should only use it for unit tests)
     * 
     * @return void
     * @since 1.1
     */
    static public function resetBindings() {
        self::$_bindings = array();
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
