<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2008-2017 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'installer/jIInstallReporter.iface.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerReporterTrait.trait.php');
require_once(JELIX_LIB_PATH.'installer/textInstallReporter.class.php');
require_once(JELIX_LIB_PATH.'installer/ghostInstallReporter.class.php');
require_once(JELIX_LIB_PATH.'installer/jIInstallerComponent.iface.php');
require_once(JELIX_LIB_PATH.'installer/jIInstallerComponent2.iface.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerException.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerGlobalSetup.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerModule.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerModule2.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerModuleInfos.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerComponentModule.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerEntryPoint.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerEntryPoint2.class.php');
require_once(JELIX_LIB_PATH.'core/jConfigCompiler.class.php');
require(JELIX_LIB_PATH.'installer/jInstallerMessageProvider.class.php');

use \Jelix\Dependencies\Item;
use \Jelix\Dependencies\Resolver;
use \Jelix\Dependencies\ItemException;

/**
 * main class for the installation
 *
 * It loads all entry points configurations and all informations about activated
 * modules. jInstaller then constructs a tree dependencies for these
 * activated modules, and launch their installation and the installation
 * of their dependencies.
 * An installation can be an initial installation, or just an upgrade
 * if the module is already installed.
 *
 * @internal The object which drives the installation of a module
 * is an object jInstallerComponentModule.
 * This object calls load a file from the directory of the module. this
 * file should contain a class which should inherits from jInstallerModule2.
 * this class should implements processes to install the module.
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

    /**
     * error code stored in a component: 
     */
    const INSTALL_ERROR_CONFLICT = 3;

    /**
     * the main entrypoint of the application
     * @var jInstallerEntryPoint2
     */
    protected $mainEntryPoint = null;

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
     * the global app setup
     * @var jInstallerGlobalSetup
     */
    protected $globalSetup;

    /**
     * initialize the installation
     *
     * jInstallerGlobalSetup reads configurations files of all entry points, and prepare object for
     * each module, needed to install/upgrade modules.
     *
     * @param jIInstallReporter $reporter  object which is responsible to process messages (display, storage or other..)
     * @param string $lang  the language code for messages
     */
    function __construct (jIInstallReporter $reporter, jInstallerGlobalSetup $globalSetup = null, $lang='') {
        $this->reporter = $reporter;
        $this->messages = new jInstallerMessageProvider($lang);

        if (!$globalSetup) {
            $globalSetup = new jInstallerGlobalSetup();
        }
        $this->globalSetup = $globalSetup;

        $this->mainEntryPoint = $globalSetup->getMainEntryPoint();
    }

    /**
     * set parameters for the installer of a module
     * @param string $moduleName the name of the module
     * @param array $parameters  parameters
     */
    public function setModuleParameters($moduleName, $parameters) {
        $module = $this->globalSetup->getModuleComponent($moduleName);
        if ($module) {
            $module->setInstallParameters($parameters);
        }
    }

    /**
     * install and upgrade if needed, all modules
     *
     * Only modules which have an access property > 0
     * are installed. Errors appeared during the installation are passed
     * to the reporter.
     *
     * @return boolean true if succeed, false if there are some errors
     */
    public function installApplication() {

        $this->startMessage();

        $resolver = new Resolver();
        foreach($this->globalSetup->getModuleComponentsList() as $name => $module) {
            $resolverItem = $module->getResolverItem();
            $resolver->addItem($resolverItem);
        }
        $modulesChains = $this->resolveDependencies($resolver);

        $result = $this->_installModules($modulesChains, true);
        $this->globalSetup->getInstallerIni()->save();
        $this->endMessage();
        return $result;
    }

    /**
     * install given modules even if they don't have an access property > 0
     * @param array $modulesList array of module names
     * @return boolean true if the installation is ok
     */
    public function installModules($modulesList)
    {
        return $this->_selectedModules(Resolver::ACTION_INSTALL, $modulesList);
    }

    /**
     * uninstall given modules
     * @param array $modulesList array of module names
     * @return boolean true if the uninstallation is ok
     * @throws Exception
     */
    public function uninstallModules($modulesList) {
        return $this->_selectedModules(Resolver::ACTION_REMOVE, $modulesList);
    }

    protected function _selectedModules($action, $modulesList) {
        $this->startMessage();

        // check that all given modules are existing
        $hasError = false;
        foreach ($modulesList as $name) {
            if (!$this->globalSetup->getModuleComponent($name)) {
                $this->error('module.unknown', $name);
                $hasError = true;
            }
        }
        if ($hasError) {
            return false;
        }
        // get all modules
        $resolver = new Resolver();
        foreach($this->globalSetup->getModuleComponentsList() as $name => $module) {
            $resolverItem = $module->getResolverItem();
            if (in_array($name, $modulesList)) {
                $resolverItem->setAction($action);
            }
            $resolver->addItem($resolverItem);
        }
        // install modules
        $modulesChains = $this->resolveDependencies($resolver);
        $result = $this->_installModules($modulesChains, false);
        $this->globalSetup->getInstallerIni()->save();
        $this->endMessage();
        return $result;
    }

    /**
     * core of the installation
     * @param Item[] $modulesChain
     * @return boolean true if the installation is ok
     */
    protected function _installModules($modulesChain) {

        $this->notice('install.start');
        jApp::setConfig($this->mainEntryPoint->getConfigObj());

        if ($this->mainEntryPoint->getConfigObj()->disableInstallers) {
            $this->notice('install.installers.disabled');
        }

        $componentsToInstall = $this->runPreInstall($modulesChain);
        if ($componentsToInstall === false) {
            $this->warning('install.bad.end');
            return false;
        }

        $installedModules = $this->runInstall($componentsToInstall);
        if ($installedModules === false) {
            $this->warning('install.bad.end');
            return false;
        }

        $result = $this->runPostInstall($installedModules);
        if (!$result) {
            $this->warning('install.bad.end');
        }
        else {
            $this->ok('install.end');
        }
        return $result;
    }

    protected function resolveDependencies(Resolver $resolver) {

        try {
            $moduleschain = $resolver->getDependenciesChainForInstallation();
        } catch(ItemException $e) {
            $item = $e->getItem();
            $component = $item->getProperty('component');

            switch($e->getCode()) {
                case ItemException::ERROR_CIRCULAR_DEPENDENCY:
                case ItemException::ERROR_REVERSE_CIRCULAR_DEPENDENCY:
                    $component->inError = self::INSTALL_ERROR_CIRCULAR_DEPENDENCY;
                    $this->error('module.circular.dependency',$component->getName());
                    break;
                case ItemException::ERROR_BAD_ITEM_VERSION:
                    $depName = $e->getRelatedData()->getName();
                    $maxVersion = $minVersion = 0;
                    foreach($component->getDependencies() as $compInfo) {
                        if ($compInfo['type'] == 'module' && $compInfo['name'] == $depName) {
                            $maxVersion = $compInfo['maxversion'];
                            $minVersion = $compInfo['minversion'];
                        }
                    }
                    $this->error('module.bad.dependency.version',array($component->getName(), $depName, $minVersion, $maxVersion));
                    break;
                case ItemException::ERROR_REMOVED_ITEM_IS_NEEDED:
                    $depName = $e->getRelatedData()->getName();
                    $this->error('install.error.delete.dependency',array($depName, $component->getName()));
                    break;
                case ItemException::ERROR_ITEM_TO_INSTALL_SHOULD_BE_REMOVED:
                    $depName = $e->getRelatedData()->getName();
                    $this->error('install.error.install.dependency',array($depName, $component->getName()));
                    break;
                case ItemException::ERROR_DEPENDENCY_MISSING_ITEM:
                    $component->inError = self::INSTALL_ERROR_MISSING_DEPENDENCIES;
                    $this->error('module.needed', array($component->getName(), implode(',',$e->getRelatedData())));
                    break;
                case ItemException::ERROR_INSTALLED_ITEM_IN_CONFLICT:
                    $component->inError = self::INSTALL_ERROR_CONFLICT;
                    $this->error('module.forbidden', array($component->getName(), implode(',',$e->getRelatedData())));
                    break;
                case ItemException::ERROR_ITEM_TO_INSTALL_IN_CONFLICT:
                    $component->inError = self::INSTALL_ERROR_CONFLICT;
                    $this->error('module.forbidden', array($component->getName(), implode(',',$e->getRelatedData())));
                    break;
                case ItemException::ERROR_CHOICE_MISSING_ITEM:
                    $component->inError = self::INSTALL_ERROR_MISSING_DEPENDENCIES;
                    $this->error('module.choice.unknown', array($component->getName(), implode(',',$e->getRelatedData())));
                    break;
                case ItemException::ERROR_CHOICE_AMBIGUOUS:
                    $component->inError = self::INSTALL_ERROR_MISSING_DEPENDENCIES;
                    $this->error('module.choice.ambiguous', array($component->getName(), implode(',',$e->getRelatedData())));
                    break;

            }

            $this->ok('install.bad.end');
            return false;
        } catch(\Exception $e) {
            $this->error('install.bad.dependencies');
            $this->ok('install.bad.end');
            return false;
        }

        $this->ok('install.dependencies.ok');
        return $moduleschain;
    }

    /**
     * @param \Jelix\Dependencies\Item[] $moduleschain
     * @return array|bool
     */
    protected function runPreInstall(&$moduleschain) {
        $result = true;
        // ----------- pre install
        // put also available installers into $componentsToInstall for
        // the next step
        $componentsToInstall = array();
        $installersDisabled = $this->mainEntryPoint->getConfigObj()->disableInstallers;
        foreach($moduleschain as $resolverItem) {
            /** @var jInstallerComponentModule $component */
            $component = $resolverItem->getProperty('component');

            try {
                if ($resolverItem->getAction() == Resolver::ACTION_INSTALL) {
                    if ($installersDisabled) {
                        $installer = null;
                    } else {
                        $installer = $component->getInstaller();
                    }
                    $componentsToInstall[] = array($installer, $component, Resolver::ACTION_INSTALL);
                    if ($installer) {
                        $component->setAsCurrentModuleInstaller($this->mainEntryPoint);
                        $installer->preInstall();
                    }
                }
                elseif ($resolverItem->getAction() == Resolver::ACTION_UPGRADE) {
                    if ($installersDisabled) {
                        $upgraders = array();
                    }
                    else {
                        $upgraders = $component->getUpgraders();
                    }

                    foreach($upgraders as $upgrader) {
                        $component->setAsCurrentModuleUpgrader($upgrader, $this->mainEntryPoint);
                        $upgrader->preInstall();
                    }
                    $componentsToInstall[] = array($upgraders, $component, Resolver::ACTION_UPGRADE);
                }
                else if ($resolverItem->getAction() == Resolver::ACTION_REMOVE) {
                    if ($installersDisabled) {
                        $installer = null;
                    } else {
                        $installer = $component->getInstaller();
                    }
                    $componentsToInstall[] = array($installer, $component, Resolver::ACTION_REMOVE);
                    if ($installer) {
                        $component->setAsCurrentModuleInstaller($this->mainEntryPoint);
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

    protected function runInstall($componentsToInstall) {

        $installedModules = array();
        $result = true;
        $installerIni = $this->globalSetup->getInstallerIni();
        // -----  installation process
        try {
            foreach($componentsToInstall as $item) {
                /** @var jInstallerComponentModule $component */
                /** @var jInstallerModule2 $installer */
                list($installer, $component, $action) = $item;
                $saveConfigIni = false;
                if ($action == Resolver::ACTION_INSTALL) {
                    if ($installer) {
                        $component->setAsCurrentModuleInstaller($this->mainEntryPoint);
                        $installer->install();
                        $saveConfigIni = true;
                    }

                    $installerIni->setValue($component->getName().'.installed',
                        1, 'modules');
                    $installerIni->setValue($component->getName().'.version',
                        $component->getSourceVersion(), 'modules');
                    $installerIni->setValue($component->getName().'.version.date',
                        $component->getSourceDate(), 'modules');
                    $installerIni->setValue($component->getName().'.firstversion',
                        $component->getSourceVersion(), 'modules');
                    $installerIni->setValue($component->getName().'.firstversion.date',
                        $component->getSourceDate(), 'modules');
                    $this->ok('install.module.installed', $component->getName());
                    $installedModules[] = array($installer, $component, $action);
                }
                elseif ($action == Resolver::ACTION_UPGRADE) {
                    $lastversion = '';
                    /** @var jInstallerModule2 $upgrader */
                    foreach($installer as $upgrader) {
                        $component->setAsCurrentModuleUpgrader($upgrader, $this->mainEntryPoint);
                        $upgrader->install();
                        $saveConfigIni = true;

                        // we set the version of the upgrade, so if an error occurs in
                        // the next upgrader, we won't have to re-run this current upgrader
                        // during a future update
                        $installerIni->setValue($component->getName().'.version',
                            $upgrader->getVersion(), 'modules');
                        $installerIni->setValue($component->getName().'.version.date',
                            $upgrader->getDate(), 'modules');
                        $this->ok('install.module.upgraded',
                            array($component->getName(), $upgrader->getVersion()));
                        $lastversion = $upgrader->getVersion();
                    }
                    // we set the version to the component version, because the version
                    // of the last upgrader could not correspond to the component version.
                    if ($lastversion != $component->getSourceVersion()) {
                        $installerIni->setValue($component->getName().'.version',
                            $component->getSourceVersion(), 'modules');
                        $installerIni->setValue($component->getName().'.version.date',
                            $component->getSourceDate(), 'modules');
                        $this->ok('install.module.upgraded',
                            array($component->getName(), $component->getSourceVersion()));
                    }
                    $installedModules[] = array($installer, $component, $action);

                }
                else if ($action == Resolver::ACTION_REMOVE) {
                    if ($installer) {
                        $component->setAsCurrentModuleInstaller($this->mainEntryPoint);
                        $installer->uninstall();
                        $saveConfigIni = true;
                    }
                    $installerIni->removeValue($component->getName().'.installed', 'modules');
                    $installerIni->removeValue($component->getName().'.version', 'modules');
                    $installerIni->removeValue($component->getName().'.version.date', 'modules');
                    $installerIni->removeValue($component->getName().'.firstversion', 'modules');
                    $installerIni->removeValue($component->getName().'.firstversion.date', 'modules');
                    $this->ok('install.module.uninstalled', $component->getName());
                    $installedModules[] = array($installer, $component, $action);
                }

                if ($saveConfigIni) {
                    // we save the configuration at each module because its
                    // installer may have modified it, and we want to save it
                    // in case the next module installer fails.
                    if ($this->globalSetup->getLiveConfigIni()->isModified()) {
                        //$ep->getLocalConfigIni()->save();
                        $this->globalSetup->getLiveConfigIni()->save();

                        // we re-load configuration file for each module because
                        // previous module installer could have modify it.
                        $this->mainEntryPoint->setConfigObj(
                            jConfigCompiler::read($this->mainEntryPoint->getConfigFile(), true,
                                $this->mainEntryPoint->isCliScript(),
                                $this->mainEntryPoint->getScriptName()));
                        jApp::setConfig($this->mainEntryPoint->getConfigObj());
                    }
                    $this->globalSetup->getUrlModifier()->save();
                }
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

    protected function runPostInstall($installedModules) {

        $result = true;
        // post install
        /** @var jInstallerComponentModule $component */
        /** @var jInstallerModule $installer */

        foreach($installedModules as $item) {
            try {
                list($installer, $component, $action) = $item;
                $saveConfigIni = false;
                if ($action == Resolver::ACTION_INSTALL) {
                    if ($installer) {
                        $component->setAsCurrentModuleInstaller($this->mainEntryPoint);
                        $installer->postInstall();
                        $component->installFinished();
                        $saveConfigIni = true;
                    }
                }
                else if ($action == Resolver::ACTION_UPGRADE) {
                    foreach ($installer as $upgrader) {
                        $component->setAsCurrentModuleUpgrader($upgrader, $this->mainEntryPoint);
                        $upgrader->postInstall();
                        $component->upgradeFinished($upgrader);
                        $saveConfigIni = true;
                    }
                }
                elseif ($action == Resolver::ACTION_REMOVE) {
                    if ($installer) {
                        $component->setAsCurrentModuleInstaller($this->mainEntryPoint);
                        $installer->postUninstall();
                        $component->uninstallFinished();
                        $saveConfigIni = true;
                    }
                }

                if ($saveConfigIni) {
                    // we save the configuration at each module because its
                    // installer may have modified it, and we want to save it
                    // in case the next module installer fails.
                    if ($this->globalSetup->getLiveConfigIni()->isModified()) {
                        $this->globalSetup->getLiveConfigIni()->save();

                        // we re-load configuration file for each module because
                        // previous module installer could have modify it.
                        $this->mainEntryPoint->setConfigObj(
                            jConfigCompiler::read($this->mainEntryPoint->getConfigFile(), true,
                                $this->mainEntryPoint->isCliScript(),
                                $this->mainEntryPoint->getScriptName()));
                        jApp::setConfig($this->mainEntryPoint->getConfigObj());
                    }
                    $this->globalSetup->getUrlModifier()->save();
                }
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

