<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2008-2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer;

// load legacy interfaces and classes
require_once JELIX_LIB_PATH.'installer/jIInstallReporter.iface.php';

require_once JELIX_LIB_PATH.'installer/jInstallerReporterTrait.trait.php';

require_once JELIX_LIB_PATH.'installer/textInstallReporter.class.php';

require_once JELIX_LIB_PATH.'installer/ghostInstallReporter.class.php';

require_once JELIX_LIB_PATH.'core/jConfigCompiler.class.php';

use Jelix\Dependencies\Item;
use Jelix\Dependencies\ItemException;
use Jelix\Dependencies\Resolver;

/**
 * main class for the installation.
 *
 * It loads all entry points configurations and all informations about activated
 * modules. jInstaller then constructs a tree dependencies for these
 * activated modules, and launch their installation and the installation
 * of their dependencies.
 * An installation can be an initial installation, or just an upgrade
 * if the module is already installed.
 *
 * @internal The object which drives the installation of a module
 * is an object \Jelix\Installer\ModuleInstallerLauncher.
 * This object calls load a file from the directory of the module. this
 * file should contain a class which should inherits from \Jelix\Installer\Module\Installer.
 * this class should implements processes to install the module.
 */
class Installer
{
    /** value for the installation status of a component: "uninstalled" status */
    const STATUS_UNINSTALLED = 0;

    /** value for the installation status of a component: "installed" status */
    const STATUS_INSTALLED = 1;

    /**
     * error code stored in a component: impossible to install
     * the module because dependencies are missing.
     */
    const INSTALL_ERROR_MISSING_DEPENDENCIES = 1;

    /**
     * error code stored in a component: impossible to install
     * the module because of circular dependencies.
     */
    const INSTALL_ERROR_CIRCULAR_DEPENDENCY = 2;

    /**
     * error code stored in a component:.
     */
    const INSTALL_ERROR_CONFLICT = 3;

    /**
     * the main entrypoint of the application.
     *
     * @var \Jelix\Installer\EntryPoint
     */
    protected $mainEntryPoint;

    /**
     * the object responsible of the results output.
     *
     * @var Reporter\ReporterInterface
     */
    protected $reporter;

    /**
     * @var \Jelix\Installer\Checker\Messages
     */
    protected $messages;

    /**
     * the global app setup.
     *
     * @var GlobalSetup
     */
    protected $globalSetup;

    /**
     * initialize the installation.
     *
     * GlobalSetup reads configurations files of all entry points, and prepare object for
     * each module, needed to install/upgrade modules.
     *
     * @param Reporter\ReporterInterface $reporter    object which is responsible to process messages (display, storage or other..)
     * @param GlobalSetup                $globalSetup
     * @param string                     $lang        the language code for messages
     */
    public function __construct(Reporter\ReporterInterface $reporter, GlobalSetup $globalSetup = null, $lang = '')
    {
        $this->reporter = $reporter;
        $this->messages = new \Jelix\Installer\Checker\Messages($lang);

        if (!$globalSetup) {
            $globalSetup = new GlobalSetup();
        }
        $this->globalSetup = $globalSetup;

        $this->mainEntryPoint = $globalSetup->getMainEntryPoint();
    }

    public static function setModuleAsInstalled($moduleName, $initialVersion, $versionDate)
    {
        $install = new \Jelix\IniFile\IniModifier(\jApp::varConfigPath('installer.ini.php'));
        $install->setValue($moduleName.'.installed', 1, 'modules');
        $install->setValue($moduleName.'.version', $initialVersion, 'modules');
        $install->setValue($moduleName.'.version.date', $versionDate, 'modules');
        $install->setValue($moduleName.'.firstversion', $initialVersion, 'modules');
        $install->setValue($moduleName.'.firstversion.date', $versionDate, 'modules');
        $install->save();
    }

    /**
     * install and upgrade if needed, all modules.
     *
     * Only modules which are enabled are installed.
     * Errors appearing during the installation are passed
     * to the reporter.
     *
     * @return bool true if succeed, false if there are some errors
     */
    public function installApplication()
    {
        $this->startMessage();

        $resolver = new Resolver();
        foreach ($this->globalSetup->getModuleComponentsList() as $name => $module) {
            $resolverItem = $module->getResolverItem();
            $resolver->addItem($resolverItem);
        }

        foreach ($this->globalSetup->getGhostModuleComponents() as $name => $module) {
            $resolverItem = $module->getResolverItem();
            $resolver->addItem($resolverItem);
        }

        $modulesChains = $this->resolveDependencies($resolver);
        if (is_array($modulesChains)) {
            $result = $this->_installModules($modulesChains);
            $this->globalSetup->getInstallerIni()->save();
        } else {
            $result = false;
        }
        $this->endMessage();

        return $result;
    }

