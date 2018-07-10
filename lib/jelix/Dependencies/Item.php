<?php

namespace Jelix\Dependencies;

class Item
{
    protected $name;
    protected $_isInstalled;
    protected $currentVersion;
    protected $action;
    protected $nextVersion;

    protected $properties = array();

    protected $dependencies = array();

    protected $incompatibilities = array();

    /**
     * Item constructor.
     *
     * @param string $name           an name that is unique among all items
     * @param bool   $isInstalled
     * @param string $currentVersion
     * @param int    $action         one of Resolver::ACTION_* const
     * @param null   $nextVersion    if action is ACTION_UPGRADE
     */
    public function __construct($name, $isInstalled, $currentVersion, $action = 0, $nextVersion = null)
    {
        $this->name = $name;
        $this->_isInstalled = $isInstalled;
        $this->currentVersion = $currentVersion;
        $this->action = $action;
        $this->nextVersion = $nextVersion;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isInstalled()
    {
        return $this->_isInstalled;
    }

    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    public function getNextVersion()
    {
        return $this->nextVersion;
    }

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

    public function addDependency($name, $version = '*')
    {
        $this->dependencies[$name] = $version;
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function addIncompatibility($name, $version = '*') {
        $this->incompatibilities[$name] = $version;
    }

    public function getIncompatibilities() {
        return $this->incompatibilities;
    }
}
