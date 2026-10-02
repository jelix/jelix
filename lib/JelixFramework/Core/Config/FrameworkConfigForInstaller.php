<?php
/**
 * @author       Laurent Jouanneau
 *
 * @copyright    2026 Laurent Jouanneau
 *
 * @see          https://www.jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Config;

use Jelix\Core\Infos\ModuleInfos;

class FrameworkConfigForInstaller extends FrameworkRuntimeConfig
{
    public function __construct(

        protected array $moduleStatuses,
        /** @var ModuleInfos[] $enabledModules */
        protected array $enabledModules,
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

    public function getAllModulePathsList() : array
    {
        return $this->allModulePathsList;
    }

    public function getModuleStatus($moduleName) : ?array
    {
        return $this->modules[$moduleName] ?? null;
    }

    public function getModuleInfos($moduleName) : ?ModuleInfos
    {
        return $this->enabledModules[$moduleName] ?? null;
    }
}
