<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2008-2023 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer;

use Jelix\Core\Config\Compiler;
use Jelix\Core\Profiles;
use Jelix\Dependencies\Item;
use Jelix\Dependencies\ItemException;
use Jelix\Dependencies\Resolver;
use Jelix\Core\App;

use Jelix\IniFile\IniModifierInterface;
use Jelix\Installer\Module\API\ConfigurationHelpers;
use Jelix\Installer\Module\API\LocalConfigurationHelpers;
use Jelix\Installer\Module\API\PreConfigurationHelpers;
use Jelix\Installer\Module\InteractiveConfigurator;
use Jelix\Routing\UrlMapping\EntryPointUrlModifier;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * main class to configure modules.
 *
 * It loads all entry points configurations and all informations about activated
 * modules. Configurator then constructs a tree dependencies for these
 * activated modules, and launch configuration of given module
 *
 * @since 1.7
 */
class Configurator
{
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
     * @var EntryPoint
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
     * @var QuestionHelper
     */
    protected $questionHelper;

    /**
     * @var InputInterface
     */
    protected $consoleInput;

    /**
     * @var OutputInterface
     */
    protected $consoleOutput;

    protected $moduleParameters = array();

    /**
     * initialize the configuration.
     *
     * GlobalSetup reads configurations files of all entry points, and prepare object for
     * each module, needed to configure modules.
     *
     * @param Reporter\ReporterInterface $reporter object which is responsible to process messages (display, storage or other..)
     * @param mixed                      $lang
     */
    public function __construct(
        Reporter\ReporterInterface $reporter,
        GlobalSetup $globalSetup,
        QuestionHelper $helper,
        InputInterface $input,
        OutputInterface $output,
        $lang = ''
    ) {
        $this->reporter = $reporter;
        $this->messages = new \Jelix\Installer\Checker\Messages($lang);

        $this->globalSetup = $globalSetup;

        $this->mainEntryPoint = $globalSetup->getMainEntryPoint();
        $this->consoleInput = $input;
        $this->consoleOutput = $output;
        $this->questionHelper = $helper;
    }

    /**
     * set parameters for the installer of a module.
     *
     * @param string $moduleName the name of the module
     * @param array  $parameters parameters
     */
    public function setModuleParameters($moduleName, $parameters)
    {
        $this->moduleParameters[$moduleName] = $parameters;
    }

    public static function setModuleAsConfigured($moduleName, IniModifierInterface $configIni)
    {
        $configIni->setValue($moduleName.'.enabled', true, 'modules');
    }

    /**
     * @param array     $modulesList           array of module names
     * @param string    $dedicatedEntryPointId entry point from which the module will
     *                                         be mainly accessible
     * @param null|bool $forLocalConfig        true if the configuration should be done into
     *                                         the local configuration instead of app configuration (false).
     *                                         give null to use the default configuration mode
     * @param bool      $forceReconfigure      true if an already configured module should
     *                                         be reconfigured
     */
    public function configureModules(
        $modulesList,
        $dedicatedEntryPointId = 'index',
        $forLocalConfig = null,
        $forceReconfigure = false
    ) {
        $this->startMessage();

        // check that all given modules are existing
        $hasError = false;
        foreach ($modulesList as $name) {
            $component = $this->globalSetup->getModuleComponent($name);
            if (!$component) {
                $this->error('module.unknown', $name);
                $hasError = true;
            }
        }
        if ($hasError) {
            return false;
        }

        $resolver = new InstallationResolver();
        try {
            $modulesToConfigure = $resolver->getItemsToConfigure(
                $this->globalSetup->getModuleInfosAndStatuses(),
                $modulesList,
                $forceReconfigure
            );
            $this->ok('install.dependencies.ok');

        } catch (ItemException $e) {
            $this->processResolverError($e);
            $this->ok('configuration.bad.end');

            return false;
        } catch (\Exception $e) {
            $this->error('install.bad.dependencies');
            $this->ok('configuration.bad.end');

            return false;
        }

        $this->notice('configuration.start');

        $entryPoint = $this->globalSetup->getEntryPointById($dedicatedEntryPointId);
        App::setConfig($entryPoint->getConfigObj());

        if ($entryPoint->getConfigObj()->disableInstallers) {
            $this->notice('install.installers.disabled');
        }

        $this->globalSetup->setCurrentConfiguratorStatus($forLocalConfig ?: false);
        $this->globalSetup->setReadWriteConfigMode(false);

        $componentsToConfigure = $this->runPreConfigure($modulesToConfigure, $entryPoint, $forLocalConfig);
        if ($componentsToConfigure === false) {
            $this->warning('configuration.bad.end');

            return false;
        }

        $this->globalSetup->setReadWriteConfigMode(true);
        if (!$this->runConfigure($componentsToConfigure, $entryPoint)) {
            $this->warning('configuration.bad.end');

            return false;
        }

        $result = $this->runPostConfigure($componentsToConfigure, $entryPoint);
        if (!$result) {
            $this->warning('configuration.bad.end');
        } else {
            $this->ok('configuration.end');
        }
        $this->globalSetup->saveUninstallerIni();

        $this->endMessage();

        return $result;
    }

