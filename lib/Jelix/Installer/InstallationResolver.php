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
        foreach ($componentsList as $component) {
            /**
             * @var ModuleInfos $infos
             * @var ModuleStatus $status
             */
            list($infos, $status) = $component;
            if (in_array($infos->name, $modulesList)) {
                $filter = $forceReconfigure ?
                    [
                        $status::FILTER_DISABLED_UNINSTALLED,
                        $status::FILTER_ENABLED_UNINSTALLED,
                        $status::FILTER_ENABLED_INSTALLED_UPGRADED,
                        $status::FILTER_ENABLED_INSTALLED_NOT_UPGRADED,
                    ]
                    : $status::FILTER_DISABLED_UNINSTALLED;

                $resolverItem = $status->getResolverItem($infos, $filter, true);
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

            /**
             * @var ModuleInfos $infos
             * @var ModuleStatus $status
             */
            list($infos, $status) = $component;

            if (in_array($infos->name, $modulesList)) {
                $filter = [
                    $status::FILTER_ENABLED_UNINSTALLED,
                    $status::FILTER_ENABLED_INSTALLED_UPGRADED,
                    $status::FILTER_ENABLED_INSTALLED_NOT_UPGRADED,
                ];

                $resolverItem = $status->getResolverItem($infos, $filter, true);
                if ($resolverItem->getAction() == Resolver::ACTION_INSTALL || $resolverItem->getAction() == Resolver::ACTION_UPGRADE) {
                    $resolverItem->setAction(Resolver::ACTION_REMOVE);
                }
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

        $modulesToUnconfigure = array();

        foreach ($modulesChain as $resolverItem) {
            if ($resolverItem->getAction() == Resolver::ACTION_REMOVE) {
                $modulesToUnconfigure[] = $resolverItem;
            }
        }
        return $modulesToUnconfigure;
    }

    /**
     * It select configurator of all modules already configured
     *
     * @param iterable $componentsList
     * @return Item[]
     * @throws ItemException
     */
    function getAllItemsToConfigureAtInstance($componentsList)
    {
        // get all modules and their dependencies
        $resolver = new Resolver();
        foreach ($componentsList as $component) {
            /**
             * @var ModuleInfos $infos
             * @var ModuleStatus $status
             */
            list($infos, $status) = $component;
            $resolverItem = $status->getResolverItem($infos,
                [$status::FILTER_ENABLED_UNINSTALLED,
                    $status::FILTER_ENABLED_INSTALLED_UPGRADED,
                    $status::FILTER_ENABLED_INSTALLED_NOT_UPGRADED,
                ], true
            );
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
        foreach ($componentsList as $component) {
            /**
             * @var ModuleInfos $infos
             * @var ModuleStatus $status
             */
            list($infos, $status) = $component;
            $resolverItem = $status->getResolverItem($infos,
                [$status::FILTER_ENABLED_UNINSTALLED,
                 $status::FILTER_ENABLED_INSTALLED_NOT_UPGRADED]);
            $resolver->addItem($resolverItem);
        }

        foreach ($ghostComponentsList as $component) {
            /**
             * @var ModuleInfos $infos
             * @var ModuleStatus $status
             */
            list($infos, $status) = $component;
            $resolverItem = $status->getResolverItem($infos,
                [$status::FILTER_DISABLED_INSTALLED]);
            $resolver->addItem($resolverItem);
        }

        // configure modules
        $modulesChain = $resolver->getDependenciesChainForInstallation(false);

        return $modulesChain;
    }



}