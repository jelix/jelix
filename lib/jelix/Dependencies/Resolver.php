<?php

namespace Jelix\Dependencies;

use Jelix\Version\VersionComparator;

class Resolver
{
    const ACTION_NONE = 0;
    const ACTION_INSTALL = 1;
    const ACTION_UPGRADE = 2;
    const ACTION_REMOVE = 3;

    /**
     * @var Item[]
     */
    protected $items = array();

    public function __construct()
    {
    }

    /**
     * @param Item $item
     * @throws Exception
     */
    public function addItem(Item $item)
    {
        if (isset($this->item[$item->getName()])) {
            throw new Exception('Item has already been added', 1);
        }
        $this->items[$item->getName()] = $item;
    }

    /**
     * @var Item[]
     */
    protected $chain = array();

    protected $checkedItems = array();
    protected $circularDependencyTracker = array();
    protected $circularReverseDependencyTracker = array();

    /**
     * Return the list of item to process, in the right order.
     * Their action property may have changed and indicate what
     * to do with them.
     *
     * @return Item[] list of item
     *
     * @throws ItemException when there is a circular dependency...
     */
    public function getDependenciesChainForInstallation()
    {
        $this->checkedItems = array();
        $this->chain = array();

        foreach ($this->items as $itemName => $item) {
            $this->circularDependencyTracker = array();
            $this->circularReverseDependencyTracker = array();
            if (isset($this->checkedItems[$itemName])) {
                continue;
            }
            if ($item->getAction() == self::ACTION_NONE) {
                continue;
            }
            if ($item->getAction() == self::ACTION_REMOVE) {
                if (!$item->isInstalled()) {
                    $this->checkedItems[$itemName] = true;
                    continue;
                }
                $this->_checkReverseDependencies($item);
            } elseif ($item->getAction() == self::ACTION_INSTALL) {
                if ($item->isInstalled()) {
                    $this->checkedItems[$itemName] = true;
                    continue;
                }
                $this->_checkDependencies($item);
            } else {
                $this->_checkDependencies($item);
            }
            $this->chain[] = $item;
        }

        $incompatibilities = array();

        // get conflict constraint from installed components
        foreach($this->items as $itemName => $item) {
            if (($item->getAction() == self::ACTION_NONE && $item->isInstalled()) ||
                $item->getAction() == self::ACTION_INSTALL
            ) {
                foreach ($item->getIncompatibilities() as $forbiddenComponent => $version) {
                    $incompatibilities[] = array(
                        'name'=>$forbiddenComponent,
                        'version' => $version,
                        'forbiddenby'=>$itemName);
                }
            }
        }

        // verify that forbidden modules are not installed or will not be installed
        foreach ($incompatibilities as $forbiddenComponent) {
            $name = $forbiddenComponent['name'];
            if (isset($this->items[$name])) {
                if ($this->items[$name]->isInstalled()) {
                    throw new ItemException('Item '.$name.' is in conflicts with item '.$forbiddenComponent['forbiddenby'],
                        $this->items[$name], 7, $this->items[$forbiddenComponent['forbiddenby']]);
                }
            }
            foreach ($this->chain as $item) {
                if ($item->getName() == $name && $item->getAction() == self::ACTION_INSTALL) {
                    throw new ItemException('Item '.$name.' is in conflicts with item '.$forbiddenComponent['forbiddenby'],
                        $item, 8, $this->items[$forbiddenComponent['forbiddenby']]);
                }
            }
        }

        return $this->chain;
    }