    /**
     * Force launch of configurator of enabled modules, in the local context.
     *
     * Should be used in the case of a user that just installed the application
     * and want to configure it with local parameter.
     *
     * This is necessary a "forced" configuration, as modules are already
     * enabled by the developers.
     */
    public function localConfigureEnabledModules()
    {
        $this->startMessage();

        $resolver = new InstallationResolver();
        try {
            $modulesToConfigure = $resolver->getAllItemsToConfigureAtInstance(
                $this->globalSetup->getModuleInfosAndStatuses()
            );
            $this->ok('install.dependencies.ok');

        } catch (ItemException $e) {
            $this->processResolverError($e);
            $this->ok('configuration.bad.end');

            return false;
        } catch (\Exception $e) {
            $this->error('install.bad.dependencies');
            $this->ok('configuration.bad.end');

            return false;
        }

        $this->notice('configuration.start');
        $entryPoint = $this->globalSetup->getMainEntryPoint();
        App::setConfig($entryPoint->getConfigObj());

        if ($entryPoint->getConfigObj()->disableInstallers) {
            $this->notice('install.installers.disabled');
        }

        $forLocalConfig = true;
        $this->globalSetup->setCurrentConfiguratorStatus($forLocalConfig);

        $this->globalSetup->setReadWriteConfigMode(false);
        $componentsToConfigure = $this->runPreConfigure($modulesToConfigure, $entryPoint, $forLocalConfig);
        if ($componentsToConfigure === false) {
            $this->warning('configuration.bad.end');

            return false;
        }

        $this->globalSetup->setReadWriteConfigMode(true);
        if (!$this->runConfigure($componentsToConfigure, $entryPoint)) {
            $this->warning('configuration.bad.end');

            return false;
        }

        $result = $this->runPostConfigure($componentsToConfigure, $entryPoint);
        if (!$result) {
            $this->warning('configuration.bad.end');
        } else {
            $this->ok('configuration.end');
        }
        $this->globalSetup->saveUninstallerIni();

        $this->endMessage();

        return $result;
    }

    protected function processResolverError(ItemException $e)
    {
        $item = $e->getItem();

        switch ($e->getCode()) {
            case ItemException::ERROR_CIRCULAR_DEPENDENCY:
            case ItemException::ERROR_REVERSE_CIRCULAR_DEPENDENCY:
                $this->error('module.circular.dependency', $item->getName());

                break;

            case ItemException::ERROR_BAD_ITEM_VERSION:
                $depName = $e->getRelatedData()->getName();
                $versionRange = $item->getDependencyVersion($depName);
                $this->error('module.bad.dependency.version', array($item->getName(), $depName, $versionRange));

                break;

            case ItemException::ERROR_REMOVED_ITEM_IS_NEEDED:
                $depName = $e->getRelatedData()->getName();
                $this->error('install.error.delete.dependency', array($depName, $item->getName()));

                break;

            case ItemException::ERROR_ITEM_TO_INSTALL_SHOULD_BE_REMOVED:
                $depName = $e->getRelatedData()->getName();
                $this->error('install.error.install.dependency', array($depName, $item->getName()));

                break;

            case ItemException::ERROR_DEPENDENCY_MISSING_ITEM:
                $this->error('module.needed', array($item->getName(), implode(',', $e->getRelatedData())));

                break;

            case ItemException::ERROR_INSTALLED_ITEM_IN_CONFLICT:
                $this->error('module.forbidden', array($item->getName(), $e->getRelatedData()->getName()));

                break;

            case ItemException::ERROR_ITEM_TO_INSTALL_IN_CONFLICT:
                $this->error('module.forbidden', array($item->getName(), $e->getRelatedData()->getName()));

                break;

            case ItemException::ERROR_CHOICE_MISSING_ITEM:
                $this->error('module.choice.unknown', array($item->getName(), implode(',', $e->getRelatedData())));

                break;

            case ItemException::ERROR_CHOICE_AMBIGUOUS:
                $this->error('module.choice.ambiguous', array($item->getName(), implode(',', $e->getRelatedData())));

                break;

            case ItemException::ERROR_DEPENDENCY_CANNOT_BE_INSTALLED:
                $depName = $e->getRelatedData()->getName();
                $this->error('module.dependency.error', array($depName, $item->getName()));

                break;
        }
    }


