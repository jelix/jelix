<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2017-2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer;

use Jelix\IniFile\IniModifier;
use Jelix\IniFile\IniModifierArray;
use Jelix\IniFile\IniModifierReadOnly;
use Jelix\IniFile\IniReader;
use Jelix\Core\App;

/**
 * @since 1.7.0
 */
class GlobalSetup
{
    /**
     * the ini file: jelix/core/defaultconfig.ini.php.
     *
     * @var \Jelix\IniFile\IniReader
     */
    protected $defaultConfigIni;

    /**
     * the mainconfig.ini.php of the application.
     *
     * @var \Jelix\IniFile\IniModifier
     */
    protected $mainConfigIni;

    /**
     * the localconfig.ini.php of the application.
     *
     * @var \Jelix\IniFile\IniModifier
     */
    protected $localConfigIni;

    /**
     * the liveconfig.ini.php of the application.
     *
     * @var \Jelix\IniFile\IniModifier
     */
    protected $liveConfigIni;

    /**
     * @var \Jelix\Routing\UrlMapping\XmlMapModifier
     */
    protected $urlMapModifier;

    /**
     * @var \Jelix\Routing\UrlMapping\XmlRedefinedMapModifier
     */
    protected $urlLocalMapModifier;

    /**
     *  @var \Jelix\IniFile\IniModifier it represents the profiles.ini.php file.
     */
    protected $profilesIni;

    /**
     *  @var \Jelix\IniFile\IniModifier it represents the installer.ini.php file.
     */
    protected $installerIni;

    /**
     *  @var \Jelix\IniFile\IniModifier it represents the install/uninstall/uninstaller.ini.php file.
     */
    protected $uninstallerIni;

    /**
     * list of entry point and their properties.
     *
     * @var EntryPoint[] keys are entry point id
     */
    protected $entryPoints = array();

    /**
     * @var EntryPoint
     */
    protected $mainEntryPoint;

    /**
     * list of modules.
     *
     * @var ModuleInstallerLauncher[] key: module name
     */
    protected $modules = array();

    /**
     * list of ghost modules.
     *
     * ghost module is a module for which we have only its uninstaller
     *
     * @var ModuleInstallerLauncher[] key: module name
     */
    protected $ghostModules = array();

    /**
     * @var \Jelix\Core\Infos\FrameworkInfos
     */
    protected $frameworkInfos;

    /**
     * @var bool true if the configuration can be modified
     */
    protected $readWriteConfigMode = true;

