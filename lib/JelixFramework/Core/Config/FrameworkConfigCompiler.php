<?php

/**
 * @author       Laurent Jouanneau
 * @copyright    2006-2026 Laurent Jouanneau
 *
 * @see          https://www.jelix.org
 *
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Config;

use Jelix\Core\Infos\FrameworkInfos;
use Jelix\Core\Infos\ModuleInfos;
use Jelix\Core\Infos\ModuleStatusDeclaration;
use Jelix\Installer\ModuleStatus;

class FrameworkConfigCompiler
{
    /**
     * @var string[] all module paths, even those are not used by the application
     */
    protected $allModulePaths;

    /**
     * @var string[] all paths of directories containing plugins, including those which are into enabled modules
     */
    protected $enabledPluginsDirectoryPathsList;

    /**
     * @var string[] list of path to modules, declared into framework.ini or with jApp::declareModule()
     */
    protected $declaredModulePathsList;

    /**
     * @var string[] final list of path of all modules (enabled or not). keys are module names.
     */
    protected $allModulesPathList = array();

    /**
     * @var string[] final list of paths of modules that are enabled
     */
    protected $enabledModulesPathList = array();

    protected $tplPluginsDirectoryPathsListByType = array();
    protected $pluginPathsListByType = array();

    protected $allBasePath = array();

    public function __construct(
        protected \jAppInstance $app,
        protected FrameworkInfos $frameworkInfo,
        protected string $installerIniPath,
    ) {}

    public function compile($installationMode = false): FrameworkConfigForInstaller|FrameworkRuntimeConfig
    {
        if ($this->frameworkInfo->isInstallersDisabled()) {
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
        // allBasePath is used to check if the config cache should be rebuilt, by comparing the file mtime value to the current time
        $this->allBasePath = $this->app->getDeclaredModulesDir();

        $this->enabledPluginsDirectoryPathsList = array_map(function ($path) {
            return rtrim($path, '/\\').DIRECTORY_SEPARATOR;
        }, $this->app->getDeclaredPluginPaths());

        $bundled = realpath(__DIR__.'/../../../jelix/plugins/').DIRECTORY_SEPARATOR;
        if (file_exists($bundled) && !in_array($bundled, $this->enabledPluginsDirectoryPathsList)) {
            array_unshift($this->enabledPluginsDirectoryPathsList, $bundled);
        }

        $modules = array();
        $enabledModules = array();
        $this->loadAllModulePaths();
        $declaredModules = $this->frameworkInfo->getModules();
        foreach ($this->allModulePaths as $moduleName => $path) {

            if (!file_exists($path.'/module.xml')) {
                continue;
            }

            if ($installationMode) {
                $modules[$moduleName] = $this->_readModuleInfo($moduleName, $declaredModules[$moduleName] ?? null, true, $path, $installation);
                if ($modules[$moduleName]->isEnabled) {
                    $enabledModules[$moduleName] = ModuleInfos::load($path);
                }
            } else {
                $this->_readModuleInfo($moduleName, $declaredModules[$moduleName] ?? null, false, $path, $installation);
            }
        }
        $this->_loadPluginsPathList();

        if ($installationMode) {
            $fmkConfig = new FrameworkConfigForInstaller(
                $modules,
                $enabledModules,
                $this->allModulesPathList,
                $this->enabledModulesPathList,
                $this->enabledPluginsDirectoryPathsList,
                $this->tplPluginsDirectoryPathsListByType,
                $this->pluginPathsListByType,
                $this->allBasePath
            );

        } else {
            $fmkConfig = new FrameworkRuntimeConfig(
                $this->enabledModulesPathList,
                $this->enabledPluginsDirectoryPathsList,
                $this->tplPluginsDirectoryPathsListByType,
                $this->pluginPathsListByType,
                $this->allBasePath
            );
        }

        $this->enabledModulesPathList = array();
        $this->allModulesPathList = array();
        $this->tplPluginsDirectoryPathsListByType = array();
        $this->pluginPathsListByType = array();

        return $fmkConfig;
    }

    /**
     * @param mixed $installationMode
     * @param mixed $path
     * @param mixed $installation
     * @param mixed $moduleName
     *
     * @return null|ModuleStatus
     */
    protected function _readModuleInfo($moduleName, ?ModuleStatusDeclaration $moduleStatusDecl, $installationMode, $path, &$installation)
    {
        // Note: $moduleStatusDecl may be null if its sources are there, but it is not yet declared into framework.ini.
        // it could be declared later if it is a dependency of another declared module.

        $isInstalled = $installation['modules'][$moduleName.'.installed'] ?? 0;
        if ($this->frameworkInfo->isInstallersDisabled()) {
            $isInstalled = 1;
        }

        $moduleEnabled = false;
        if ($moduleName == 'jelix') {
            $moduleEnabled = true; // the jelix module should always be enabled
        } else {
            if ($this->frameworkInfo->isAllModulesEnabled()) {
                if ($this->frameworkInfo->isInstallersDisabled()
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

        if ($moduleEnabled) {
            $this->enabledModulesPathList[$moduleName] = $path;
            $p = $path.'plugins'.DIRECTORY_SEPARATOR;
            if (file_exists($p)
                && is_dir($p)
                && !in_array($p, $this->enabledPluginsDirectoryPathsList)) {
                $this->enabledPluginsDirectoryPathsList[] = $p;
            }
        }

        if (!$installationMode) {
            return null;
        }

        $module = array(
            'enabled' => $moduleEnabled,
        );

        $module['dbprofile'] = $installation['modules'][$moduleName.'.dbprofile'] ?? 'default';

        $module['version'] = (string) ($installation['modules'][$moduleName.'.version'] ?? '');
        $module['installed'] = $isInstalled;

        if ($moduleStatusDecl) {
            if ($moduleStatusDecl->parameters) {
                $module['installparam'] = $moduleStatusDecl->parameters;
            }
            if ($moduleStatusDecl->skipInstaller) {
                $module['skipinstaller'] = 'skip';
            }
        }

        $this->allModulesPathList[$moduleName] = $path;

        return new ModuleStatus(
            $moduleName,
            $path,
            $module,
            $moduleStatusDecl ? $moduleStatusDecl->isNative : false
        );
    }

    /**
     * Load all module paths, even those are not used by the application.
     */
    protected function loadAllModulePaths()
    {
        $this->allModulePaths = array();
        $this->allModulePaths['jelix'] = realpath(__DIR__.'/../../../jelix/core-modules/jelix/').DIRECTORY_SEPARATOR;

        // retrieve module paths declared with jApp::declareModule()
        $this->declaredModulePathsList = $this->app->getDeclaredModulePaths();

        // retrieve module paths declared directly into framework.ini
        foreach ($this->frameworkInfo->getModulesDeclaredPaths() as $module => $path) {
            $p = \jFile::parseJelixPath($path);
            if (!file_exists($p)) {
                throw new \Exception('Error in the configuration file -- The module path, '.$path.', given in the configuration, doesn\'t exist', 10);
            }
            if (!is_dir($p)) {
                throw new \Exception('Error in the configuration file -- The module path, '.$path.', given in the configuration, is not a directory', 10);
            }
            $this->declaredModulePathsList[] = rtrim($p, '/');
        }

        // Adds all of these modules into the result
        foreach ($this->declaredModulePathsList as $modulePath) {
            $this->allModulePaths[basename($modulePath)] = $modulePath.DIRECTORY_SEPARATOR;
        }

        // Adds all module paths declared with jApp::declareModulesDir()
        foreach ($this->app->getDeclaredModulesDirAndModules() as $basePath => $names) {
            $path = $basePath.DIRECTORY_SEPARATOR;
            if (is_array($names)) {
                foreach ($names as $name) {
                    $this->allModulePaths[$name] = $path.$name.DIRECTORY_SEPARATOR;
                }
            } elseif ($names == '*' || $names === null) {
                if ($handle = opendir($path)) {
                    while (($name = readdir($handle)) !== false) {
                        if ($name[0] != '.' && is_dir($path.$name)) {
                            $this->allModulePaths[$name] = $path.$name.DIRECTORY_SEPARATOR;
                        }
                    }
                    closedir($handle);
                }
            }
        }
    }

    /**
     * Analyse plugin paths.
     */
    protected function _loadPluginsPathList()
    {
        foreach ($this->enabledPluginsDirectoryPathsList as $k => $p) {
            if ($handle = opendir($p)) {
                while (($f = readdir($handle)) !== false) {
                    if ($f[0] != '.' && is_dir($p.$f)) {
                        if ($subdir = opendir($p.$f)) {
                            // @deprecated
                            if ($k != 0) {
                                $this->allBasePath[] = $p.$f.'/';
                            }
                            while (($subf = readdir($subdir)) !== false) {
                                if ($subf[0] != '.' && is_dir($p.$f.'/'.$subf)) {
                                    if ($f == 'tpl') {
                                        if (!isset($this->tplPluginsDirectoryPathsListByType[$subf])) {
                                            $this->tplPluginsDirectoryPathsListByType[$subf] = array();
                                        }
                                        array_unshift($this->tplPluginsDirectoryPathsListByType[$subf], $p.$f.'/'.$subf.'/');
                                    } else {
                                        $this->pluginPathsListByType[$f][$subf] = $p.$f.'/'.$subf.'/';
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