    /**
     * Launch the preConfigure method of each modules configurator.
     *
     * @param \Jelix\Dependencies\Item[] $moduleschain
     * @param bool|null                  $forLocalConfig If not null, uninstalled modules will
     *                                   be configured into the local or app scope, according
     *                                   to this given value, else installed module will be configured
     *                                   in the scope already indicated into their current status
     *
     * @return array|bool
     */
    protected function runPreConfigure(&$moduleschain, EntryPoint $entryPoint, $forLocalConfig)
    {
        $result = true;
        $componentsToInstall = array();
        $installersDisabled = $entryPoint->getConfigObj()->disableInstallers;

        $preconfigHelpers = new PreConfigurationHelpers($this->globalSetup);

        foreach ($moduleschain as $resolverItem) {
            /** @var ModuleInstallerLauncher $component */
            $component = $this->globalSetup->getModuleComponent($resolverItem->getName());

            try {
                if ($installersDisabled) {
                    $configurator = null;
                } else {
                    if (isset($this->moduleParameters[$component->getName()])) {
                        $parameters = $this->moduleParameters[$component->getName()];
                    } else {
                        $parameters = null;
                    }
                    $configurator = $component->getConfigurator($component::CONFIGURATOR_TO_CONFIGURE, $forLocalConfig, $parameters);
                }
                $componentsToInstall[] = array($configurator, $component);

                if ($configurator) {
                    $this->globalSetup->setCurrentProcessedModule($component->getName());
                    $configurator->preConfigure($preconfigHelpers);
                }
            } catch (Exception $e) {
                $result = false;
                $this->error($e->getLocaleKey(), $e->getLocaleParameters());
            } catch (\Exception $e) {
                $result = false;
                $this->error('configuration.module.error', array($component->getName(), $e->getMessage()));
            }
        }
        if (!$result) {
            return false;
        }

        return $componentsToInstall;
    }

    /**
     * @param array[] $componentsToConfigure each items have a
     *                                       \Jelix\Installer\Module\Configurator object and a \Jelix\Installer\ModuleInstallerLauncher object
     *
     * @return bool
     */
    protected function runConfigure($componentsToConfigure, EntryPoint $entryPoint)
    {
        $result = true;
        $interactiveCli = new InteractiveConfigurator(
            $this->questionHelper,
            $this->consoleInput,
            $this->consoleOutput
        );
        $configHelpers = new ConfigurationHelpers($this->globalSetup, $interactiveCli);
        $localConfigHelpers = new LocalConfigurationHelpers($this->globalSetup, $interactiveCli);

        try {
            foreach ($componentsToConfigure as $item) {
                /** @var ModuleInstallerLauncher $component */
                /** @var Module\Configurator $configurator */
                list($configurator, $component) = $item;

                $this->notice('configuration.module.start', array($component->getName()));
                if ($configurator) {
                    $this->globalSetup->setCurrentProcessedModule($component->getName());
                    if ($this->globalSetup->forLocalConfiguration()) {
                        if ($component->isEnabledOnlyInLocalConfiguration()) {
                            $this->execModuleConfigure($configurator, $configHelpers);
                        }
                        $configurator->localConfigure($localConfigHelpers);
                    } else {
                        $this->execModuleConfigure($configurator, $configHelpers);
                    }
                    $component->setInstallParameters($configurator->getParameters());
                }
                $component->saveModuleStatus();
                $this->saveConfigurationFiles($entryPoint);
            }
        } catch (Exception $e) {
            $result = false;
            $this->error($e->getLocaleKey(), $e->getLocaleParameters());
        } catch (\Exception $e) {
            $result = false;
            $this->error('configuration.module.error', array($component->getName(), $e->getMessage()));
        }

        return $result;
    }