    /**
     * check dependencies of an item.
     *
     * @param Item   $item
     * @param string $epId
     */
    protected function _checkDependencies(Item $item)
    {
        if (isset($this->circularDependencyTracker[$item->getName()])) {
            throw new ItemException('Circular dependency! Cannot process the item '.$item->getName(), $item, 1);
        }

        $this->circularDependencyTracker[$item->getName()] = true;

        $missingItems = array();
        foreach ($item->getDependencies() as $depItemName => $depItemVersion) {
            $depItem = null;
            if (isset($this->items[$depItemName])) {
                $depItem = $this->items[$depItemName];
            } else {
                $missingItems[] = $depItemName;
                continue;
            }

            if ($depItem->getAction() == self::ACTION_REMOVE) {
                throw new ItemException('Item '.$depItemName.', needed by item '.$item->getName().', should be removed at the same time', $item, 3, $depItem);
            }

            if (isset($this->checkedItems[$depItemName])) {
                continue;
            }

            if ($depItem->getAction() == self::ACTION_NONE) {
                $version = $depItem->getCurrentVersion();
                if (!VersionComparator::compareVersionRange($version, $depItemVersion)) {
                    throw new ItemException("Version of item '".$depItemName."' does not match required version by item ".$item->getName(), $item, 2, $depItem);
                }
                if (!$depItem->isInstalled()) {
                    $depItem->setAction(self::ACTION_INSTALL);
                    $this->_checkDependencies($depItem);
                    $this->chain[] = $depItem;
                }
            } elseif ($depItem->getAction() == self::ACTION_INSTALL) {
                $version = $depItem->getCurrentVersion();
                if (!VersionComparator::compareVersionRange($version, $depItemVersion)) {
                    throw new ItemException("Version of item '".$depItemName."' does not match required version by item ".$item->getName(), $item, 2, $depItem);
                }
                $this->_checkDependencies($depItem);
                $this->chain[] = $depItem;
            } elseif ($depItem->getAction() == self::ACTION_UPGRADE) {
                $version = $depItem->getNextVersion();
                if (!VersionComparator::compareVersionRange($version, $depItemVersion)) {
                    throw new ItemException("Version of item '".$depItemName."' does not match required version by item ".$item->getName(), $item, 2, $depItem);
                }
                $this->_checkDependencies($depItem);
                $this->chain[] = $depItem;
            }
        }

        $this->checkedItems[$item->getName()] = true;
        unset($this->circularDependencyTracker[$item->getName()]);

        if ($missingItems) {
            throw new ItemException('For item '.$item->getName().', some items are missing :'.implode(',', $missingItems), $item, 6, $missingItems);
        }
    }

    /**
     * check reverse dependencies of an item to remove.
     *
     * Find all items having the given item as dependency, and remove them
     *
     * @param Item   $item
     * @param string $epId
     */
    protected function _checkReverseDependencies(Item $item)
    {
        if (isset($this->circularReverseDependencyTracker[$item->getName()])) {
            throw new ItemException('Circular reverse dependency! Cannot process the item '.$item->getName(), $item, 4);
        }

        $this->circularReverseDependencyTracker[$item->getName()] = true;
        foreach ($this->items as $revdepItemName => $revdepItem) {
            $dependencies = $revdepItem->getDependencies();
            if (!isset($dependencies[$item->getName()])) {
                continue;
            }

            if ($revdepItem->getAction() == self::ACTION_INSTALL || $revdepItem->getAction() == self::ACTION_UPGRADE) {
                throw new ItemException('Item '.$revdepItemName.' should be removed because of the removal of one of its dependencies, '.$item->getName().', but it asked to be install/upgrade at the same time', $item, 5, $revdepItem);
            }

            if (isset($this->checkedItems[$revdepItemName])) {
                continue;
            }

            if ($revdepItem->getAction() == self::ACTION_REMOVE) {
                $this->_checkReverseDependencies($revdepItem);
                $this->chain[] = $revdepItem;
            } elseif ($revdepItem->getAction() == self::ACTION_NONE) {
                if ($revdepItem->isInstalled()) {
                    $revdepItem->setAction(self::ACTION_REMOVE);
                    $this->_checkReverseDependencies($revdepItem);
                    $this->chain[] = $revdepItem;
                }
            }
        }

        $this->checkedItems[$item->getName()] = true;
        unset($this->circularReverseDependencyTracker[$item->getName()]);
    }
}
