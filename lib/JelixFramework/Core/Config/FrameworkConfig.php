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

class FrameworkConfig
{
    public function __construct(
        protected array $moduleStatuses,
        /** @var ModuleInfos[] $enabledModules */
        protected array $enabledModules,
        protected array $enabledModulePathsList,
        protected array $enabledPluginPathsList,
        protected array $tplpluginPathsListByType,
        protected array $pluginPathsListByType,
    ) {

    }

    public function getModuleStatus($moduleName) : ?array
    {
        return $this->modules[$moduleName] ?? null;
    }

    public function getModuleInfos($moduleName) : ?ModuleInfos
    {
        return $this->enabledModules[$moduleName] ?? null;
    }

    public function getEnabledModulePathsList() : array
    {
        return $this->enabledModulePathsList;
    }

    public function getEnabledPluginPathsList() : array
    {
        return $this->enabledPluginPathsList;
    }
}