    /**
     * GlobalSetup constructor.
     *
     * @param null|\Jelix\Core\Infos\FrameworkInfos|string $frameworkFileName
     * @param null|string                                  $mainConfigFileName
     * @param null|string                                  $localConfigFileName
     * @param null|string                                  $urlXmlFileName
     * @param null|string                                  $urlLocalXmlFileName
     * @param null|mixed                                   $localFrameworkFileName
     */
    public function __construct(
        $frameworkFileName = null,
        $localFrameworkFileName = null,
        $mainConfigFileName = null,
        $localConfigFileName = null,
        $urlXmlFileName = null,
        $urlLocalXmlFileName = null
    ) {
        if ($frameworkFileName instanceof \Jelix\Core\Infos\FrameworkInfos) {
            $this->frameworkInfos = $frameworkFileName;
        } else {
            if (!$frameworkFileName) {
                $frameworkFileName = App::appSystemPath('framework.ini.php');
            }
            if (!$localFrameworkFileName) {
                $localFrameworkFileName = App::varConfigPath('localframework.ini.php');
            }
            $this->frameworkInfos = new \Jelix\Core\Infos\FrameworkInfos($frameworkFileName, $localFrameworkFileName);
        }

        $profileIniFileName = App::varConfigPath('profiles.ini.php');
        if (!file_exists($profileIniFileName)) {
            $profileIniDist = App::varConfigPath('profiles.ini.php.dist');
            if (file_exists($profileIniDist)) {
                copy($profileIniDist, $profileIniFileName);
            } else {
                file_put_contents($profileIniFileName, ';<'.'?php die(\'\');?'.'> ');
            }
        }

        $this->profilesIni = new \Jelix\IniFile\IniModifier($profileIniFileName);

        if (!$mainConfigFileName) {
            $mainConfigFileName = App::mainConfigFile();
        }

        if (!$localConfigFileName) {
            $localConfigFileName = App::varConfigPath('localconfig.ini.php');
            if (!file_exists($localConfigFileName)) {
                $localConfigDist = App::varConfigPath('localconfig.ini.php.dist');
                if (file_exists($localConfigDist)) {
                    copy($localConfigDist, $localConfigFileName);
                } else {
                    file_put_contents($localConfigFileName, ';<'.'?php die(\'\');?'.'> static local configuration');
                }
            }
        }

        $liveConfigFileName = App::varConfigPath('liveconfig.ini.php');
        if (!file_exists($liveConfigFileName)) {
            file_put_contents($liveConfigFileName, ';<'.'?php die(\'\');?'.'> live local configuration');
        }

        $this->defaultConfigIni = new IniReader(\Jelix\Core\Config::getDefaultConfigFile());

        $this->mainConfigIni = new IniModifier($mainConfigFileName);

        $this->localConfigIni = new IniModifier($localConfigFileName);

        $this->liveConfigIni = new IniModifier($liveConfigFileName);

        $this->installerIni = $this->loadInstallerIni();

        \jFile::createDir(App::appPath('install/uninstall'));
        $this->uninstallerIni = new IniModifier(
            App::appPath('install/uninstall/uninstaller.ini.php'),
            ";<?php die(''); ?>
; for security reasons , don't remove or modify the first line
; don't modify this file if you don't know what you do. it is generated automatically by jInstaller

"
        );

        if (!$urlXmlFileName) {
            $ini = $this->getSystemConfigIni();
            $ini['local'] = $this->localConfigIni;
            $urlXmlFileName = App::appSystemPath($ini->getValue('significantFile', 'urlengine'));
            $urlLocalXmlFileName = App::varConfigPath($ini->getValue('localSignificantFile', 'urlengine'));
        }
        $this->urlMapModifier = new \Jelix\Routing\UrlMapping\XmlMapModifier($urlXmlFileName, true);
        $this->urlLocalMapModifier = new \Jelix\Routing\UrlMapping\XmlRedefinedMapModifier(
            $this->urlMapModifier,
            $urlLocalXmlFileName
        );

        $this->readEntryPointData();
        $this->readModuleInfos();

        // be sure temp path is ready
        $chmod = $this->getSystemConfigIni()->getValue('chmodDir');
        \jFile::createDir(App::tempPath(), intval($chmod, 8));
    }

    /**
     * @param bool $rwMode indicate if the configuration can be modified or not
     */
    public function setReadWriteConfigMode($rwMode)
    {
        $this->readWriteConfigMode = $rwMode;
    }

    /**
     * @return bool true if the configuration can be modified
     */
    public function isReadWriteConfigMode()
    {
        return $this->readWriteConfigMode;
    }

    /**
     * read the list of entrypoint from the project.xml file
     * and read all modules data used by each entry point.
     *
     * @throws \Exception
     */
    protected function readEntryPointData()
    {
        $configFileList = array();
        $entryPoints = $this->frameworkInfos->getEntryPoints();
        if (!count($entryPoints)) {
            throw new \Exception('No entrypoint declaration into framework.ini.php');
        }

        $defaultEntryPoint = $this->frameworkInfos->getDefaultEntryPointInfo();

        // read all entry points data
        foreach ($entryPoints as $entrypoint) {

            // ignore entry point which have the same config file of an other one
            // FIXME: what about installer.ini ?
            if (isset($configFileList[$entrypoint->getConfigFile()])) {
                continue;
            }

            $configFileList[$entrypoint->getConfigFile()] = true;

            // we create an object corresponding to the entry point
            $ep = $this->createEntryPointObject(
                $entrypoint->getConfigFile(),
                $entrypoint->getFile(),
                $entrypoint->getType()
            );
            $epId = $ep->getEpId();

            if ($defaultEntryPoint->getId() == $entrypoint->getId()) {
                $this->mainEntryPoint = $ep;
            }

            $this->entryPoints[$epId] = $ep;
        }
    }

