<?php

namespace Jelix\Dependencies;

class Item
{
    protected $name;
    protected $_isInstalled = false;
    protected $_canBeInstalled = true;
    protected $currentVersion;
    protected $action;
    protected $nextVersion;

    protected $properties = array();

    protected $dependencies = array();

    protected $alternativeDependencies = array();

    protected $incompatibilities = array();

    /**
     * Item constructor.
     *
     * @param string $name           an name that is unique among all items
     * @param string $currentVersion
     * @param bool   $isInstalled
     * @param bool   $canBeInstalled indicate if the module can be installed automatically by the resolver
     */
    public function __construct($name, $currentVersion, $isInstalled, $canBeInstalled = true)
    {
        $this->name = $name;
        $this->_isInstalled = $isInstalled;
        $this->_canBeInstalled = $canBeInstalled;
        $this->currentVersion = $currentVersion;
        $this->action = Resolver::ACTION_NONE;
        $this->nextVersion = null;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isInstalled()
    {
        return $this->_isInstalled;
    }

    public function canBeInstalled()
    {
        return $this->_canBeInstalled;
    }

    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    public function getNextVersion()
    {
        return $this->nextVersion;
    }

    /**
     * @param int  $action      one of Resolver::ACTION_* const
     * @param null $nextVersion if action is ACTION_UPGRADE
     */
    public function setAction($action, $nextVersion = null)
    {
        $this->action = $action;
        $this->nextVersion = $nextVersion;
    }

    public function getAction()
    {
        return $this->action;
    }

    /**
     * To set properties that can be useful for the user of the Resolver.
     *
     * @param $name
     * @param $value
     */
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;
    }

    public function getProperty($name)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }

        return null;
    }

    public function addDependency($name, $version = '*', $optional = false)
    {
        $this->dependencies[$name] = array($version, $optional);
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param array $choice list of dependencies where one of them should be installed, not all
     *                      ex:
     *                      ```
     *                      [
     *                      // this dependency
     *                      '$name'=> '$version',
     *                      // or this dependency
     *                      '$name'=> '$version',
     *                      // or ...
     *                      ]
     *                      ```
     */
    public function addAlternativeDependencies(array $choice)
    {
        $this->alternativeDependencies[] = $choice;
    }

    public function getAlternativeDependencies()
    {
        return $this->alternativeDependencies;
    }

    public function addIncompatibility($name, $version = '*')
    {
        $this->incompatibilities[$name] = $version;
    }

    public function getIncompatibilities()
    {
        return $this->incompatibilities;
    }
}
