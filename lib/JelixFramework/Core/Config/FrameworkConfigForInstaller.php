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

class FrameworkConfigForInstaller extends FrameworkConfig
{
    public function __construct(
        $moduleStatuses,
        $enabledModules,
        $enabledModulePathsList,
        $enabledPluginPathsList,
        $tplpluginPathsListByType,
        $pluginPathsListByType,
        protected array $allModulePathsList,

    ) {
        parent::__construct(
            $moduleStatuses,
            $enabledModules,
            $enabledModulePathsList,
            $enabledPluginPathsList,
            $tplpluginPathsListByType,
            $pluginPathsListByType
        );
    }

    public function getAllModulePathsList() : array
    {
        return $this->allModulePathsList;
    }

}