    protected function execModuleConfigure(Module\Configurator $configurator, ConfigurationHelpers $configHelpers)
    {
        $configurator->configure($configHelpers);

        $newEntryPoints = $configurator->getEntryPointsToCreate();
        foreach($newEntryPoints as $newEP) {
            $configHelpers->createEntryPoint(
                $newEP->getEntryPointFileToCopy(),
                $newEP->getId(),
                $newEP->getConfigFile(),
                $newEP->getType(),
                $newEP->getConfigFileToCopy()
            );
        }

        if ($this->globalSetup->forLocalConfiguration()) {
            $mapModifier = $this->globalSetup->getLocalUrlModifier();
        }
        else {
            $mapModifier = $this->globalSetup->getUrlModifier();
        }

        $epModifier = new EntryPointUrlModifier($mapModifier, $configurator->getModuleName());
        $mapModifier->removeAllUrlOfModule($configurator->getModuleName());
        $configurator->declareUrls($epModifier);

        $prefix = $this->globalSetup->getCurrentModulePath().'install/';
        foreach ($configurator->getFilesToCopy() as $source => $target) {

            if (is_array($target)) {
                list($target, $overwrite) = $target;
            }
            else {
                $overwrite = true;
            }

            if (is_dir($prefix.$source)) {
                $configHelpers->copyDirectoryContent($source, $target, $overwrite);
            } elseif (is_file($prefix.$source)) {
                $configHelpers->copyFile($source, $target, $overwrite);
            }
        }
    }

    protected function execModuleUnconfigure(Module\Configurator $configurator, ConfigurationHelpers $configHelpers)
    {
        $newEntryPoints = $configurator->getEntryPointsToCreate();
        foreach($newEntryPoints as $newEP) {
            $configHelpers->removeEntryPoint(
                $newEP->getId(),
                $newEP->getConfigFile(),
                $newEP->getType()
            );
        }

        $this->globalSetup->getLocalUrlModifier()->removeAllUrlOfModule($configurator->getModuleName());
        $this->globalSetup->getUrlModifier()->removeAllUrlOfModule($configurator->getModuleName());

        $configurator->unconfigure($configHelpers);
        $prefix = $this->globalSetup->getCurrentModulePath().'install/';
        foreach ($configurator->getFilesToCopy() as $source => $target) {
            if (is_array($target)) {
                $target = $target[0];
            }
            if (is_dir($prefix.$source)) {
                $configHelpers->removeDirectoryContent($target);
            } elseif (is_file($prefix.$source)) {
                $configHelpers->removeFile($target);
            }
        }
    }

    /**
     * @param array[] $componentsToConfigure each items have a
     *                                       \Jelix\Installer\Module\Configurator object and a \Jelix\Installer\ModuleInstallerLauncher object
     *
     * @return bool
     */
    protected function runPostConfigure($componentsToConfigure, EntryPoint $entryPoint)
    {
        $result = true;
        $interactiveCli = new InteractiveConfigurator(
            $this->questionHelper,
            $this->consoleInput,
            $this->consoleOutput
        );
        $configHelpers = new ConfigurationHelpers($this->globalSetup, $interactiveCli);

        foreach ($componentsToConfigure as $item) {
            try {
                /** @var ModuleInstallerLauncher $component */
                /** @var Module\Configurator $configurator */
                list($configurator, $component) = $item;
                if ($configurator) {
                    $this->globalSetup->setCurrentProcessedModule($component->getName());
                    $configurator->postConfigure($configHelpers);
                    $this->saveConfigurationFiles($entryPoint);
                }
            } catch (Exception $e) {
                $result = false;
                $this->error($e->getLocaleKey(), $e->getLocaleParameters());
            } catch (\Exception $e) {
                $result = false;
                $this->error('configurator.module.error', array($component->getName(), $e->getMessage()));
            }
        }

        return $result;
    }

