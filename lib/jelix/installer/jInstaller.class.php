<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2008-2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'installer/jIInstallReporter.iface.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerReporterTrait.trait.php');
require_once(JELIX_LIB_PATH.'installer/textInstallReporter.class.php');
require_once(JELIX_LIB_PATH.'installer/ghostInstallReporter.class.php');
require_once(JELIX_LIB_PATH.'installer/jIInstallerComponent.iface.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerException.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerBase.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerModule.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerModuleInfos.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerComponentBase.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerComponentModule.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerEntryPoint.class.php');
require_once(JELIX_LIB_PATH.'core/jConfigCompiler.class.php');
require(JELIX_LIB_PATH.'installer/jInstallerMessageProvider.class.php');

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
 * (module...) is an object which inherits from jInstallerComponentBase.
 * This object calls load a file from the directory of the component. this
 * file should contain a class which should inherits from jInstallerModule.
 *  this class should implements processes to install
 * the component.
 */
class jInstaller {

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

    /**
     * error code stored in a component: impossible to install
     * the module because dependencies are missing
     */
    const INSTALL_ERROR_MISSING_DEPENDENCIES = 1;

    /**
     * error code stored in a component: impossible to install
     * the module because of circular dependencies
     */
    const INSTALL_ERROR_CIRCULAR_DEPENDENCY = 2;

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
     * @var array of jInstallerEntryPoint. keys are entry point id.
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
     * list of modules for each entry point
     * @var array first key: entry point id, second key: module name, value = jInstallerComponentModule
     */
    protected $modules = array();
    