    /**
     * @internal for tests
     *
     * @param mixed $configFile
     * @param mixed $file
     * @param mixed $type
     */
    protected function createEntryPointObject($configFile, $file, $type)
    {
        return new EntryPoint($this, $configFile, $file, $type);
    }

    protected function readModuleInfos()
    {
        // now let's read all modules properties
        $modulesList = $this->mainEntryPoint->getModulesList();

        foreach ($modulesList as $name => $path) {
            $compModule = $this->createComponentModule($name, $path);
            $this->addModuleComponent($compModule);
        }

        // load ghost modules we have to uninstall
        $uninstallersDir = App::appPath('install/uninstall');
        if (file_exists($uninstallersDir)) {
            $dir = new \DirectoryIterator($uninstallersDir);
            $modulesInfos = $this->uninstallerIni->getValues('modules');

            foreach ($dir as $dirContent) {
                if ($dirContent->isDot() || !$dirContent->isDir()) {
                    continue;
                }

                $moduleName = $dirContent->getFilename();

                if (
                    isset($this->modules[$moduleName])
                    || !$this->installerIni->getValue($moduleName.'.installed', 'modules')
                    || !isset($modulesInfos[$moduleName.'.enabled'])
                ) {
                    continue;
                }

                $modulesInfos[$moduleName.'.installed'] = 1;
                $modulesInfos[$moduleName.'.version'] = (string) $this->installerIni->getValue($moduleName.'.version', 'modules');
                $modulesInfos[$moduleName.'.enabled'] = false;

                $moduleInfos = new ModuleStatus(
                    $moduleName,
                    $dirContent->getPathname(),
                    $modulesInfos
                );

                $this->ghostModules[$moduleName] = new ModuleInstallerLauncher($moduleInfos, $this);
                $this->ghostModules[$moduleName]->init();
            }
        }

        // remove informations about modules that don't exist anymore
        $modules = $this->installerIni->getValues('modules');
        foreach ($modules as $key => $value) {
            $l = explode('.', $key);
            if (count($l) <= 1) {
                continue;
            }
            if (!isset($modulesList[$l[0]]) && !isset($this->ghostModules[$l[0]])) {
                $this->installerIni->removeValue($key, 'modules');
            }
        }
    }

    public function addModuleComponent(ModuleInstallerLauncher $compModule)
    {
        $name = $compModule->getName();
        $this->modules[$name] = $compModule;
        $compModule->init();
        $this->installerIni->setValue($name.'.installed', $compModule->isInstalled(), 'modules');
        $this->installerIni->setValue($name.'.version', $compModule->getInstalledVersion(), 'modules');
    }

    /**
     * @internal for tests
     *
     * @param mixed $name
     * @param mixed $path
     *
     * @return ModuleInstallerLauncher
     */
    protected function createComponentModule($name, $path)
    {
        $moduleSetupList = $this->mainEntryPoint->getConfigObj()->modules;
        $moduleInfos = new ModuleStatus($name, $path, $moduleSetupList);

        return new ModuleInstallerLauncher($moduleInfos, $this);
    }

    /**
     * @param string $name
     *
     * @return null|ModuleInstallerLauncher
     */
    public function getModuleComponent($name)
    {
        if (isset($this->modules[$name])) {
            return $this->modules[$name];
        }

        return null;
    }

    /**
     * @return ModuleInstallerLauncher[]
     */
    public function getModuleComponentsList()
    {
        return $this->modules;
    }

