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

    protected $allowToForceInstallDependencies = true;

    /**
     * Return the list of item to process, in the right order.
     * Their action property may have changed and indicate what
     * to do with them.
     *
     * @param bool  $allowToInstallDependencies      true if the resolver is authorized
     *                                               to force the installation of dependencies that have not the
     *                                               the action flag ACTION_INSTALL
     * @param mixed $allowToForceInstallDependencies
     *
     * @throws ItemException when there is a circular dependency...
     *
     * @return Item[] list of item
     */
    public function getDependenciesChainForInstallation($allowToForceInstallDependencies = true)
    {
        $this->allowToForceInstallDependencies = $allowToForceInstallDependencies;
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
                if ($item->isInstalled() && !$allowToForceInstallDependencies) {
                    $this->checkedItems[$itemName] = true;

                    continue;
                }
                $this->_checkDependencies($item);
            } else { // self::ACTION_UPGRADE
                $this->_checkDependencies($item);
            }
            $this->chain[] = $item;
        }

        $incompatibilities = array();

        // get conflict constraint from installed components
        foreach ($this->items as $itemName => $item) {
            if (($item->getAction() == self::ACTION_NONE && $item->isInstalled()) ||
                $item->getAction() == self::ACTION_INSTALL
            ) {
                foreach ($item->getIncompatibilities() as $forbiddenComponent => $version) {
                    $incompatibilities[] = array(
                        'name' => $forbiddenComponent,
                        'version' => $version,
                        'forbiddenby' => $itemName, );
                }
            }
        }

        // verify that forbidden modules are not installed or will not be installed
        foreach ($incompatibilities as $forbiddenComponent) {
            $name = $forbiddenComponent['name'];
            if (isset($this->items[$name])) {
                if ($this->items[$name]->isInstalled() && $this->items[$name]->getAction() != self::ACTION_REMOVE) {
                    throw new ItemException(
                        'Item '.$name.' is in conflicts with item '.$forbiddenComponent['forbiddenby'],
                        $this->items[$name],
                        ItemException::ERROR_INSTALLED_ITEM_IN_CONFLICT,
                        $this->items[$forbiddenComponent['forbiddenby']]
                    );
                }
            }
            foreach ($this->chain as $item) {
                if ($item->getName() == $name && $item->getAction() == self::ACTION_INSTALL) {
                    throw new ItemException(
                        'Item '.$name.' is in conflicts with item '.$forbiddenComponent['forbiddenby'],
                        $item,
                        ItemException::ERROR_ITEM_TO_INSTALL_IN_CONFLICT,
                        $this->items[$forbiddenComponent['forbiddenby']]
                    );
                }
            }
        }

        return $this->chain;
    }

    /**
     * check dependencies of an item.
     *
     * @param string $epId
     */
    protected function _checkDependencies(Item $item)
    {
        if (isset($this->circularDependencyTracker[$item->getName()])) {
            throw new ItemException(
                'Circular dependency! Cannot process the item '.$item->getName(),
                $item,
                ItemException::ERROR_CIRCULAR_DEPENDENCY
            );
        }

        $this->circularDependencyTracker[$item->getName()] = true;

        $missingItems = array();
        foreach ($item->getDependencies() as $depItemName => $depItemInfo) {
            list($depItemVersion, $depItemOptional) = $depItemInfo;
            $depItem = null;

            if (isset($this->items[$depItemName])) {
                $depItem = $this->items[$depItemName];
            } else {
                if (!$depItemOptional) {
                    $missingItems[] = $depItemName;
                }

                continue;
            }

            if ($depItem->getAction() == self::ACTION_REMOVE) {
                throw new ItemException(
                    'Item '.$depItemName.', needed by item '.$item->getName().', should not be removed at the same time',
                    $item,
                    ItemException::ERROR_REMOVED_ITEM_IS_NEEDED,
                    $depItem
                );
            }

            if (isset($this->checkedItems[$depItemName])) {
                continue;
            }

            if ($depItem->getAction() == self::ACTION_NONE) {
                $version = $depItem->getCurrentVersion();
                if (!VersionComparator::compareVersionRange($version, $depItemVersion)) {
                    throw new ItemException(
                        "Version of item '".$depItemName."' ({$version}) does not match required version by item ".$item->getName()." ({$depItemVersion})",
                        $item,
                        ItemException::ERROR_BAD_ITEM_VERSION,
                        $depItem
                    );
                }
                if (!$depItem->isInstalled()) {
                    if (!$depItem->canBeInstalled() || !$this->allowToForceInstallDependencies) {
                        throw new ItemException(
                            "item '".$depItemName."' needed by ".$item->getName().'cannot be installed ',
                            $item,
                            ItemException::ERROR_DEPENDENCY_CANNOT_BE_INSTALLED,
                            $depItem
                        );
                    }
                    $depItem->setAction(self::ACTION_INSTALL);
                    $this->_checkDependencies($depItem);
                    $this->chain[] = $depItem;
                }
            } elseif ($depItem->getAction() == self::ACTION_INSTALL) {
                $version = $depItem->getCurrentVersion();
                if (!VersionComparator::compareVersionRange($version, $depItemVersion)) {
                    throw new ItemException(
                        "Version of item '".$depItemName."' ({$version}) does not match required version by item ".$item->getName()." ({$depItemVersion})",
                        $item,
                        ItemException::ERROR_BAD_ITEM_VERSION,
                        $depItem
                    );
                }
                $this->_checkDependencies($depItem);
                $this->chain[] = $depItem;
            } elseif ($depItem->getAction() == self::ACTION_UPGRADE) {
                $version = $depItem->getNextVersion();
                if (!VersionComparator::compareVersionRange($version, $depItemVersion)) {
                    throw new ItemException(
                        "Version of item '".$depItemName."' ({$version}) does not match required version by item ".$item->getName()." ({$depItemVersion})",
                        $item,
                        ItemException::ERROR_BAD_ITEM_VERSION,
                        $depItem
                    );
                }
                $this->_checkDependencies($depItem);
                $this->chain[] = $depItem;
            }
        }

        foreach ($item->getAlternativeDependencies() as $choiceList) {
            $choiceDepInstalled = array();
            $choiceDepToInstall = array();
            $choiceWillBeInstalled = array();
            $choiceMissing = array();

            foreach ($choiceList as $depItemName => $depItemVersion) {
                if (!isset($this->items[$depItemName])) {
                    $choiceMissing[] = $depItemName;
                    // the item is not known, so this is not a candidate to choose
                    continue;
                }
                $depItem = $this->items[$depItemName];
                if ($depItem->getAction() == self::ACTION_REMOVE) {
                    $choiceMissing[] = $depItemName;
                    // the item will be remove, so this is not a candidate to choose
                    continue;
                }
                if ($depItem->getAction() == self::ACTION_NONE) {
                    $version = $depItem->getCurrentVersion();
                    if (!VersionComparator::compareVersionRange($version, $depItemVersion)) {
                        $choiceMissing[] = $depItemName;
                        // the item has no required version,   so this is not a candidate to choose
                        continue;
                    }
                    if ($depItem->isInstalled()) {
                        $choiceDepInstalled[] = $depItem;
                    } elseif ($depItem->canBeInstalled() || !$this->allowToForceInstallDependencies) {
                        $choiceDepToInstall[] = $depItem;
                    } else {
                        $choiceMissing[] = $depItemName;
                    }
                } elseif ($depItem->getAction() == self::ACTION_INSTALL) {
                    $version = $depItem->getCurrentVersion();
                    if (!VersionComparator::compareVersionRange($version, $depItemVersion)) {
                        $choiceMissing[] = $depItemName;
                        // the item has no required version,   so this is not a candidate to choose
                        continue;
                    }
                    $choiceWillBeInstalled[] = $depItem;
                } elseif ($depItem->getAction() == self::ACTION_UPGRADE) {
                    $version = $depItem->getNextVersion();
                    if (!VersionComparator::compareVersionRange($version, $depItemVersion)) {
                        // the item has no required version,   so this is not a candidate to choose
                        $choiceMissing[] = $depItemName;

                        continue;
                    }
                    $choiceWillBeInstalled[] = $depItem;
                }
            }

            if (count($choiceDepInstalled) || count($choiceWillBeInstalled)) {
                // one or more of item to choice are installed or will be installed,
                // nothing to do, we can continue...
                continue;
            }
            if (!count($choiceDepToInstall)) {
                throw new ItemException(
                    'Item '.$item->getName().' depends on alternative items but there are unknown or do not met installation criterias. Install or upgrade one of them before installing it.',
                    $item,
                    ItemException::ERROR_CHOICE_MISSING_ITEM,
                    $choiceMissing
                );
            }
            if (count($choiceDepToInstall) > 1) {
                $list = array_map(function ($it) { return $it->getName(); }, $choiceDepToInstall);

                throw new ItemException(
                    'Item '.$item->getName().' depends on alternative items but there are ambiguities to choose them. Installed one of them before installing it.',
                    $item,
                    ItemException::ERROR_CHOICE_AMBIGUOUS,
                    $list
                );
            }

            $depItem = $choiceDepToInstall[0];
            $depItem->setAction(self::ACTION_INSTALL);
            $this->_checkDependencies($depItem);
            $this->chain[] = $depItem;
        }

        $this->checkedItems[$item->getName()] = true;
        unset($this->circularDependencyTracker[$item->getName()]);

        if ($missingItems) {
            throw new ItemException(
                'For item '.$item->getName().', some items are missing: '.implode(',', $missingItems),
                $item,
                ItemException::ERROR_DEPENDENCY_MISSING_ITEM,
                $missingItems
            );
        }
    }

    /**
     * check reverse dependencies of an item to remove.
     *
     * Find all items having the given item as dependency, and remove them
     *
     * @param string $epId
     */
    protected function _checkReverseDependencies(Item $item)
    {
        if (isset($this->circularReverseDependencyTracker[$item->getName()])) {
            throw new ItemException(
                'Circular reverse dependency! Cannot process the item '.$item->getName(),
                $item,
                ItemException::ERROR_REVERSE_CIRCULAR_DEPENDENCY
            );
        }

        $this->circularReverseDependencyTracker[$item->getName()] = true;
        foreach ($this->items as $revdepItemName => $revdepItem) {
            $dependencies = $revdepItem->getDependencies();
            $hasDependency = isset($dependencies[$item->getName()]);

            $hasAltDependency = false;
            $altDependencies = $revdepItem->getAlternativeDependencies();
            if (count($altDependencies)) {
                foreach ($altDependencies as $choiceItems) {
                    if (isset($choiceItems[$item->getName()])) {
                        $hasAltDependency = true;

                        break;
                    }
                }
            }

            if (!$hasDependency && !$hasAltDependency) {
                continue;
            }

            if ($revdepItem->getAction() == self::ACTION_INSTALL || $revdepItem->getAction() == self::ACTION_UPGRADE) {
                throw new ItemException(
                    'Item '.$revdepItemName.' should be removed because of the removal of one of its dependencies, '.$item->getName().', but it asked to be install/upgrade at the same time',
                    $item,
                    ItemException::ERROR_ITEM_TO_INSTALL_SHOULD_BE_REMOVED,
                    $revdepItem
                );
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
