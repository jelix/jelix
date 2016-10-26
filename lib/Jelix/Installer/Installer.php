<?php
/**
* @author      Laurent Jouanneau
* @copyright   2008-2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer;
use Jelix\Core\App as App;
use \Jelix\Dependencies\Item;
use \Jelix\Dependencies\Resolver;
use \Jelix\Dependencies\ItemException;

/**
 * main class for the installation
 *
 * It load all entry points configurations. Each configurations has its own
 * activated modules. jInstaller then construct a tree dependencies for these
 * activated modules, and launch their installation and the installation
 * of their dependencies.
 * An installation can be an initial installation, or just an upgrade
 * if the module is already installed.
 * @internal The object which drives the installation of a component
 * (module...) is an object which inherits from AbstractInstallLauncher.
 * This object calls load a file from the directory of the component. this
 * file should contain a class which should inherits from ModuleInstallLauncher.
 * This class should implements processes to install
 * the component.
 */
class Installer {

    /** value for the installation status of a component: "uninstalled" status */
    const STATUS_UNINSTALLED = 0;

    /** value for the installation status of a component: "installed" status */
    const STATUS_INSTALLED = 1;

    /**
     * value for the access level of a component: "forbidden" level.
     * a module which have this level won't be installed
     */
    const ACCESS_FORBIDDEN = 0;

    /**
     * value for the access level of a component: "private" level.
     * a module which have this level won't be accessible directly
     * from the web, but only from other modules
     */
    const ACCESS_PRIVATE = 1;

    /**
     * value for the access level of a component: "public" level.
     * the module is accessible from the web
     */
    const ACCESS_PUBLIC = 2;

    const FLAG_INSTALL_MODULE = 1;

    const FLAG_UPGRADE_MODULE = 2;

    const FLAG_REMOVE_MODULE = 4;

    const FLAG_ALL = 7;

    /**
     *  @var \Jelix\IniFile\IniModifier it represents the installer.ini.php file.
     */
    public $installerIni = null;

    /**
     * list of entry point and their properties
     * @var EntryPoint[]  keys are entry point id.
     */
    protected $entryPoints = array();

    /**
     * list of entry point identifiant (provided by the configuration compiler).
     * identifiant of the entry point is the path+filename of the entry point
     * without the php extension
     * @var array   key=entry point name, value=url id
     */
    protected $epId = array();

    /**
     * list of all modules of the application
     * @var \Jelix\Installer\ModuleInstallLauncher[]   key=path of the module
     */
    protected $allModules = array();

    /**
     * the object responsible of the results output
     * @var jIInstallReporter
     */
    public $reporter;

    /**
     * @var JInstallerMessageProvider
     */
    public $messages;

    /**
     * the mainconfig.ini.php combined with the defaultconfig.ini.php
     * @var \Jelix\IniFile\MultiIniModifier
     */
    protected $mainConfig;

    /**
     * the localconfig.ini.php content combined with $mainConfig
     * @var \Jelix\IniFile\MultiIniModifier
     */
    protected $localConfig;

    /**
     * @var \Jelix\Routing\UrlMapping\XmlMapModifier
     */
    protected $xmlMapFile;

