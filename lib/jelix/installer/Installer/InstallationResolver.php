<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2008-2023 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer;


use Jelix\Core\Infos\ModuleInfos;
use Jelix\Dependencies\Item;
use Jelix\Dependencies\ItemException;
use Jelix\Dependencies\Resolver;

class InstallationResolver
{

    /**
     * @param iterable $componentsList
     * @return Item[]
     * @throws ItemException
     */
    function getItemsToConfigure($componentsList, $modulesList, $forceReconfigure)
    {
        $resolver = new Resolver();
        foreach ($componentsList as $name => $component) {
            /**
             * @var ModuleInfos $infos
             * @var ModuleStatus $status
             */
            list($infos, $status) = $component;
            if (in_array($name, $modulesList)) {
                $resolverItem = $status->getResolverItem($infos, !$status->isEnabled || $forceReconfigure, true);
            }
            else {
                $resolverItem = $status->getResolverItem($infos, false, true);
            }

            $resolver->addItem($resolverItem);
        }

        // configure modules
        if (count($modulesList)) {
            $modulesChain = $resolver->getDependenciesChainForSpecificItems($modulesList, true);
        }
        else {
            $modulesChain = $resolver->getDependenciesChainForInstallation(true);
        }

        $modulesToConfigure = array();

        foreach ($modulesChain as $resolverItem) {
            if ($resolverItem->getAction() == Resolver::ACTION_INSTALL || $resolverItem->getAction() == Resolver::ACTION_UPGRADE) {
                $modulesToConfigure[] = $resolverItem;
            }
        }
        return $modulesToConfigure;
    }

    /**
     * @param iterable $componentsList
     * @return Item[]
     * @throws ItemException
     */
    function getItemsToUnConfigure($componentsList, $modulesList)
    {
        $resolver = new Resolver();
        foreach ($componentsList as $name => $component) {
            $resolverItem = $status->getResolverItem($infos, false, true);
            /**
             * @var ModuleInfos $infos
             * @var ModuleStatus $status
             */
            list($infos, $status) = $component;
            if (in_array($name, $modulesList)) {
                if ($status->isEnabled) {
                    $resolverItem->setAction(Resolver::ACTION_REMOVE);
                }
            }

            $resolver->addItem($resolverItem);
        }

        // configure modules
        if (count($modulesList)) {
            $modulesChain = $resolver->getDependenciesChainForSpecificItems($modulesList, true);
        }
        else {
            $modulesChain = $resolver->getDependenciesChainForInstallation(true);
        }

        $modulesToUnconfigure = array();

        foreach ($modulesChain as $resolverItem) {
            if ($resolverItem->getAction() == Resolver::ACTION_REMOVE) {
                $modulesToUnconfigure[] = $resolverItem;
            }
        }
        return $modulesToUnconfigure;
    }

    /**
     * It force
     *
     * @param iterable $componentsList
     * @return Item[]
     * @throws ItemException
     */
    function getAllItemsToConfigureAtInstance($componentsList, $forceToConfigure = true)
    {
        // get all modules and their dependencies
        $resolver = new Resolver();
        foreach ($componentsList as $name => $component) {
            /**
             * @var ModuleInfos $infos
             * @var ModuleStatus $status
             */
            list($infos, $status) = $component;
            $resolverItem = $status->getResolverItem($infos, $forceToConfigure, true);
            $resolver->addItem($resolverItem);
        }

        // configure modules
        $modulesChain = $resolver->getDependenciesChainForInstallation(true);

        $modulesToConfigure = array();

        foreach ($modulesChain as $resolverItem) {
            if ($resolverItem->getAction() == Resolver::ACTION_INSTALL || $resolverItem->getAction() == Resolver::ACTION_UPGRADE) {
                $modulesToConfigure[] = $resolverItem;
            }
        }
        return $modulesToConfigure;
    }

    /**
     * @param iterable $componentsList
     * @return Item[]
     * @throws ItemException
     */
    function getAllItemsToInstall($componentsList, $ghostComponentsList)
    {
        // get all modules and their dependencies
        $resolver = new Resolver();
        foreach ($componentsList as $name => $component) {
            /**
             * @var ModuleInfos $infos
             * @var ModuleStatus $status
             */
            list($infos, $status) = $component;
            $resolverItem = $status->getResolverItem($infos);
            $resolver->addItem($resolverItem);
        }

        foreach ($ghostComponentsList as $name => $component) {
            /**
             * @var ModuleInfos $infos
             * @var ModuleStatus $status
             */
            list($infos, $status) = $component;
            $resolverItem = $status->getResolverItem($infos);
            $resolver->addItem($resolverItem);
        }

        // configure modules
        $modulesChain = $resolver->getDependenciesChainForInstallation(false);

        return $modulesChain;
    }



}