    /**
     * List of modules that should be uninstall and we
     * have only their uninstaller into install/uninstall/.
     *
     * @return ModuleInstallerLauncher[]
     */
    public function getGhostModuleComponents()
    {
        return $this->ghostModules;
    }

    /**
     * @return EntryPoint
     */
    public function getMainEntryPoint()
    {
        return $this->mainEntryPoint;
    }

    /**
     * @return EntryPoint[]
     */
    public function getEntryPointsList()
    {
        return $this->entryPoints;
    }

    /**
     * @param mixed $epId
     *
     * @return EntryPoint
     */
    public function getEntryPointById($epId)
    {
        if (isset($this->entryPoints[$epId])) {
            return $this->entryPoints[$epId];
        }

        return null;
    }

    /**
     * @param mixed $type
     *
     * @return EntryPoint[]
     */
    public function getEntryPointsByType($type = 'classic')
    {
        $list = array();
        foreach ($this->entryPoints as $id => $ep) {
            if ($ep->getType() == $type) {
                $list[$id] = $ep;
            }
        }

        return $list;
    }

    /**
     * the combined global config files, defaultconfig.ini.php and mainconfig.ini.php.
     *
     * @param mixed $forceReadOnly
     *
     * @return \Jelix\IniFile\IniModifierArray
     */
    public function getSystemConfigIni($forceReadOnly = false)
    {
        return new IniModifierArray(array(
            'default' => $this->defaultConfigIni,
            'main' => $this->getMainConfigIni($forceReadOnly),
        ));
    }

    /**
     * All combined config files :  defaultconfig.ini.php, mainconfig.ini.php
     * localconfig.ini.php and liveconfig.ini.php.
     *
     * @param bool $forceReadOnly
     *
     * @return \Jelix\IniFile\IniModifierArray
     */
    public function getFullConfigIni($forceReadOnly = false)
    {
        $ini = $this->getSystemConfigIni($forceReadOnly);
        $ini['local'] = $this->getLocalConfigIni($forceReadOnly);
        $ini['live'] = $this->getLiveConfigIni($forceReadOnly);

        return $ini;
    }

    /**
     * the defaultconfig.ini.php file.
     *
     * @return \Jelix\IniFile\IniReader
     */
    public function getDefaultConfigIni()
    {
        return $this->defaultConfigIni;
    }

    /**
     * the mainconfig.ini.php file.
     *
     * @param mixed $forceReadOnly
     *
     * @return \Jelix\IniFile\IniModifierInterface|\Jelix\IniFile\IniReaderInterface
     */
    public function getMainConfigIni($forceReadOnly = false)
    {
        if ($forceReadOnly || !$this->readWriteConfigMode) {
            return new IniModifierReadOnly($this->mainConfigIni);
        }

        return $this->mainConfigIni;
    }

    /**
     * the localconfig.ini.php file.
     *
     * @param mixed $forceReadOnly
     *
     * @return \Jelix\IniFile\IniModifierInterface|\Jelix\IniFile\IniReaderInterface
     */
    public function getLocalConfigIni($forceReadOnly = false)
    {
        if ($forceReadOnly || !$this->readWriteConfigMode) {
            return new IniModifierReadOnly($this->localConfigIni);
        }

        return $this->localConfigIni;
    }

    /**
     * the liveconfig.ini.php file.
     *
     * @param mixed $forceReadOnly
     *
     * @return \Jelix\IniFile\IniModifierInterface|\Jelix\IniFile\IniReaderInterface
     */
    public function getLiveConfigIni($forceReadOnly = false)
    {
        if ($forceReadOnly || !$this->readWriteConfigMode) {
            return new IniModifierReadOnly($this->liveConfigIni);
        }

        return $this->liveConfigIni;
    }

    /**
     * the profiles.ini.php file.
     *
     * @param mixed $forceReadOnly
     *
     * @return \Jelix\IniFile\IniModifierInterface|\Jelix\IniFile\IniReaderInterface
     */
    public function getProfilesIni($forceReadOnly = false)
    {
        if ($forceReadOnly || !$this->readWriteConfigMode) {
            return new IniModifierReadOnly($this->profilesIni);
        }

        return $this->profilesIni;
    }

