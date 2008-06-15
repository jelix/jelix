<?php
/**
 * @package     jelix
 * @subpackage  utils
 * @author      Christophe THIRIOT
 * @copyright   2008 Christophe THIRIOT
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 * @since 1.1
 */

/**
 * Services binding for jelix
 *
 * @package     jelix
 * @subpackage  utils
 */
class jBinding {
    /**
     * @var jSelectorInterface|jSelectorClass Called selector
     */
    protected $fromSelector = null;

    /**
     * selector to the binded class (string form)
     */
    protected $toSelector = null;

    /**
     * resulting binded instance
     */
    protected $instance = null;

    /**
     * __constructor
     * @param jSelectorInterface|jSelectorClass
     * @return void
     */
    public function __construct($selector) {
        require_once($selector->getPath());
        $this->fromSelector = $selector;
    }

    /**
     * Bind the selector to the class specified
     * Even if this instance is already defined (BE CAREFUL !!! singleton is bypassed)
     *
     * @param string $toselector
     * @return jBinding $this
     */
    public function to($toselector) {
        $this->toSelector = new jSelectorClass($toselector);
        $this->instance   = null;
        return $this;
    }

    /**
     * Bind the selector to the specified instance
     * Even if this instance is already defined (BE CAREFUL !!! singleton is bypassed)
     *
     * @param  mixed    $instance
     * @return jBinding $this
     */
    public function toInstance($instance) {
        $this->instance   = $instance;
        $this->toSelector = null;
        return $this;
    }

    /**
     * Get the binded instance
     *
     * @return mixed
     */
    public function getInstance($singleton=true) {
        $instance = null;
        if (true === $singleton && $this->instance !== null) {
            $instance = $this->instance;
        } elseif (true === $singleton && $this->instance === null) {
            $instance = $this->instance = $this->_createInstance();
        } else { // all cases with $singleton === false
            $instance = $this->_createInstance();
        }
        return $instance;
    }

    /**
     * Create the binded selector if not initialzed yet
     * 
     * @return mixed 
     */
    protected function _createInstance() {
        if ($this->toSelector === null) {
            $this->instance   = null;
            $this->toSelector = $this->_getClassSelector();    
        }
        return jClasses::create($this->toSelector->toString());
    }

    /**
     * Get the name of the binded class without creating this class
     *
     * @return string
     */
    public function getClassName() {
        $class_name = null;
        if ($this->instance !== null) {
            $class_name = get_class($this->instance);
        } elseif ($this->toSelector !== null) {
            $class_name = $this->toSelector->className;
        } else {
            $class_name = $this->_getClassSelector()->className;
        }
        return $class_name;
    }

    /**
     * Get the selector to the binded class
     * Protected because this does not work if called after a simple __construct() and a toInstance()
     *
     * @return string
     */
    protected function _getClassSelector() {
        $class_selector = null;

        // the instance is not already created
        if ($this->toSelector === null && $this->instance === null) {
            $str_selector      = $this->fromSelector->toString();
            $str_selector_long = $this->fromSelector->toString(true);

            // 1) verify that a default implementation is specified in the jelix config file
            global $gJConfig;
            if (isset($gJConfig->Bindings)) {
                $conf = $gJConfig->Bindings;

                // No '~' allowed as key of a ini file, we use '-' instead
                $conf_selector      = str_replace('~', '-', $str_selector);
                $conf_selector_long = str_replace('~', '-', $str_selector_long);
                // get the binding corresponding to selector, long or not
                $str_fromselector = null;
                if (isset($conf[$conf_selector])) {
                    $str_fromselector = $conf_selector;
                } elseif (isset($conf[$conf_selector_long])) {
                    $str_fromselector = $conf_selector_long;
                }

                if ($str_fromselector !== null) {
                    $this->fromSelector = jSelectorFactory::create($str_selector_long, 'iface');
                    return $this->toSelector = new jSelectorClass($conf[$str_fromselector]);
                }
            }

            // 2) see if a default implementation is specified in the source class
            $class_selector = @constant($this->fromSelector->className . '::JBINDING_BINDED_IMPLEMENTATION');
            if ($class_selector!==null) return $this->toSelector = new jSelectorClass($class_selector);

            // 3) If the source is a class, then use it as the default implementation
            if (true === ($this->fromSelector instanceof jSelectorClass)) {
                return $this->toSelector = $this->fromSelector;
            }

            throw new jException('jelix~errors.bindings.nobinding', array($this->fromSelector->toString(true)));
        }
    }
}
?>