    /**
     * Unconfigure some modules.
     *
     * @param array     $modulesList           array of module names
     * @param string    $dedicatedEntryPointId entry point from which the module is
     *                                         mainly accessible
     * @param null|bool $forLocalConfig        true if the configuration should be done into
     *                                         the local configuration instead of app configuration (false).
     *                                         give null to use the default configuration mode
     */
    public function unconfigureModule(
        $modulesList,
        $dedicatedEntryPointId = 'index',
        $forLocalConfig = null
    ) {
        $this->startMessage();

        // check that all given modules are existing
        $hasError = false;
        foreach ($modulesList as $name) {
            $component = $this->globalSetup->getModuleComponent($name);
            if (!$component) {
                $this->error('module.unknown', $name);
                $hasError = true;
            }
        }
        if ($hasError) {
            return false;
        }

        $resolver = new InstallationResolver();
        try {
            $modulesToUnconfigure = $resolver->getItemsToUnConfigure(
                $this->globalSetup->getModuleInfosAndStatuses(),
                $modulesList
            );
            $this->ok('install.dependencies.ok');

        } catch (ItemException $e) {
            $this->processResolverError($e);
            $this->ok('configuration.bad.end');

            return false;
        } catch (\Exception $e) {
            $this->error('install.bad.dependencies');
            $this->ok('configuration.bad.end');

            return false;
        }

        $this->notice('configuration.start');
        $entryPoint = $this->globalSetup->getEntryPointById($dedicatedEntryPointId);
        App::setConfig($entryPoint->getConfigObj());

        if ($entryPoint->getConfigObj()->disableInstallers) {
            $this->notice('install.installers.disabled');
        }

        $this->globalSetup->setCurrentConfiguratorStatus($forLocalConfig);
        $this->globalSetup->setReadWriteConfigMode(false);

        $componentsToUnconfigure = $this->runPreUnconfigure($modulesToUnconfigure, $entryPoint);
        if ($componentsToUnconfigure === false) {
            $this->warning('configuration.bad.end');

            return false;
        }

        $this->globalSetup->setReadWriteConfigMode(true);
        if (!$this->runUnconfigure($componentsToUnconfigure, $entryPoint)) {
            $this->warning('configuration.bad.end');

            return false;
        }

        $result = $this->runPostUnconfigure($componentsToUnconfigure, $entryPoint);
        if (!$result) {
            $this->warning('configuration.bad.end');
        } else {
            $this->ok('configuration.end');
        }
        $this->globalSetup->saveUninstallerIni();
        $this->endMessage();

        return $result;
    }

    /**
     * Launch the preUnconfigure method of each modules configurator.
     *
     * @param \Jelix\Dependencies\Item[] $moduleschain
     *
     * @return array|bool
     */
    protected function runPreUnconfigure(&$moduleschain, EntryPoint $entryPoint)
    {
        $result = true;
        $componentsToInstall = array();
        $installersDisabled = $entryPoint->getConfigObj()->disableInstallers;
        $preconfigHelpers = new PreConfigurationHelpers($this->globalSetup);

        foreach ($moduleschain as $resolverItem) {
            /** @var ModuleInstallerLauncher $component */
            $component = $this->globalSetup->getModuleComponent($resolverItem->getName());

            try {
                if ($installersDisabled) {
                    $configurator = null;
                } else {
                    $configurator = $component->getConfigurator($component::CONFIGURATOR_TO_UNCONFIGURE);
                }
                $componentsToInstall[] = array($configurator, $component);

                if ($configurator) {
                    $this->globalSetup->setCurrentProcessedModule($component->getName());

                    $component->setInstallParameters($configurator->getParameters());

                    $configurator->preUnconfigure($preconfigHelpers);
                }
            } catch (Exception $e) {
                $result = false;
                $this->error($e->getLocaleKey(), $e->getLocaleParameters());
            } catch (\Exception $e) {
                $result = false;
                $this->error('configuration.module.error', array($component->getName(), $e->getMessage()));
            }
        }
        if (!$result) {
            return false;
        }

        return $componentsToInstall;
    }