    /**
     * core of the installation.
     *
     * @param Item[] $modulesChain
     *
     * @return bool true if the installation is ok
     */
    protected function _installModules($modulesChain)
    {
        $this->notice('install.start');
        \jApp::setConfig($this->mainEntryPoint->getConfigObj());

        if ($this->mainEntryPoint->getConfigObj()->disableInstallers) {
            $this->notice('install.installers.disabled');
        }

        $this->globalSetup->setReadWriteConfigMode(false);
        $componentsToInstall = $this->runPreInstall($modulesChain);
        if ($componentsToInstall === false) {
            $this->warning('install.bad.end');

            return false;
        }

        $this->globalSetup->setReadWriteConfigMode(true);
        $installedModules = $this->runInstall($componentsToInstall);
        if ($installedModules === false) {
            $this->warning('install.bad.end');

            return false;
        }

        $result = $this->runPostInstall($installedModules);
        if (!$result) {
            $this->warning('install.bad.end');
        } else {
            $this->ok('install.end');
        }

        return $result;
    }

    protected function resolveDependencies(Resolver $resolver)
    {
        try {
            $moduleschain = $resolver->getDependenciesChainForInstallation(false);
        } catch (ItemException $e) {
            $item = $e->getItem();
            $component = $item->getProperty('component');

            switch ($e->getCode()) {
                case ItemException::ERROR_CIRCULAR_DEPENDENCY:
                case ItemException::ERROR_REVERSE_CIRCULAR_DEPENDENCY:
                    $component->inError = self::INSTALL_ERROR_CIRCULAR_DEPENDENCY;
                    $this->error('module.circular.dependency', $component->getName());

                    break;

                case ItemException::ERROR_BAD_ITEM_VERSION:
                    $depName = $e->getRelatedData()->getName();
                    $maxVersion = $minVersion = 0;
                    foreach ($component->getDependencies() as $compInfo) {
                        if ($compInfo['type'] == 'module' && $compInfo['name'] == $depName) {
                            $maxVersion = $compInfo['maxversion'];
                            $minVersion = $compInfo['minversion'];
                        }
                    }
                    $this->error('module.bad.dependency.version', array($component->getName(), $depName, $minVersion, $maxVersion));

                    break;

                case ItemException::ERROR_REMOVED_ITEM_IS_NEEDED:
                    $depName = $e->getRelatedData()->getName();
                    $this->error('install.error.delete.dependency', array($depName, $component->getName()));

                    break;

                case ItemException::ERROR_ITEM_TO_INSTALL_SHOULD_BE_REMOVED:
                    $depName = $e->getRelatedData()->getName();
                    $this->error('install.error.install.dependency', array($depName, $component->getName()));

                    break;

                case ItemException::ERROR_DEPENDENCY_MISSING_ITEM:
                    $component->inError = self::INSTALL_ERROR_MISSING_DEPENDENCIES;
                    $this->error('module.needed', array($component->getName(), implode(',', $e->getRelatedData())));

                    break;

                case ItemException::ERROR_INSTALLED_ITEM_IN_CONFLICT:
                    $component->inError = self::INSTALL_ERROR_CONFLICT;
                    $this->error('module.forbidden', array($component->getName(), $e->getRelatedData()->getName()));

                    break;

                case ItemException::ERROR_ITEM_TO_INSTALL_IN_CONFLICT:
                    $component->inError = self::INSTALL_ERROR_CONFLICT;
                    $this->error('module.forbidden', array($component->getName(), $e->getRelatedData()->getName()));

                    break;

                case ItemException::ERROR_CHOICE_MISSING_ITEM:
                    $component->inError = self::INSTALL_ERROR_MISSING_DEPENDENCIES;
                    $this->error('module.choice.unknown', array($component->getName(), implode(',', $e->getRelatedData())));

                    break;

                case ItemException::ERROR_CHOICE_AMBIGUOUS:
                    $component->inError = self::INSTALL_ERROR_MISSING_DEPENDENCIES;
                    $this->error('module.choice.ambiguous', array($component->getName(), implode(',', $e->getRelatedData())));

                    break;

                case ItemException::ERROR_DEPENDENCY_CANNOT_BE_INSTALLED:
                    $component->inError = self::INSTALL_ERROR_MISSING_DEPENDENCIES;
                    $depName = $e->getRelatedData()->getName();
                    $this->error('module.dependency.error', array($depName, $component->getName()));

                    break;
            }

            $this->ok('install.bad.end');

            return false;
        } catch (\Exception $e) {
            $this->error('install.bad.dependencies');
            $this->ok('install.bad.end');

            return false;
        }

        $this->ok('install.dependencies.ok');

        return $moduleschain;
    }

