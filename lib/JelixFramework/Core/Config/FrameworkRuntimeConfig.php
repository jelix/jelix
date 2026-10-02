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

class FrameworkRuntimeConfig
{
    public function __construct(
        /**
         * @var array list of enabled modules paths. Keys are module names
         */
        protected array $enabledModulePathsList,

        /**
         * @var array list of enabled plugins directory paths.
         */
        protected array $enabledPluginsDirectoryPathsList,
        /**
         * @var array[] list of plugins directory path for the template engine
         *            key is the format type, value is the list of plugin directory paths
         */
        protected array $tplPluginsDirectoryPathsList,

        /**
         * @var array[] list of plugins.
         *              first key is the format type, second key is the plugin name
         *              value is the plugin directory path
         */
        protected array $pluginPathsListByType,

        /**
         * @var array list of paths to check for compilation cache
         */
        protected array $allPathsToCheck
    ) {

    }

    public function getModulePath($moduleName) : string
    {
        if (!isset($this->enabledModulePathsList[$moduleName])) {
            throw new Exception('getModulePath : invalid module name');
        }

        return $this->enabledModulePathsList[$moduleName];
    }

    public function isModuleEnabled($moduleName) : bool
    {
        return isset($this->enabledModulePathsList[$moduleName]);
    }

    /**
     * @return array list of enabled modules paths. Keys are module names
     */
    public function getEnabledModulePathsList() : array
    {
        return $this->enabledModulePathsList;
    }

    /**
     * @return array
     * @deprecated
     */
    public function getEnabledPluginsDirectoryPathsList() : array
    {
        return $this->enabledPluginsDirectoryPathsList;
    }

    public function getPathsToCheck() : array
    {
        return $this->allPathsToCheck;
    }

    public function getPluginPathByNameAndType(string $name, string $type) : string|false
    {
        if (!isset($this->pluginPathsListByType[$type][$name])) {
            return false;
        }

        return $this->pluginPathsListByType[$type][$name];
    }


    public function getTemplatePluginsDirectoryPathsByFormat($format) : array
    {
        return $this->tplPluginsDirectoryPathsList[$format] ?? [];
    }


    function saveForRuntime($directory)
    {
        $content = '<?php'."\n"
            . 'return '.var_export($this, true).";\n";

        file_put_contents($directory.'/framework.config.php', $content);
    }

    public static function __set_state($state)
    {
        return new FrameworkRuntimeConfig(
            $state['enabledModulePathsList'],
            $state['enabledPluginsDirectoryPathsList'],
            $state['tplpluginPathsListByType'],
            $state['pluginPathsListByType'],
            $state['allPathsToCheck'],
        );
    }

    public static function loadForRuntime($directory)
    {
        if ((!file_exists($directory.'/framework.config.php'))) {
            throw new Exception('Application not installer, framework.config.php not found');
        }
        return include $directory.'/framework.config.php';
    }
}