    /**
     * @param array[] $componentsToUnconfigure each items have a
     *                                         \Jelix\Installer\Module\Configurator object and a \Jelix\Installer\ModuleInstallerLauncher object
     *
     * @return bool
     */
    protected function runUnconfigure($componentsToUnconfigure, EntryPoint $entryPoint)
    {
        $result = true;
        $interactiveCli = new InteractiveConfigurator(
            $this->questionHelper,
            $this->consoleInput,
            $this->consoleOutput
        );
        $configHelpers = new ConfigurationHelpers($this->globalSetup, $interactiveCli);
        $localConfigHelpers = new LocalConfigurationHelpers($this->globalSetup, $interactiveCli);

        // In $componentsToConfigure, we have the module to unconfigure and
        // all of its reverse dependencies to unconfigure. If none of them have an
        // install script, we don't need to register them into the uninstaller.ini.php
        // and we don't need to backup their uninstall.php script.
        // Else, to uninstall properly the module, we need its uninstall.php script,
        // but also all of its reverse dependencies into uninstaller.ini.php
        $shouldBackupUninstallScript = array_reduce(
            $componentsToUnconfigure,
            function ($carry, $item) {
                // @var \Jelix\Installer\Module\Configurator $item[1]
                return $carry | $item[1]->hasUninstallScript();
            },
            false
        );

        try {
            foreach ($componentsToUnconfigure as $item) {
                /** @var ModuleInstallerLauncher $component */
                /** @var Module\Configurator $configurator */
                list($configurator, $component) = $item;

                $this->notice('configuration.module.unconfigure.start', array($component->getName()));

                if ($configurator) {
                    $this->globalSetup->setCurrentProcessedModule($component->getName());
                    if ($this->globalSetup->forLocalConfiguration()) {
                        if ($component->isEnabledOnlyInLocalConfiguration()) {
                            $this->execModuleUnconfigure($configurator, $configHelpers);
                        }
                        $configurator->localUnconfigure($localConfigHelpers);
                    } else {
                        $this->execModuleUnconfigure($configurator, $configHelpers);
                    }
                }
                $component->saveModuleStatus();
                if ($shouldBackupUninstallScript) {
                    $this->globalSetup->saveUninstallerData($component->getModuleStatus());
                }

                $this->saveConfigurationFiles($entryPoint);
            }
        } catch (Exception $e) {
            $result = false;
            $this->error($e->getLocaleKey(), $e->getLocaleParameters());
        } catch (\Exception $e) {
            $result = false;
            $this->error('configuration.module.error', array($component->getName(), $e->getMessage()));
        }

        return $result;
    }

    /**
     * @param array[] $componentsToUnconfigure each items have a
     *                                         \Jelix\Installer\Module\Configurator object and a \Jelix\Installer\ModuleInstallerLauncher object
     *
     * @return bool
     */
    protected function runPostUnconfigure($componentsToUnconfigure, EntryPoint $entryPoint)
    {
        $result = true;
        $interactiveCli = new InteractiveConfigurator(
            $this->questionHelper,
            $this->consoleInput,
            $this->consoleOutput
        );
        $configHelpers = new ConfigurationHelpers($this->globalSetup, $interactiveCli);

        foreach ($componentsToUnconfigure as $item) {
            try {
                /** @var ModuleInstallerLauncher $component */
                /** @var Module\Configurator $configurator */
                list($configurator, $component) = $item;
                if ($configurator) {
                    $this->globalSetup->setCurrentProcessedModule($component->getName());
                    $configurator->postUnconfigure($configHelpers);
                    $this->saveConfigurationFiles($entryPoint);
                }
            } catch (Exception $e) {
                $result = false;
                $this->error($e->getLocaleKey(), $e->getLocaleParameters());
            } catch (\Exception $e) {
                $result = false;
                $this->error('configuration.module.error', array($component->getName(), $e->getMessage()));
            }
        }

        return $result;
    }

    protected function saveConfigurationFiles(EntryPoint $entryPoint)
    {

        // we save the configuration at each module because its
        // configurator may have modified it, and we want to save it
        // in case the next module configurator fails.
        $fullConfig = $this->globalSetup->getFullConfigIni();
        if ($fullConfig->isModified()) {
            $fullConfig->save();
            $epConfig = $entryPoint->getSingleConfigIni();
            if ($epConfig instanceof IniModifierInterface) {
                $epConfig->save();
            }

            // we re-load configuration file for each module because
            // previous module configurator could have modify it.
            $compiler = new Compiler($entryPoint->getConfigFileName(),
                $entryPoint->getScriptName()
            );
            $entryPoint->setConfigObj(
                $compiler->read(true)
            );
            App::setConfig($entryPoint->getConfigObj());
        }
        $this->globalSetup->getUrlModifier()->save();
        $this->globalSetup->getLocalUrlModifier()->save();
        $profileIni = $this->globalSetup->getProfilesIni();
        if ($profileIni->isModified()) {
            $profileIni->save();
            Profiles::clear();
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
