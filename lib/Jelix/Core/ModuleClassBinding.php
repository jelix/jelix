<?php
/**
 * @author      Christophe Thiriot
 * @contributor Laurent Jouanneau
 *
 * @copyright   2008 Christophe Thiriot, 2008-2014 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 *
 * @since 1.1
 */

namespace Jelix\Core;

/**
 * Services binding for jelix.
 *
 * @experimental  This class is EXPERIMENTAL. Its API and its behaviors could
 * change in future version
 */
class ModuleClassBinding
{
    /**
     * @var Selector\ClassSelector|Selector\InterfaceSelector Called selector
     */
    protected $fromSelector;

    /**
     * selector to the binded class (string form).
     *
     * @var Selector\ClassSelector
     */
    protected $toSelector;

    /**
     * resulting binded instance.
     */
    protected $instance;

    /**
     * __constructor.
     *
     * @param Selector\ClassSelector|Selector\InterfaceSelector $selector the selector of the class
     */
    public function __construct($selector)
    {
        require_once $selector->getPath();
        $this->fromSelector = $selector;
    }

    /**
     * Bind the selector to the class specified
     * Even if this instance is already defined (BE CAREFUL !!! singleton is bypassed).
     *
     * @param string $toselector
     *
     * @return ModuleClassBinding $this
     */
    public function to($toselector)
    {
        $this->toSelector = new Selector\ClassSelector($toselector);
        $this->instance = null;

        return $this;
    }

    /**
     * Bind the selector to the specified instance
     * Even if this instance is already defined (BE CAREFUL !!! singleton is bypassed).
     *
     * @param mixed $instance
     *
     * @return ModuleClassBinding $this
     */
    public function toInstance($instance)
    {
        $this->instance = $instance;
        $this->toSelector = null;

        return $this;
    }

    /**
     * Get the binded instance.
     *
     * @param mixed $singleton
     *
     * @return mixed
     */
    public function getInstance($singleton = true)
    {
        if ($singleton === true && $this->instance !== null) {
            return $this->instance;
        }
        $this->instance = $this->_createInstance();

        return $this->instance;
    }

    /**
     * Create the binded selector if not initialized yet.
     *
     * @return mixed
     */
    protected function _createInstance()
    {
        if ($this->toSelector === null) {
            $this->instance = null;
            $this->toSelector = $this->_getClassSelector();
        }

        return ModuleClass::create($this->toSelector->toString());
    }

    /**
     * Get the name of the binded class without creating this class.
     *
     * @return string
     */
    public function getClassName()
    {
        if ($this->instance !== null) {
            return get_class($this->instance);
        }
        if ($this->toSelector !== null) {
            return $this->toSelector->className;
        }

        return $this->_getClassSelector()->className;
    }

    /**
     * Get the selector to the binded class
     * Protected because this does not work if called after a simple __construct() and a toInstance().
     *
     * @throws \jException
     *
     * @return Selector\ClassSelector
     */
    protected function _getClassSelector()
    {
        $class_selector = null;

        // the instance is not already created
        if ($this->toSelector === null && $this->instance === null) {
            $str_selector = $this->fromSelector->toString();
            $str_selector_long = $this->fromSelector->toString(true);

            // 1) verify that a default implementation is specified in the jelix config file
            $config = App::config();
            if (isset($config->classbindings) && count($config->classbindings)) {
                $conf = $config->classbindings;

                // No '~' allowed as key of a ini file, we use '-' instead
                $conf_selector = str_replace('~', '-', $str_selector);
                $conf_selector_long = str_replace('~', '-', $str_selector_long);
                // get the binding corresponding to selector, long or not
                $str_fromselector = null;
                if (isset($conf[$conf_selector])) {
                    $str_fromselector = $conf_selector;
                } elseif (isset($conf[$conf_selector_long])) {
                    $str_fromselector = $conf_selector_long;
                }

                if ($str_fromselector !== null) {
                    $this->fromSelector = Selector\Factory::create($str_selector_long, 'iface');

                    return $this->toSelector = new Selector\ClassSelector($conf[$str_fromselector]);
                }
            }

            // 2) see if a default implementation is specified in the source class
            $constname = $this->fromSelector->className.'::JBINDING_BINDED_IMPLEMENTATION';
            if (defined($constname)) { // check first, constant() crashes on some php version when the const does not exist
                $class_selector = constant($constname);
                if ($class_selector !== null) {
                    return $this->toSelector = new Selector\ClassSelector($class_selector);
                }
            }

            // 3) If the source is a class, then use it as the default implementation
            if (($this->fromSelector instanceof Selector\ClassSelector) === true) {
                return $this->toSelector = $this->fromSelector;
            }

            throw new \jException('jelix~errors.bindings.nobinding', array($this->fromSelector->toString(true)));
        }

        return $class_selector;
    }
}
