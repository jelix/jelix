<?php
/**
 * @author       Laurent Jouanneau
 *
 * @copyright    2006-2026 Laurent Jouanneau
 *
 * @see          https://www.jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Config;

use Jelix\Core\Infos\FrameworkInfos;
use Jelix\Core\Infos\ModuleInfos;
use Jelix\Core\Infos\ModuleStatusDeclaration;

class FrameworkConfigCompiler
{
    /**
     * @var string[] all module paths, even those are not used by the application.
     */
    protected $allModulePaths = null;

    /**
     * @var string[] all paths of directories containing plugins, including those which are into enabled modules
     */
    protected $enabledPluginPathsList = null;

    /**
     * @var string[] list of path to modules, declared into framework.ini or with jApp::declareModule()
     */
    protected $declaredModulePathsList = null;

    /**
     * @var string[] final list of module paths (used and unused by the app). keys are module names.
     */
    protected $allModulesPathList = array();

    /**
     * @var string[] final list of paths of modules that are enabled
     */
    protected $enabledModulesPathList = array();


    protected $tplpluginPathsListByType = array();
    protected $pluginPathsListByType = array();

    protected $allBasePath = null;


    function __construct(
        protected \jAppInstance $app,
        protected FrameworkInfos $frameworkInfo,
        protected string $installerIniPath,
        protected bool $enableAllModules = false,
        protected bool $disabledInstallers = false,
        protected bool $checkCacheFiletime = true
    )
    {

    }


    public function compile($installationMode = false) : FrameworkConfig|FrameworkConfigForInstaller
    {
        if ($this->disabledInstallers) {
            $installation = array();
        } elseif (file_exists($this->installerIniPath)) {
            $installation = parse_ini_file($this->installerIniPath, true, INI_SCANNER_TYPED);
        } else {
            if ($installationMode) {
                $installation = array();
            } else {
                throw new Exception("The application is not installed -- installer.ini.php doesn't exist!\n", 9);
            }
        }

        if (!isset($installation['modules'])) {
            $installation['modules'] = array();
        }

        // @deprecated
        if ($this->checkCacheFiletime) {
            // _allBasePath is used to check if the config cache should be rebuilt, by comparing the file mtime value to the current time
            $this->allBasePath = $this->app->getDeclaredModulesDir();
        } else {
            $this->allBasePath = array();
        }

        $this->enabledPluginPathsList = array_map(function ($path) {
            return rtrim($path, '/\\').DIRECTORY_SEPARATOR;
        }, $this->app->getDeclaredPluginPaths());


        $bundled = realpath(__DIR__.'/../../../jelix/plugins/').DIRECTORY_SEPARATOR;
        if (file_exists($bundled) && !in_array($bundled, $this->enabledPluginPathsList)) {
            array_unshift($this->enabledPluginPathsList, $bundled);
        }

        $modules = array();
        $enabledModules = array();
        $this->loadAllModulePaths();
        $declaredModules = $this->frameworkInfo->getModules();
        foreach ($this->allModulePaths as $moduleName => $path) {
            $moduleInfo = \Jelix\Core\Infos\ModuleInfos::load($path);
            if (!$moduleInfo->exists()) {
                continue;
            }

            $mod = $modules[$moduleInfo->name] = $this->_readModuleInfo($moduleInfo, ($declaredModules[$moduleName] ?? null), $installationMode, $path, $installation);
            if ($mod['enabled']) {
                $enabledModules[$moduleName] = $moduleInfo;
            }
        }
        $this->_loadPluginsPathList();

        if ($installationMode) {
            $fmkConfig = new FrameworkConfigForInstaller(
                $modules,
                $enabledModules,
                $this->enabledModulesPathList,
                $this->enabledPluginPathsList,
                $this->tplpluginPathsListByType,
                $this->pluginPathsListByType,
                $this->allModulesPathList,
            );

        }
        else {
            $fmkConfig = new FrameworkConfig(
                $modules,
                $enabledModules,
                $this->enabledModulesPathList,
                $this->enabledPluginPathsList,
                $this->tplpluginPathsListByType,
                $this->pluginPathsListByType,
            );
        }

        $this->enabledModulesPathList = array();
        $this->allModulesPathList = array();
        $this->tplpluginPathsListByType = array();
        $this->pluginPathsListByType = array();
        return $fmkConfig;
    }

    /**
     *
     * @param mixed $installationMode
     * @param mixed $path
     * @param mixed $installation
     *
     */
    protected function _readModuleInfo(ModuleInfos $moduleInfos, ?ModuleStatusDeclaration $moduleStatusDecl, $installationMode, $path, &$installation)
    {
        $f = $moduleInfos->name;
        $isInstalled = $installation['modules'][$f . '.installed'] ?? 0;
        if ($this->disabledInstallers) {
            $isInstalled = 1;
        }

        $moduleEnabled = false;
        if ($f == 'jelix') {
            $moduleEnabled = true; // the jelix module should always be enabled
        } else {
            if ($this->enableAllModules) {
                if ($this->disabledInstallers
                    || $isInstalled
                    || $installationMode) {
                    $moduleEnabled = true;
                }
            } elseif ($moduleStatusDecl !== null) {
                $moduleEnabled = $moduleStatusDecl->isEnabled;
                if (!$isInstalled) {
                    // module is not installed.
                    // Outside an installation context, we force the disabling
                    // so the module is unusable until it is installed
                    if (!$installationMode) {
                        $moduleEnabled = false;
                    }
                }
            }
        }

        $module = array(
            'enabled' => $moduleEnabled,
        );

        if ($moduleEnabled || $installationMode) {
            $module['dbprofile'] = $installation['modules'][$f . '.dbprofile'] ?? 'default';
        }

        if ($installationMode) {
            // pour instance ModuleStatus (dans jInstallerEntrypoint, dans createComponentModule, )
            $module['version'] = ((string)$installation['modules'][$f . '.version'] ?? '');
            // pour instance ModuleStatus (dans jInstallerEntrypoint, dans createComponentModule, )
            $module['installed'] = $isInstalled;

            if ($moduleStatusDecl) {
                if ($moduleStatusDecl->parameters) {
                    $module['installparam'] = $moduleStatusDecl->parameters;
                }
                if ($moduleStatusDecl->skipInstaller) {
                    $module['skipinstaller'] = 'skip';
                }
            }

            $this->allModulesPathList[$f] = $path;
        }

        if ($moduleEnabled) {
            $this->enabledModulesPathList[$f] = $path;
            $p = $path.'plugins'.DIRECTORY_SEPARATOR;
            if (file_exists($p)
                && is_dir($p)
                && !in_array($p, $this->enabledPluginPathsList)) {
                $this->enabledPluginPathsList[] = $p;
            }
        }

        return $module;
    }


    /**
     * Load all module paths, even those are not used by the application.
     */
    protected function loadAllModulePaths()
    {
        $this->allModulePaths = array();
        $this->allModulePaths['jelix'] = realpath(__DIR__ . '/../core-modules/jelix/') . DIRECTORY_SEPARATOR;

        // retrieve module paths declared with jApp::declareModule()
        $this->declaredModulePathsList = $this->app->getDeclaredModulePaths();

        // retrieve module paths declared directly into framework.ini
        foreach ($this->frameworkInfo->getDeclaredModulePaths() as $module => $path) {
            $p = \jFile::parseJelixPath($path);
            if (!file_exists($p)) {
                throw new \Exception('Error in the configuration file -- The module path, ' . $path . ', given in the configuration, doesn\'t exist', 10);
            }
            if (!is_dir($p)) {
                throw new \Exception('Error in the configuration file -- The module path, ' . $path . ', given in the configuration, is not a directory', 10);
            }
            $this->declaredModulePathsList[] = rtrim($p, '/');
        }

        // Adds all of these modules into the result
        foreach ($this->declaredModulePathsList as $modulePath) {
            $this->allModulePaths[basename($modulePath)] = $modulePath . DIRECTORY_SEPARATOR;
        }

        // Adds all module paths declared with jApp::declareModulesDir()
        foreach ($this->app->getDeclaredModulesDirAndModules() as $basePath => $names) {
            $path = $basePath . DIRECTORY_SEPARATOR;
            if (is_array($names)) {
                foreach ($names as $name) {
                    $this->allModulePaths[$name] = $path . $name . DIRECTORY_SEPARATOR;
                }
            } elseif ($names == '*' || $names === null) {
                if ($handle = opendir($path)) {
                    while (($name = readdir($handle)) !== false) {
                        if ($name[0] != '.' && is_dir($path . $name)) {
                            $this->allModulePaths[$name] = $path . $name . DIRECTORY_SEPARATOR;
                        }
                    }
                    closedir($handle);
                }
            }
        }
    }

    /**
     * Analyse plugin paths.
     *
     */
    protected function _loadPluginsPathList()
    {
        foreach ($this->enabledPluginPathsList as $k => $p) {
            if ($handle = opendir($p)) {
                while (($f = readdir($handle)) !== false) {
                    if ($f[0] != '.' && is_dir($p . $f)) {
                        if ($subdir = opendir($p . $f)) {
                            // @deprecated
                            if ($k != 0 && $this->checkCacheFiletime) {
                                $this->allBasePath[] = $p . $f . '/';
                            }
                            while (($subf = readdir($subdir)) !== false) {
                                if ($subf[0] != '.' && is_dir($p . $f . '/' . $subf)) {
                                    if ($f == 'tpl') {
                                        $prop = '_tplpluginsPathList_' . $subf;
                                        if (!isset($this->tplpluginPathsListByType[$prop])) {
                                            $this->tplpluginPathsListByType[$prop] = array();
                                        }
                                        array_unshift($this->tplpluginPathsListByType[$prop], $p . $f . '/' . $subf . '/');
                                    } else {
                                        $prop = '_pluginsPathList_' . $f;
                                        $this->pluginPathsListByType[$prop][$subf] = $p . $f . '/' . $subf . '/';
                                    }
                                }
                            }
                            closedir($subdir);
                        }
                    }
                }
                closedir($handle);
            }
        }
    }
}