    /**
     * launch preInstall()/preUninstall() methods of  installers or upgraders.
     *
     * @param \Jelix\Dependencies\Item[] $moduleschain
     *
     * @return array|bool
     */
    protected function runPreInstall(&$moduleschain)
    {
        $result = true;
        // put available installers into $componentsToInstall for
        // the next step
        $componentsToInstall = array();
        $installersDisabled = $this->mainEntryPoint->getConfigObj()->disableInstallers;

        $helpers = new Module\API\PreInstallHelpers($this->globalSetup);

        foreach ($moduleschain as $resolverItem) {
            /** @var \Jelix\Installer\ModuleInstallerLauncher $component */
            $component = $resolverItem->getProperty('component');

            try {
                $this->globalSetup->setCurrentProcessedModule($component->getName());
                if ($resolverItem->getAction() == Resolver::ACTION_INSTALL) {
                    if ($installersDisabled) {
                        $installer = null;
                    } else {
                        $installer = $component->getInstaller();
                    }
                    $componentsToInstall[] = array($installer, $component, Resolver::ACTION_INSTALL);
                    if ($installer) {
                        if ($installer instanceof \jInstallerModule) {
                            $installer->preInstall();
                        } else {
                            $installer->preInstall($helpers);
                        }
                    }
                } elseif ($resolverItem->getAction() == Resolver::ACTION_UPGRADE) {
                    if ($installersDisabled) {
                        $upgraders = array();
                    } else {
                        $upgraders = $component->getUpgraders();
                    }

                    foreach ($upgraders as $upgrader) {
                        if ($upgrader instanceof \jInstallerModule) {
                            $upgrader->preInstall();
                        } else {
                            $upgrader->preInstall($helpers);
                        }
                    }
                    $componentsToInstall[] = array($upgraders, $component, Resolver::ACTION_UPGRADE);
                } elseif ($resolverItem->getAction() == Resolver::ACTION_REMOVE) {
                    if ($installersDisabled) {
                        $installer = null;
                    } else {
                        $installer = $component->getUninstaller();
                    }
                    $componentsToInstall[] = array($installer, $component, Resolver::ACTION_REMOVE);
                    if ($installer) {
                        if ($installer instanceof \jInstallerModule) {
                            $installer->preUninstall();
                        } else {
                            $installer->preUninstall($helpers);
                        }
                    }
                }
            } catch (Exception $e) {
                $result = false;
                $this->error($e->getLocaleKey(), $e->getLocaleParameters());
            } catch (\Exception $e) {
                $result = false;
                $this->error('install.module.error', array($component->getName(), $e->getMessage()));
            }
        }
        if (!$result) {
            return false;
        }

        return $componentsToInstall;
    }