    /**
     * the installer.ini.php.
     *
     * @return \Jelix\IniFile\IniModifier
     */
    public function getInstallerIni()
    {
        return $this->installerIni;
    }

    /**
     * the uninstaller.ini.php.
     *
     * @return \Jelix\IniFile\IniModifier
     */
    public function getUninstallerIni()
    {
        return $this->uninstallerIni;
    }

    /**
     * @throws \Exception
     *
     * @return \Jelix\IniFile\IniModifier the modifier for the installer.ini.php file
     */
    protected function loadInstallerIni()
    {
        if (!file_exists(App::varConfigPath('installer.ini.php'))) {
            if (@file_put_contents(App::varConfigPath('installer.ini.php'), ";<?php die(''); ?>
; for security reasons , don't remove or modify the first line
; don't modify this file if you don't know what you do. it is generated automatically by jInstaller

") === false) {
                throw new \Exception('impossible to create var/config/installer.ini.php');
            }
        } else {
            copy(App::varConfigPath('installer.ini.php'), App::varConfigPath('installer.bak.ini.php'));
        }

        return new \Jelix\IniFile\IniModifier(App::varConfigPath('installer.ini.php'));
    }

    /**
     * @return \Jelix\Routing\UrlMapping\XmlMapModifier
     */
    public function getUrlModifier()
    {
        return $this->urlMapModifier;
    }

    /**
     * @return \Jelix\Routing\UrlMapping\XmlMapModifier
     */
    public function getLocalUrlModifier()
    {
        return $this->urlLocalMapModifier;
    }

    /**
     * Declare a new entry point.
     *
     * @param string $epId
     * @param string $epType
     * @param string $configFileName
     *
     * @throws \Exception
     */
    public function declareNewEntryPoint($epId, $epType, $configFileName)
    {
        if (strpos($epId, '.php') !== false) {
            $epId = substr($epId, 0, -4);
        }

        //if ($this->frameworkInfos->getEntryPointInfo($epId)) {
        //    throw new \Exception("There is already an entrypoint with the same name ({$epId}, {$epType})");
        //}

        if ($this->forLocalConfiguration()) {
            $this->frameworkInfos->addLocalEntryPointInfo($epId, $configFileName, $epType);
        } else {
            $this->frameworkInfos->addEntryPointInfo($epId, $configFileName, $epType);
        }
        $this->frameworkInfos->save();

        $ep = $this->createEntryPointObject($configFileName, $epId.'.php', $epType);
        $this->entryPoints[$epId] = $ep;

        if ($this->forLocalConfiguration()) {
            $this->urlLocalMapModifier->addEntryPoint($epId, $epType);
        } else {
            $this->urlMapModifier->addEntryPoint($epId, $epType);
        }
    }

    /**
     * Undeclare an entry point.
     *
     * @param string $epId
     * @param string $epType
     * @param string $configFileName
     *
     * @throws \Exception
     */
    public function undeclareEntryPoint($epId)
    {
        if (strpos($epId, '.php') !== false) {
            $epId = substr($epId, 0, -4);
        }

        $this->frameworkInfos->removeEntryPointInfo($epId);
        $this->frameworkInfos->save();

        $this->urlLocalMapModifier->removeEntryPoint($epId);
        $this->urlMapModifier->removeEntryPoint($epId);
    }

    protected $installerContexts = array();

    public function getInstallerContexts($moduleName)
    {
        $contexts = $this->installerIni->getValue($moduleName.'.contexts', '__modules_data');
        if ($contexts !== null && $contexts !== '') {
            $contexts = explode(',', $contexts);
        } else {
            $contexts = array();
        }

        return $contexts;
    }

    public function updateInstallerContexts($moduleName, $contexts)
    {
        $this->installerIni->setValue($moduleName.'.contexts', implode(',', $contexts), '__modules_data');
    }

    public function removeInstallerContexts($moduleName)
    {
        $this->installerIni->removeValue($moduleName.'.contexts', '__modules_data');
    }

    /**
     * @param \Jelix\IniFile\IniModifier $config
     * @param string                     $name       the name of webassets
     * @param string                     $collection the name of the webassets collection
     * @param bool                       $force
     */
    public function declareWebAssetsInConfig(
        \Jelix\IniFile\IniModifierInterface $config,
        $name,
        array $values,
        $collection,
        $force
    ) {
        $section = 'webassets_'.$collection;
        if (!$force && (
            $config->getValue($name.'.css', $section)
                || $config->getValue($name.'.js', $section)
                || $config->getValue($name.'.require', $section)
        )) {
            return;
        }

        if (isset($values['css'])) {
            $config->setValue($name.'.css', $values['css'], $section);
        } else {
            $config->removeValue($name.'.css', $section);
        }
        if (isset($values['js'])) {
            $config->setValue($name.'.js', $values['js'], $section);
        } else {
            $config->removeValue($name.'.js', $section);
        }
        if (isset($values['require'])) {
            $config->setValue($name.'.require', $values['require'], $section);
        } else {
            $config->removeValue($name.'.require', $section);
        }
    }

    /**
     * @param \Jelix\IniFile\IniModifier $config
     * @param string                     $name       the name of webassets
     * @param string                     $collection the name of the webassets collection
     */
    public function removeWebAssetsFromConfig(
        \Jelix\IniFile\IniModifierInterface $config,
        $name,
        $collection
    ) {
        $section = 'webassets_'.$collection;
        $config->removeValue($name.'.css', $section);
        $config->removeValue($name.'.js', $section);
        $config->removeValue($name.'.require', $section);
    }

    /**
     * return the section name of configuration of a plugin for the coordinator
     * or the IniModifier for the configuration file of the plugin if it exists.
     *
     * @param \Jelix\IniFile\IniModifierInterface $config     the global configuration content
     * @param string                              $pluginName
     *
     * @throws Exception when the configuration filename is not found
     *
     * @return null|array null if plugin is unknown, else array($iniModifier, $section)
     */
    public function getCoordPluginConf(
        \Jelix\IniFile\IniModifierInterface $config,
        $pluginName
    ) {
        $conf = $config->getValue($pluginName, 'coordplugins');
        if (!$conf) {
            return null;
        }
        if ($conf === 1 || $conf === true) {
            $pluginConf = $config->getValues($pluginName);
            if ($pluginConf) {
                return array($config, $pluginName);
            }

            // old section naming. deprecated
            $pluginConf = $config->getValues('coordplugin_'.$pluginName);
            if ($pluginConf) {
                return array($config, 'coordplugin_'.$pluginName);
            }

            return null;
        }
        // the configuration value is a filename
        $confpath = App::appSystemPath($conf);
        if (!file_exists($confpath)) {
            $confpath = App::varConfigPath($conf);
            if (!file_exists($confpath)) {
                return null;
            }
        }

        if ($this->readWriteConfigMode) {
            $ini = new \Jelix\IniFile\IniModifier($confpath);
        } else {
            $ini = new \Jelix\IniFile\IniReader($confpath);
        }

        return array($ini, 0);
    }

    /**
     * @var ModuleInstallerLauncher
     */
    protected $currentProcessedModule;

    public function setCurrentProcessedModule($name)
    {
        $this->currentProcessedModule = $this->modules[$name];
    }

    public function getCurrentModulePath()
    {
        return $this->currentProcessedModule->getPath();
    }

    private $forLocalConfiguration = false;

    public function setCurrentConfiguratorStatus($forLocalConfiguration)
    {
        $this->forLocalConfiguration = $forLocalConfiguration;
    }

    public function forLocalConfiguration()
    {
        return $this->forLocalConfiguration;
    }
}