    /**
     * list of all modules of the application
     * @var array key=path of the module, value = jInstallerComponentModule
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
     * @param jIInstallReporter $reporter  object which is responsible to process messages (display, storage or other..)
     * @param string $lang  the language code for messages
     */
    function __construct (jIInstallReporter $reporter, $lang='') {
        $this->reporter = $reporter;
        $this->messages = new jInstallerMessageProvider($lang);

        $localConfig = jApp::configPath('localconfig.ini.php');
        if (!file_exists($localConfig)) {
           $localConfigDist = jApp::configPath('localconfig.ini.php.dist');
           if (file_exists($localConfigDist)) {
              copy($localConfigDist, $localConfig);
           }
           else {
              file_put_contents($localConfig, ';<'.'?php die(\'\');?'.'>');
           }
        }

        $this->mainConfig = new \Jelix\IniFile\MultiIniModifier(jConfig::getDefaultConfigFile(),
                                                                jApp::mainConfigFile());
        $this->localConfig = new \Jelix\IniFile\MultiIniModifier($this->mainConfig,
                                                                 $localConfig);
        $this->installerIni = $this->getInstallerIni();

        $urlfile = jApp::appConfigPath($this->localConfig->getValue('significantFile', 'urlengine'));
        $this->xmlMapFile = new \Jelix\Routing\UrlMapping\XmlMapModifier($urlfile, true);

        // be sure temp path is ready
        $chmod = $this->mainConfig->getValue('chmodDir');
        jFile::createDir(jApp::tempPath(), intval($chmod, 8));

        $this->readEntryPointData(simplexml_load_file(jApp::appPath('project.xml')));
        $this->installerIni->save();
    }
    /**
     * @internal mainly for tests
     * @return \Jelix\IniFile\IniModifier the modifier for the installer.ini.php file
     */
    protected function getInstallerIni() {
        if (!file_exists(jApp::configPath('installer.ini.php'))) {
            if (false === @file_put_contents(jApp::configPath('installer.ini.php'), ";<?php die(''); ?>
; for security reasons , don't remove or modify the first line
; don't modify this file if you don't know what you do. it is generated automatically by jInstaller

")) {
                throw new Exception('impossible to create var/config/installer.ini.php');
            }
        }
        else {
            copy(jApp::configPath('installer.ini.php'), jApp::configPath('installer.bak.ini.php'));
        }
        return new \Jelix\IniFile\IniModifier(jApp::configPath('installer.ini.php'));
    }

    /**
     * read the list of entrypoint from the project.xml file
     * and read all modules data used by each entry point
     * @param SimpleXmlElement $xml
     */
    protected function readEntryPointData($xml) {

        $configFileList = array();

        // read all entry points data
        foreach ($xml->entrypoints->entry as $entrypoint) {

            $file = (string)$entrypoint['file'];
            $configFile = (string)$entrypoint['config'];
            if (isset($entrypoint['type'])) {
                $type = (string)$entrypoint['type'];
            }
            else {
                $type = "classic";
            }

            // ignore entry point which have the same config file of an other one
            // FIXME: what about installer.ini ?
            if (isset($configFileList[$configFile]))
                continue;

            $configFileList[$configFile] = true;

            // we create an object corresponding to the entry point
            $ep = $this->getEntryPointObject($configFile, $file, $type);

            $epId = $ep->getEpId();
            $ep->setUrlMap($this->xmlMapFile->addEntryPoint($epId, $type));

            $this->epId[$file] = $epId;
            $this->entryPoints[$epId] = $ep;
            $this->modules[$epId] = array();

            // now let's read all modules properties
            $modulesList = $ep->getModulesList();
            foreach ($modulesList as $name=>$path) {
                $module = $ep->getModule($name);

                $this->installerIni->setValue($name.'.installed', $module->isInstalled, $epId);
                $this->installerIni->setValue($name.'.version', $module->version, $epId);

                if (!isset($this->allModules[$path])) {
                    $this->allModules[$path] = $this->getComponentModule($name, $path, $this);
                    $this->allModules[$path]->init();
                }

                $m = $this->allModules[$path];
                $m->addModuleInfos($epId, $module);
                $this->modules[$epId][$name] = $m;
            }
            // remove informations about modules that don't exist anymore
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
     */
    protected function getEntryPointObject($configFile, $file, $type) {
        return new jInstallerEntryPoint($this->mainConfig, $this->localConfig,
                                        $configFile, $file, $type);
    }

    /**
     * @internal for tests
     */
    protected function getComponentModule($name, $path, $installer) {
        return new jInstallerComponentModule($name, $path, $installer);
    }

    /**
     * @param string $epId an entry point id
     * @return jInstallerEntryPoint the corresponding entry point object
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
        foreach(array_keys($this->entryPoints) as $epId) {
            $modules = array();
            if (isset($this->modules[$epId][$moduleName])) {
                $this->modules[$epId][$moduleName]->setInstalledVersion($epId, $version);
            }
        }
    }

    /**
     * set parameters for the installer of a module
     * @param string $moduleName the name of the module
     * @param array $parameters  parameters
     * @param string $entrypoint  the entry point for which parameters will be applied when installing the module.
     *                     if null, parameters are valid for all entry points
     */
    public function setModuleParameters($moduleName, $parameters, $entrypoint = null) {
        if ($entrypoint !== null) {
            if (!isset($this->epId[$entrypoint]))
                return;
            $epId = $this->epId[$entrypoint];
            if (isset($this->entryPoints[$epId]) && isset($this->modules[$epId][$moduleName])) {
                $this->modules[$epId][$moduleName]->setInstallParameters($epId, $parameters);
            }
        }
        else {
            foreach(array_keys($this->entryPoints) as $epId) {
                $modules = array();
                if (isset($this->modules[$epId][$moduleName])) {
                    $this->modules[$epId][$moduleName]->setInstallParameters($epId, $parameters);
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

        foreach(array_keys($this->entryPoints) as $epId) {
            $resolver = new Resolver();
            foreach($this->modules[$epId] as $name => $module) {
                $resolverItem = $module->getResolverItem($epId);
                $resolver->addItem($resolverItem);
            }
            $result = $result & $this->_installModules($resolver, $epId, true, $flags);
            if (!$result) {
                break;
            }
        }

        $this->installerIni->save();
        $this->endMessage();
        return $result;
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
            throw new Exception("unknown entry point");
        }

        $epId = $this->epId[$entrypoint];
        $resolver = new Resolver();
        foreach($this->modules[$epId] as $name => $module) {
            $resolverItem = $module->getResolverItem($epId);
            $resolver->addItem($resolverItem);
        }

        $result = $this->_installModules($resolver, $epId, true);

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
    public function installModules($modulesList, $entrypoint = null) {

        $this->startMessage();

        if ($entrypoint == null) {
            $entryPointList = array_keys($this->entryPoints);
        }
        else if (isset($this->epId[$entrypoint])) {
            $entryPointList = array($this->epId[$entrypoint]);
        }
        else {
            throw new Exception("unknown entry point");
        }

        $result = true;

        foreach ($entryPointList as $epId) {
            // check that all given modules are existing
            $hasError = false;
            foreach ($modulesList as $name) {
                if (!isset($this->modules[$epId][$name])) {
                    $this->error('module.unknown', $name);
                    $hasError = true;
                }
            }
            if ($hasError) {
                continue;
            }
            // get all modules
            $resolver = new Resolver();
            foreach($this->modules[$epId] as $name => $module) {
                $resolverItem = $module->getResolverItem($epId);
                if (in_array($name, $modulesList)) {
                    $resolverItem->setAction(Resolve::ACTION_INSTALL);
                }
                $resolver->addItem($resolverItem);
            }
            // install modules
            $result = $result & $this->_installModules($resolver, $epId, true, $flags);
            if (!$result)
                break;
            $this->installerIni->save();
        }

        $this->endMessage();
        return $result;
    }

    /**
     * core of the installation
     * @param array $modules list of jInstallerComponentModule
     * @param string $epId  the entrypoint id
     * @param boolean $installWholeApp true if the installation is done during app installation
     * @param integer $flags to know what to do
     * @return boolean true if the installation is ok
     */
    protected function _installModules(Resolver $resolver, $epId, $installWholeApp, $flags=7) {

        $this->notice('install.entrypoint.start', $epId);
        $ep = $this->entryPoints[$epId];
        jApp::setConfig($ep->getConfigObj());

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
            } catch (jInstallerException $e) {
                $result = false;
                $this->error ($e->getLocaleKey(), $e->getLocaleParameters());
            } catch (Exception $e) {
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
                $ep->setConfigObj(
                    jConfigCompiler::read($ep->getConfigFile(), true,
                        $ep->isCliScript,
                        $ep->scriptName));
                jApp::setConfig($ep->getConfigObj());
            }
        } catch (jInstallerException $e) {
            $result = false;
            $this->error ($e->getLocaleKey(), $e->getLocaleParameters());
        } catch (Exception $e) {
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
                $ep->setConfigObj(
                    jConfigCompiler::read($ep->getConfigFile(), true,
                        $ep->isCliScript,
                        $ep->scriptName));
                jApp::setConfig($ep->getConfigObj());
            } catch (jInstallerException $e) {
                $result = false;
                $this->error ($e->getLocaleKey(), $e->getLocaleParameters());
            } catch (Exception $e) {
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