    /**
     * Launch the install()/uninstall() method of installers or upgraders.
     *
     * @param array $componentsToInstall
     *
     * @return array|bool
     */
    protected function runInstall($componentsToInstall)
    {
        $installedModules = array();
        $result = true;
        $installerIni = $this->globalSetup->getInstallerIni();

        $databaseHelpers = new Module\API\DatabaseHelpers($this->globalSetup);
        $helpers = new Module\API\InstallHelpers($this->globalSetup, $databaseHelpers);

        try {
            foreach ($componentsToInstall as $item) {
                /** @var \Jelix\Installer\ModuleInstallerLauncher $component */
                /** @var \Jelix\Installer\Module\Installer|\Jelix\Installer\Module\Uninstaller $installer */
                list($installer, $component, $action) = $item;
                $saveConfigIni = false;
                $this->globalSetup->setCurrentProcessedModule($component->getName());

                if ($action == Resolver::ACTION_INSTALL) {
                    if ($installer) {
                        if ($installer instanceof \jInstallerModule) {
                            $installer->install();
                        } else {
                            $databaseHelpers->useDbProfile($installer->getDefaultDbProfile() ?: $component->getDbProfile());
                            $installer->install($helpers);
                        }
                        $saveConfigIni = true;
                    }

                    $installerIni->setValue(
                        $component->getName().'.installed',
                        1,
                        'modules'
                    );
                    $installerIni->setValue(
                        $component->getName().'.version',
                        $component->getSourceVersion(),
                        'modules'
                    );
                    $installerIni->setValue(
                        $component->getName().'.version.date',
                        $component->getSourceDate(),
                        'modules'
                    );
                    $installerIni->setValue(
                        $component->getName().'.firstversion',
                        $component->getSourceVersion(),
                        'modules'
                    );
                    $installerIni->setValue(
                        $component->getName().'.firstversion.date',
                        $component->getSourceDate(),
                        'modules'
                    );
                    $this->ok('install.module.installed', $component->getName());
                    $installedModules[] = array($installer, $component, $action);
                } elseif ($action == Resolver::ACTION_UPGRADE) {
                    $lastversion = '';
                    /** @var \Jelix\Installer\Module\Installer|\jInstallerModule $upgrader */
                    foreach ($installer as $upgrader) {
                        if ($upgrader instanceof \jInstallerModule) {
                            $upgrader->install();
                        } else {
                            $databaseHelpers->useDbProfile($upgrader->getDefaultDbProfile() ?: $component->getDbProfile());
                            $upgrader->install($helpers);
                        }
                        $saveConfigIni = true;

                        // we set the version of the upgrade, so if an error occurs in
                        // the next upgrader, we won't have to re-run this current upgrader
                        // during a future update
                        $installerIni->setValue(
                            $component->getName().'.version',
                            $upgrader->getVersion(),
                            'modules'
                        );
                        $installerIni->setValue(
                            $component->getName().'.version.date',
                            $upgrader->getDate(),
                            'modules'
                        );
                        $this->ok(
                            'install.module.upgraded',
                            array($component->getName(), $upgrader->getVersion())
                        );
                        $lastversion = $upgrader->getVersion();
                    }
                    // we set the version to the component version, because the version
                    // of the last upgrader could not correspond to the component version.
                    if ($lastversion != $component->getSourceVersion()) {
                        $installerIni->setValue(
                            $component->getName().'.version',
                            $component->getSourceVersion(),
                            'modules'
                        );
                        $installerIni->setValue(
                            $component->getName().'.version.date',
                            $component->getSourceDate(),
                            'modules'
                        );
                        $this->ok(
                            'install.module.upgraded',
                            array($component->getName(), $component->getSourceVersion())
                        );
                    }
                    $installedModules[] = array($installer, $component, $action);
                } elseif ($action == Resolver::ACTION_REMOVE) {
                    if ($installer) {
                        if ($installer instanceof \jInstallerModule) {
                            $installer->uninstall();
                        } else {
                            $databaseHelpers->useDbProfile($installer->getDefaultDbProfile() ?: $component->getDbProfile());
                            $installer->uninstall($helpers);
                        }
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
                    $this->saveConfigurationFiles($this->mainEntryPoint);
                }
            }
        } catch (Exception $e) {
            $result = false;
            $this->error($e->getLocaleKey(), $e->getLocaleParameters());
        } catch (\Exception $e) {
            $result = false;
            $this->error('install.module.error', array($component->getName(), $e->getMessage()));
        }
        if (!$result) {
            return false;
        }

        return $installedModules;
    }

    /**
     * Launch the postInstall()/postUninstall() method of installers or upgraders.
     *
     * @param array $installedModules
     *
     * @return bool
     */
    protected function runPostInstall($installedModules)
    {
        $result = true;
        $databaseHelpers = new Module\API\DatabaseHelpers($this->globalSetup);
        $helpers = new Module\API\InstallHelpers($this->globalSetup, $databaseHelpers);

        foreach ($installedModules as $item) {
            try {
                /** @var \Jelix\Installer\ModuleInstallerLauncher $component */
                /** @var \Jelix\Installer\Module\Installer|\Jelix\Installer\Module\Uninstaller $installer */
                list($installer, $component, $action) = $item;
                $saveConfigIni = false;
                $this->globalSetup->setCurrentProcessedModule($component->getName());

                if ($action == Resolver::ACTION_INSTALL) {
                    if ($installer) {
                        if ($installer instanceof \jInstallerModule) {
                            $installer->postInstall();
                            $this->declareLegacyNewEntryPoint($installer);
                        } else {
                            $databaseHelpers->useDbProfile($installer->getDefaultDbProfile() ?: $component->getDbProfile());
                            $installer->postInstall($helpers);
                        }
                        $component->installFinished();
                        $saveConfigIni = true;
                    }
                } elseif ($action == Resolver::ACTION_UPGRADE) {
                    foreach ($installer as $upgrader) {
                        if ($upgrader instanceof \jInstallerModule) {
                            $upgrader->postInstall();
                            $this->declareLegacyNewEntryPoint($upgrader);
                        } else {
                            $databaseHelpers->useDbProfile($upgrader->getDefaultDbProfile() ?: $component->getDbProfile());
                            $upgrader->postInstall($helpers);
                        }
                        $component->upgradeFinished($upgrader);
                        $saveConfigIni = true;
                    }
                } elseif ($action == Resolver::ACTION_REMOVE) {
                    if ($installer) {
                        if ($installer instanceof \jInstallerModule) {
                            $installer->postUninstall();
                        } else {
                            $databaseHelpers->useDbProfile($installer->getDefaultDbProfile() ?: $component->getDbProfile());
                            $installer->postUninstall($helpers);
                        }
                        $component->uninstallFinished();
                        $saveConfigIni = true;
                    }
                }

                if ($saveConfigIni) {
                    $this->saveConfigurationFiles($this->mainEntryPoint);
                }
            } catch (Exception $e) {
                $result = false;
                $this->error($e->getLocaleKey(), $e->getLocaleParameters());
            } catch (\Exception $e) {
                $result = false;
                $this->error('install.module.error', array($component->getName(), $e->getMessage()));
            }
        }

        return $result;
    }

    protected function saveConfigurationFiles(EntryPoint $entryPoint)
    {

        // we save the configuration at each module because its
        // installer may have modified it, and we want to save it
        // in case the next module installer fails.
        $fullConfig = $this->globalSetup->getFullConfigIni();
        if ($fullConfig->isModified()) {
            $fullConfig->save();

            // we re-load configuration file for each module because
            // previous module installer could have modify it.
            $entryPoint->setConfigObj(
                \jConfigCompiler::read(
                    $entryPoint->getConfigFileName(),
                    true,
                    $entryPoint->isCliScript(),
                    $entryPoint->getScriptName()
                )
            );
            \jApp::setConfig($entryPoint->getConfigObj());
        }

        foreach($this->globalSetup->getEntryPointsList() as $ep) {
            $conf = $ep->getConfigIni();
            if ($conf->isModified()) {
                $conf->save();
            }
        }

        $profileIni = $this->globalSetup->getProfilesIni();
        if ($profileIni->isModified()) {
            $profileIni->save();
            \jProfiles::clear();
        }
    }

    protected function declareLegacyNewEntryPoint(\jInstallerModule $installer)
    {
        foreach ($installer->getNewEntrypoints() as $epId => $epInfo) {
            $this->globalSetup->declareNewEntryPoint($epId, $epInfo['type'], $epInfo['config']);
        }
        if ($this->globalSetup->forLocalConfiguration()) {
            $this->globalSetup->getLocalUrlModifier()->save();
        }
        else {
            $this->globalSetup->getUrlModifier()->save();
        }

    }

    protected function startMessage()
    {
        $this->reporter->start();
    }

    protected function endMessage()
    {
        $this->reporter->end();
    }

    protected function error($msg, $params = null, $fullString = false)
    {
        if (!$fullString) {
            $msg = $this->messages->get($msg, $params);
        }
        $this->reporter->message($msg, 'error');
    }

    protected function ok($msg, $params = null, $fullString = false)
    {
        if (!$fullString) {
            $msg = $this->messages->get($msg, $params);
        }
        $this->reporter->message($msg, '');
    }

    protected function warning($msg, $params = null, $fullString = false)
    {
        if (!$fullString) {
            $msg = $this->messages->get($msg, $params);
        }
        $this->reporter->message($msg, 'warning');
    }

    protected function notice($msg, $params = null, $fullString = false)
    {
        if (!$fullString) {
            $msg = $this->messages->get($msg, $params);
        }
        $this->reporter->message($msg, 'notice');
    }
}