    /**
     * initialize the installation
     *
     * it reads configurations files of all entry points, and prepare object for
     * each module, needed to install/upgrade modules.
     * @param ReporterInterface $reporter  object which is responsible to process messages (display, storage or other..)
     * @param string $lang  the language code for messages
     */
    function __construct (ReporterInterface $reporter, $lang='') {
        $this->reporter = $reporter;
        $this->messages = new Checker\Messages($lang);

        $localConfig = App::configPath('localconfig.ini.php');
        if (!file_exists($localConfig)) {
           $localConfigDist = App::configPath('localconfig.ini.php.dist');
           if (file_exists($localConfigDist)) {
              copy($localConfigDist, $localConfig);
           }
           else {
              file_put_contents($localConfig, ';<'.'?php die(\'\');?'.'>');
           }
        }

        $this->mainConfig = new \Jelix\IniFile\MultiIniModifier(\Jelix\Core\Config::getDefaultConfigFile(),
                                                                App::mainConfigFile());
        $this->localConfig = new \Jelix\IniFile\MultiIniModifier($this->mainConfig,
                                                                 $localConfig);
        $this->installerIni = $this->getInstallerIni();

        $urlfile = App::appConfigPath($this->localConfig->getValue('significantFile', 'urlengine'));
        $this->xmlMapFile = new \Jelix\Routing\UrlMapping\XmlMapModifier($urlfile, true);

        $appInfos = new \Jelix\Core\Infos\AppInfos();
        $this->readEntryPointsData($appInfos);
        $this->installerIni->save();

        // be sure temp path is ready
        $chmod = $this->mainConfig->getValue('chmodDir');
        \jFile::createDir(App::tempPath(), intval($chmod, 8));
    }
    /**
     * @internal mainly for tests
     * @return \Jelix\IniFile\IniModifier the modifier for the installer.ini.php file
     */
    protected function getInstallerIni() {
        if (!file_exists(App::configPath('installer.ini.php'))) {
            if (false === @file_put_contents(App::configPath('installer.ini.php'), ";<?php die(''); ?>
; for security reasons , don't remove or modify the first line
; don't modify this file if you don't know what you do. it is generated automatically by jInstaller

")) {
                throw new \Exception('impossible to create var/config/installer.ini.php');
            }
        }
        else {
            copy(App::configPath('installer.ini.php'), App::configPath('installer.bak.ini.php'));
        }
        return new \Jelix\IniFile\IniModifier(App::configPath('installer.ini.php'));
    }

    /**
     * read the list of entrypoint from the project.xml file
     * and read all modules data used by each entry point
     * @param \Jelix\Core\Infos\AppInfos $appInfos
     */
    protected function readEntryPointsData(\Jelix\Core\Infos\AppInfos $appInfos) {

        $configFileList = array();
        $installerIni = $this->installerIni;
        $allModules = &$this->allModules;
        $that = $this;

        // read all entry points data
        foreach ($appInfos->entrypoints as $file => $entrypoint) {

            $configFile = $entrypoint['config'];
            if (isset($entrypoint['type'])) {
                $type = $entrypoint['type'];
            }
            else {
                $type = "classic";
            }

            // we create an object corresponding to the entry point
            $ep = $this->getEntryPointObject($configFile, $file, $type);

            $epId = $ep->getEpId();
            $ep->setUrlMap($this->xmlMapFile->addEntryPoint($epId, $type));
            $ep->setAppInfos($appInfos);

            $this->epId[$file] = $epId;
            $this->entryPoints[$epId] = $ep;

            // ignore entry point which have the same config file of an other one
            if (isset($configFileList[$configFile])) {
                $ep->sameConfigAs = $configFileList[$configFile];
                continue;
            }

            $configFileList[$configFile] = $epId;

            $ep->createInstallLaunchers(function ($moduleStatus, $moduleInfos)
                                        use($that, $epId){
                $name = $moduleInfos->name;
                $path = $moduleInfos->getPath();
                $that->installerIni->setValue($name.'.installed', $moduleStatus->isInstalled, $epId);
                $that->installerIni->setValue($name.'.version', $moduleStatus->version, $epId);
                if (!isset($that->allModules[$path])) {
                    $that->allModules[$path] = new ModuleInstallLauncher($moduleInfos, $that);
                }
                return $that->allModules[$path];
            });

            // remove informations about modules that don't exist anymore
            $modulesList = $ep->getModulesList();
            $modules = $this->installerIni->getValues($epId);
            foreach($modules as $key=>$value) {
                $l = explode('.', $key);
                if (count($l)<=1) {
                    continue;
                }
                if (!isset($modulesList[$l[0]])) {
                    $this->installerIni->removeValue($key, $epId);
                }
            }
        }
    }

    /**
     * @internal for tests
     * @return EntryPoint
     */
    protected function getEntryPointObject($configFile, $file, $type) {
        return new EntryPoint($this->mainConfig, $this->localConfig, $configFile, $file, $type);
    }

    /**
     * @param string $epId an entry point id
     * @return EntryPoint the corresponding entry point object
     */
    public function getEntryPoint($epId) {
        return $this->entryPoints[$epId];
    }

    /**
     * change the module version in readed informations, to simulate an update
     * when we call installApplication or an other method.
     * internal use !!
     * @param string $moduleName the name of the module
     * @param string $version the new version
     */
    public function forceModuleVersion($moduleName, $version) {
        foreach($this->entryPoints as $epId=>$ep) {
            $launcher = $ep->getLauncher($moduleName);
            if ($launcher) {
               $launcher->setInstalledVersion($epId, $version);
            }
        }
    }

    /**
     * set parameters for the installer of a module
     * @param string $moduleName the name of the module
     * @param array $parameters  parameters
     * @param string $entrypoint  the entry point name for which parameters will be applied when installing the module.
     *                     if null, parameters are valid for all entry points
     */
    public function setModuleParameters($moduleName, $parameters, $entrypoint = null) {
        if ($entrypoint !== null) {
            if (!isset($this->epId[$entrypoint])) {
                throw new \Exception("Unknown entrypoint name");
            }
            $epId = $this->epId[$entrypoint];
            if (!isset($this->entryPoints[$epId])) {
                throw new \Exception("Unknown entrypoint name");
            }

            $launcher = $this->entryPoints[$epId]->getLauncher($moduleName);
            if ($launcher) {
               $launcher->setInstallParameters($epId, $parameters);
            }
        }
        else {
            foreach($this->entryPoints as $epId=>$ep) {
               $launcher = $ep->getLauncher($moduleName);
               if ($launcher) {
                  $launcher->setInstallParameters($epId, $parameters);
               }
            }
        }
    }

    /**
     * install and upgrade if needed, all modules for each
     * entry point. Only modules which have an access property > 0
     * are installed. Errors appeared during the installation are passed
     * to the reporter.
     * @param int $flags flags indicating if we should install, and/or upgrade
     *                   modules or only modify config files. internal use.
     *                   see FLAG_* constants
     * @return boolean true if succeed, false if there are some errors
     */
    public function installApplication($flags = false) {

        if ($flags === false) {
            $flags = self::FLAG_ALL;
        }

        $this->startMessage();
        $result = true;

        foreach($this->entryPoints as $epId=>$ep) {
            if ($ep->sameConfigAs) {
               continue;
            }
            $resolver = new Resolver();
            foreach($ep->getLaunchers() as $name => $module) {
                $resolverItem = $module->getResolverItem($epId);
                $resolver->addItem($resolverItem);
            }
            $result = $result & $this->_installModules($resolver, $ep, true, $flags);
            if (!$result) {
                break;
            }
        }

        if ($result) {
            $this->completeInstallStatus();
        }
        $this->installerIni->save();
        $this->endMessage();
        return $result;
    }

    protected function completeInstallStatus() {
      foreach($this->entryPoints as $epId=>$ep) {
         if ($ep->sameConfigAs) {
            // let's copy the installation information of the other entry point
            $values = $this->installerIni->getValues($ep->sameConfigAs);
            $this->installerIni->setValues($values, $epId);
            $this->ok('install.entrypoint.installed', $epId);
         }
      }
    }
    
    /**
     * install and upgrade if needed, all modules for the given
     * entry point. Only modules which have an access property > 0
     * are installed. Errors appeared during the installation are passed
     * to the reporter.
     * @param string $entrypoint  the entrypoint name as it appears in project.xml
     * @return boolean true if succeed, false if there are some errors
     */
    public function installEntryPoint($entrypoint) {

        $this->startMessage();

        if (!isset($this->epId[$entrypoint])) {
            throw new \Exception("unknown entry point");
        }

        $epId = $this->epId[$entrypoint];
        $ep = $this->entrypoints[$epId];
        if ($ep->sameConfigAs) {
           // let's copy the installation information of the other entry point
           $values = $this->installerIni->getValues($ep->sameConfigAs);
           $this->installerIni->setValues($values, $epId);
           $this->ok('install.entrypoint.installed', $epId);
        }
        else {
            $resolver = new Resolver();
            foreach($ep->getLaunchers() as $name => $module) {
                $resolverItem = $module->getResolverItem($epId);
                $resolver->addItem($resolverItem);
            }
            $result = $this->_installModules($resolver, $ep, true);
        }

        $this->installerIni->save();
        $this->endMessage();
        return $result;
    }

    /**
     * install given modules even if they don't have an access property > 0
     * @param array $modulesList array of module names
     * @param string $entrypoint  the entrypoint name as it appears in project.xml
     *               or null if modules should be installed for all entry points
     * @return boolean true if the installation is ok
     */
    public function installModules($modulesList, $entrypoint = null)
    {
        return $this->_singleModules(Resolver::ACTION_INSTALL, $modulesList, $entrypoint);
    }

    /**
     * uninstall given modules
     * @param array $modulesList array of module names
     * @param string $entrypoint  the entrypoint name as it appears in project.xml
     *               or null if modules should be uninstalled for all entry points
     * @return boolean true if the uninstallation is ok
     */
    public function uninstallModules($modulesList, $entrypoint = null) {
        return $this->_singleModules(Resolver::ACTION_REMOVE, $modulesList, $entrypoint );
    }

    protected function _singleModules($action, $modulesList, $entrypoint ) {
        $this->startMessage();

        if ($entrypoint == null) {
            $entryPointList = $this->entryPoints;
        }
        else if (isset($this->epId[$entrypoint])) {
            $entryPointList = array($this->entryPoints[$this->epId[$entrypoint]]);
        }
        else {
            throw new \Exception("unknown entry point");
        }

        foreach ($entryPointList as $epId=>$ep) {
            if ($ep->sameConfigAs) {
                continue;
            }
            $allModules = &$ep->getLaunchers();
            $modules = array();
            // check that all given modules are existing
            $hasError = false;
            foreach ($modulesList as $name) {
                if (!isset($allModules[$name])) {
                    $this->error('module.unknown', $name);
                    $hasError = true;
                }
            }
            if ($hasError) {
                continue;
            }

            // get all modules
            $resolver = new Resolver();
            foreach($ep->getLaunchers() as $name => $module) {
                $resolverItem = $module->getResolverItem($epId);
                if (in_array($name, $modulesList)) {
                    $resolverItem->setAction($action);
                }
                $resolver->addItem($resolverItem);
            }
            // install modules
            $result = $result & $this->_installModules($resolver, $ep, false);
            if (!$result)
                break;
            $this->installerIni->save();
        }

        $this->completeInstallStatus();
        $this->installerIni->save();
        $this->endMessage();
        return $result;
    }

    /**
     * core of the installation
     * @param ModuleInstallLauncher[] $modules
     * @param EntryPoint $ep  the entrypoint
     * @param boolean $installWholeApp true if the installation is done during app installation
     * @param integer $flags to know what to do
     * @return boolean true if the installation is ok
     */
    protected function _installModules(Resolver $resolver, EntryPoint $ep, $installWholeApp, $flags=7) {
        $epId = $ep->getEpId();
        $this->notice('install.entrypoint.start', $epId);
        $ep = $this->entryPoints[$epId];
        App::setConfig($ep->getConfigObj());

        if ($ep->getConfigObj()->disableInstallers) {
            $this->notice('install.entrypoint.installers.disabled');
        }

        $moduleschain = $this->resolveDependencies($resolver, $epId);
        if ($moduleschain === false) {
            return false;
        }

        $componentsToInstall = $this->runPreInstall($moduleschain, $ep, $installWholeApp, $flags);
        if ($componentsToInstall === false) {
            $this->warning('install.entrypoint.bad.end', $epId);
            return false;
        }

        $installedModules = $this->runInstall($componentsToInstall, $ep, $epId, $flags);
        if ($installedModules === false) {
            $this->warning('install.entrypoint.bad.end', $epId);
            return false;
        }

        $result = $this->runPostInstall($installedModules, $ep, $flags);
        if (!$result) {
            $this->warning('install.entrypoint.bad.end', $epId);
        }
        else {
            $this->ok('install.entrypoint.end', $epId);
        }
        return $result;
    }

    protected function resolveDependencies(Resolver $resolver, $epId) {

        try {
            $moduleschain = $resolver->getDependenciesChainForInstallation();
        } catch(ItemException $e) {
            $item = $e->getItem();
            $component = $item->getProperty('component');

            if ($e->getCode() == 1 || $e->getCode() == 4) {
                $component->inError = self::INSTALL_ERROR_CIRCULAR_DEPENDENCY;
                $this->error('module.circular.dependency',$component->getName());
            }
            else if ($e->getCode() == 2) {
                $depName = $e->getRelatedData()->getName();
                $maxVersion = $minVersion = 0;
                foreach($component->dependencies as $compInfo) {
                    if ($compInfo['type'] == 'module' && $compInfo['name'] == $depName) {
                        $maxVersion = $depInfo['maxversion'];
                        $minVersion = $depInfo['minversion'];
                    }
                }
                $this->error('module.bad.dependency.version',array($component->getName(), $depName, $minVersion, $maxVersion));
            }
            else if ($e->getCode() == 3) {
                $depName = $e->getRelatedData()->getName();
                $this->error('install.error.delete.dependency',array($depName, $component->getName()));
            }
            else if ($e->getCode() == 6) {
                $component->inError = self::INSTALL_ERROR_MISSING_DEPENDENCIES;
                $this->error('module.needed', array($component->getName(), implode(',',$e->getRelatedData())));
            }
            else if ($e->getCode() == 5) {
                $depName = $e->getRelatedData()->getName();
                $this->error('install.error.install.dependency',array($depName, $component->getName()));
            }
            $this->ok('install.entrypoint.bad.end', $epId);
            return false;
        } catch(\Exception $e) {
            $this->error('install.bad.dependencies');
            $this->ok('install.entrypoint.bad.end', $epId);
            return false;
        }

        $this->ok('install.dependencies.ok');
        return $moduleschain;
    }


    protected function runPreInstall($moduleschain, $ep, $installWholeApp, $flags) {
        $result = true;
        // ----------- pre install
        // put also available installers into $componentsToInstall for
        // the next step
        $componentsToInstall = array();

        foreach($moduleschain as $resolverItem) {
            $component = $resolverItem->getProperty('component');

            try {
                if ($resolverItem->getAction() == Resolver::ACTION_INSTALL) {
                    if ($ep->getConfigObj()->disableInstallers) {
                        $installer = null;
                    } else {
                        $installer = $component->getInstaller($ep, $installWholeApp);
                    }
                    $componentsToInstall[] = array($installer, $component, Resolver::ACTION_INSTALL);
                    if ($flags & self::FLAG_INSTALL_MODULE && $installer) {
                        $installer->preInstall();
                    }
                }
                elseif ($resolverItem->getAction() == Resolver::ACTION_UPGRADE) {
                    if ($ep->getConfigObj()->disableInstallers) {
                        $upgraders = array();
                    }
                    else {
                        $upgraders = $component->getUpgraders($ep);
                    }

                    if ($flags & self::FLAG_UPGRADE_MODULE && count($upgraders)) {
                        foreach($upgraders as $upgrader) {
                            $upgrader->preInstall();
                        }
                    }
                    $componentsToInstall[] = array($upgraders, $component, Resolver::ACTION_UPGRADE);
                }
                else if ($resolverItem->getAction() == Resolver::ACTION_REMOVE) {
                    if ($ep->getConfigObj()->disableInstallers) {
                        $installer = null;
                    } else {
                        $installer = $component->getInstaller($ep, $installWholeApp);
                    }
                    $componentsToInstall[] = array($installer, $component, Resolver::ACTION_REMOVE);
                    if ($flags & self::FLAG_REMOVE_MODULE && $installer) {
                        $installer->preUninstall();
                    }
                }
            } catch (Exception $e) {
                $result = false;
                $this->error ($e->getLocaleKey(), $e->getLocaleParameters());
            } catch (\Exception $e) {
                $result = false;
                $this->error ('install.module.error', array($component->getName(), $e->getMessage()));
            }
        }
        if (!$result) {
            return false;
        }
        return $componentsToInstall;
    }

    protected function runInstall($componentsToInstall, $ep, $epId, $flags) {

        $installedModules = array();
        $result = true;
        // -----  installation process
        try {
            foreach($componentsToInstall as $item) {
                list($installer, $component, $action) = $item;
                if ($action == Resolver::ACTION_INSTALL) {
                    if ($installer && ($flags & self::FLAG_INSTALL_MODULE)) {
                        $installer->install();
                    }
                    $this->installerIni->setValue($component->getName().'.installed',
                        1, $epId);
                    $this->installerIni->setValue($component->getName().'.version',
                        $component->getSourceVersion(), $epId);
                    $this->installerIni->setValue($component->getName().'.version.date',
                        $component->getSourceDate(), $epId);
                    $this->installerIni->setValue($component->getName().'.firstversion',
                        $component->getSourceVersion(), $epId);
                    $this->installerIni->setValue($component->getName().'.firstversion.date',
                        $component->getSourceDate(), $epId);
                    $this->ok('install.module.installed', $component->getName());
                    $installedModules[] = array($installer, $component, $action);
                }
                elseif ($action == Resolver::ACTION_UPGRADE) {
                    $lastversion = '';
                    foreach($installer as $upgrader) {
                        if ($flags & self::FLAG_UPGRADE_MODULE) {
                            $upgrader->install();
                        }
                        // we set the version of the upgrade, so if an error occurs in
                        // the next upgrader, we won't have to re-run this current upgrader
                        // during a future update
                        $this->installerIni->setValue($component->getName().'.version',
                            $upgrader->version, $epId);
                        $this->installerIni->setValue($component->getName().'.version.date',
                            $upgrader->date, $epId);
                        $this->ok('install.module.upgraded',
                            array($component->getName(), $upgrader->version));
                        $lastversion = $upgrader->version;
                    }
                    // we set the version to the component version, because the version
                    // of the last upgrader could not correspond to the component version.
                    if ($lastversion != $component->getSourceVersion()) {
                        $this->installerIni->setValue($component->getName().'.version',
                            $component->getSourceVersion(), $epId);
                        $this->installerIni->setValue($component->getName().'.version.date',
                            $component->getSourceDate(), $epId);
                        $this->ok('install.module.upgraded',
                            array($component->getName(), $component->getSourceVersion()));
                    }
                    $installedModules[] = array($installer, $component, $action);
                }
                else if ($action == Resolver::ACTION_REMOVE) {
                    if ($installer && ($flags & self::FLAG_REMOVE_MODULE)) {
                        $installer->uninstall();
                    }
                    $this->installerIni->removeValue($component->getName().'.installed', $epId);
                    $this->installerIni->removeValue($component->getName().'.version', $epId);
                    $this->installerIni->removeValue($component->getName().'.version.date', $epId);
                    $this->installerIni->removeValue($component->getName().'.firstversion', $epId);
                    $this->installerIni->removeValue($component->getName().'.firstversion.date', $epId);
                    $this->ok('install.module.uninstalled', $component->getName());
                    $installedModules[] = array($installer, $component, $action);
                }
                // we always save the configuration, so it invalidates the cache
                $ep->getConfigIni()->save();
                $this->xmlMapFile->save();

                // we re-load configuration file for each module because
                // previous module installer could have modify it.
                $compiler =  new \Jelix\Core\Config\Compiler($ep->getConfigFile(),
                                                             $ep->scriptName,
                                                             $ep->isCliScript);
                $ep->setConfigObj($compiler->read(true));

                App::setConfig($ep->getConfigObj());
            }
        } catch (Exception $e) {
            $result = false;
            $this->error ($e->getLocaleKey(), $e->getLocaleParameters());
        } catch (\Exception $e) {
            $result = false;
            $this->error ('install.module.error', array($component->getName(), $e->getMessage()));
        }
        if (!$result) {
            return false;
        }
        return $installedModules;
    }

    protected function runPostInstall($installedModules, $ep, $flags) {
        $result = true;
        // post install
        foreach($installedModules as $item) {
            try {
                list($installer, $component, $action) = $item;

                if ($action == Resolver::ACTION_INSTALL) {
                    if ($installer && ($flags & self::FLAG_INSTALL_MODULE)) {
                        $installer->postInstall();
                        $component->installFinished($ep);
                    }
                }
                else if ($action == Resolver::ACTION_UPGRADE) {
                    if ($flags & self::FLAG_UPGRADE_MODULE) {
                        foreach ($installer as $upgrader) {
                            $upgrader->postInstall();
                            $component->upgradeFinished($ep, $upgrader);
                        }
                    }
                }
                elseif ($action == Resolver::ACTION_REMOVE) {
                    if ($installer && ($flags & self::FLAG_REMOVE_MODULE)) {
                        $installer->postUninstall();
                        $component->uninstallFinished($ep);
                    }
                }

                // we always save the configuration, so it invalidates the cache
                $ep->getConfigIni()->save();
                $this->xmlMapFile->save();

                // we re-load configuration file for each module because
                // previous module installer could have modify it.
                $compiler =  new \Jelix\Core\Config\Compiler($ep->getConfigFile(),
                                                             $ep->scriptName,
                                                             $ep->isCliScript);
                $ep->setConfigObj($compiler->read(true));
                App::setConfig($ep->getConfigObj());
            } catch (Exception $e) {
                $result = false;
                $this->error ($e->getLocaleKey(), $e->getLocaleParameters());
            } catch (\Exception $e) {
                $result = false;
                $this->error ('install.module.error', array($component->getName(), $e->getMessage()));
            }
        }
        return $result;
    }

    protected function startMessage () {
        $this->reporter->start();
    }

    protected function endMessage() {
        $this->reporter->end();
    }

    protected function error($msg, $params=null, $fullString=false){
        if (!$fullString) {
            $msg = $this->messages->get($msg,$params);
        }
        $this->reporter->message($msg, 'error');
    }

    protected function ok($msg, $params=null, $fullString=false){
        if (!$fullString) {
            $msg = $this->messages->get($msg,$params);
        }
        $this->reporter->message($msg, '');
    }

    protected function warning($msg, $params=null, $fullString=false){
        if (!$fullString) {
            $msg = $this->messages->get($msg,$params);
        }
        $this->reporter->message($msg, 'warning');
    }

    protected function notice($msg, $params=null, $fullString=false){
        if (!$fullString) {
            $msg = $this->messages->get($msg,$params);
        }
        $this->reporter->message($msg, 'notice');
    }
}

