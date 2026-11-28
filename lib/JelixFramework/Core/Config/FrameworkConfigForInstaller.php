<?php

/**
 * @author       Laurent Jouanneau
 * @copyright    2026 Laurent Jouanneau
 *
 * @see          https://www.jelix.org
 *
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Config;

use Jelix\Core\Infos\ModuleInfos;
use Jelix\Installer\ModuleStatus;

class FrameworkConfigForInstaller extends FrameworkRuntimeConfig
{
    public function __construct(

        /**
         * @@var ModuleStatus[]
         */
        protected array $moduleStatuses,

        /**
         * information about enabled modules.
         *
         * @var ModuleInfos[]
         */
        protected array $enabledModules,
        /**
         * list of path of all modules (enabled or not). keys are module names.
         *
         * @var array<string,string> keys are module names, values are paths
         */
        protected array $allModulePathsList,
        $enabledModulePathsList,
        $enabledPluginPathsList,
        $tplpluginPathsListByType,
        $pluginPathsListByType,
        $allPathsToCheck
    ) {
        parent::__construct(
            $enabledModulePathsList,
            $enabledPluginPathsList,
            $tplpluginPathsListByType,
            $pluginPathsListByType,
            $allPathsToCheck
        );
    }

    /**
     * list of path of all modules (enabled or not). keys are module names.
     *
     * @return array<string,string> keys are module names, values are paths
     */
    public function getAllModulesPathsList(): array
    {
        return $this->allModulePathsList;
    }

    public function getModuleStatus($moduleName): ?ModuleStatus
    {
        return $this->moduleStatuses[$moduleName] ?? null;
    }

    public function getModuleInfos($moduleName): ?ModuleInfos
    {
        return $this->enabledModules[$moduleName] ?? null;
    }

    public function saveForRuntime($directory)
    {
        // we don't want to save ModuleStatus and ModuleInfos objects
        $fmkConfig = new FrameworkRuntimeConfig(
            $this->enabledModulePathsList,
            $this->enabledPluginsDirectoryPathsList,
            $this->tplPluginsDirectoryPathsList,
            $this->pluginPathsListByType,
            $this->allPathsToCheck,
        );
        $fmkConfig->saveForRuntime($directory);
    }
}
