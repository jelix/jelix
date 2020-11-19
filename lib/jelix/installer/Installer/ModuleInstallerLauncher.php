<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2008-2019 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer;

use Jelix\Dependencies\Item;
use Jelix\Dependencies\Resolver;
use Jelix\Version\VersionComparator;

/**
 * Manage status of a module and its installer/updaters.
 *
 * @since 1.7
 */
class ModuleInstallerLauncher
{
    /**
     *  @var string  name of the module
     */
    protected $name = '';

    /**
     * @var GlobalSetup
     */
    protected $globalSetup;

    /**
     * @var string the minimum version of jelix for which the component is compatible
     */
    protected $jelixMinVersion = '*';

    /**
     * @var string the maximum version of jelix for which the component is compatible
     */
    protected $jelixMaxVersion = '*';

    /**
     * code error of the installation.
     */
    public $inError = 0;

    /**
     * informations of the modules from their module.xml.
     *
     * @var \Jelix\Core\Infos\ModuleInfos
     */
    protected $moduleInfos;

    /**
     * status of modules into the application.
     *
     * @var ModuleStatus
     */
    protected $moduleStatus;

    /**
     * @var Module\Configurator
     */
    protected $moduleConfigurator;

    /**
     * @var \jInstallerModule|Module\Installer
     */
    protected $moduleInstaller;

    /**
     * @var \jInstallerModule|Module\Uninstaller
     */
    protected $moduleUninstaller;

    /**
     * @var \jInstallerModule[]|Module\Installer[]
     */
    protected $moduleUpgraders;

    /**
     * @var \jInstallerModule|Module\Installer
     */
    protected $moduleMainUpgrader;

    protected $upgradersContexts = array();

    public function __construct(ModuleStatus $moduleStatus, GlobalSetup $globalSetup)
    {
        $this->globalSetup = $globalSetup;
        $this->moduleStatus = $moduleStatus;
        $this->name = $moduleStatus->getName();
    }

    /**
     * initialize the object, by reading the identity file.
     */
    public function init()
    {
        if ($this->moduleInfos) {
            return;
        }
        $this->moduleInfos = \Jelix\Core\Infos\ModuleInfos::load($this->moduleStatus->getPath());

        if ($this->moduleInfos->version == '') {
            throw new Exception('module.missing.version', array($this->name));
        }

        foreach ($this->moduleInfos->dependencies as $dep) {
            if ($dep['type'] == 'module' && $dep['name'] == 'jelix') {
                $this->jelixMinVersion = $dep['minversion'];
                $this->jelixMaxVersion = $dep['maxversion'];

                break;
            }
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->moduleStatus->getPath();
    }

    public function getSourceVersion()
    {
        return $this->moduleInfos->version;
    }

    public function getSourceDate()
    {
        return $this->moduleInfos->versionDate;
    }

    public function getJelixVersion()
    {
        return array($this->jelixMinVersion, $this->jelixMaxVersion);
    }

    public function getDependencies()
    {
        return $this->moduleInfos->dependencies;
    }

    public function getIncompatibilities()
    {
        return $this->moduleInfos->incompatibilities;
    }

    public function isEnabled()
    {
        return $this->moduleStatus->isEnabled;
    }

    public function isInstalled()
    {
        return $this->moduleStatus->isInstalled;
    }

    public function isEnabledOnlyInLocalConfiguration()
    {
        return $this->moduleStatus->configurationScope == ModuleStatus::CONFIG_SCOPE_LOCAL;
    }

    public function getDbProfile()
    {
        return $this->moduleStatus->dbProfile;
    }

    /**
     * @throws Exception
     *
     * @return bool
     */
    public function isUpgraded()
    {
        if (!$this->isInstalled()) {
            return false;
        }
        if ($this->moduleStatus->version == '') {
            throw new Exception('installer.ini.missing.version', array($this->name));
        }

        return VersionComparator::compareVersion($this->moduleInfos->version, $this->moduleStatus->version) == 0;
    }

    public function getInstalledVersion()
    {
        return $this->moduleStatus->version;
    }

    public function setInstalledVersion($version)
    {
        $this->moduleStatus->version = $version;
    }

    /**
     * Set installation parameters into module infos.
     *
     * @param string[] $parameters
     */
    public function setInstallParameters($parameters)
    {
        $this->moduleStatus->parameters = $parameters;
    }

    /**
     * save module infos into the app config or the local config.
     */
    public function saveModuleStatus()
    {
        if ($this->moduleStatus->configurationScope == ModuleStatus::CONFIG_SCOPE_LOCAL ||
            $this->globalSetup->forLocalConfiguration()
        ) {
            $conf = $this->globalSetup->getSystemConfigIni(true);
            $conf['local'] = $this->globalSetup->getLocalConfigIni();
        } else {
            $this->moduleStatus->clearInfos($this->globalSetup->getLocalConfigIni());
            $conf = $this->globalSetup->getSystemConfigIni();
        }
        $this->moduleStatus->saveInfos($conf, ($this->moduleConfigurator ? $this->moduleConfigurator->getDefaultParameters() : array()));
    }

    /**
     * @return string[]
     */
    public function getInstallParameters()
    {
        return $this->moduleStatus->parameters;
    }

    /**
     * Backup the uninstall.php outside the module.
     *
     * It allows to run the uninstall.php script of the module, even if the
     * module does not exist any more. This could be the case when the module is
     * bundled into a composer package, and we removed the composer package from
     * composer.json before deploying the application.
     * The script is copied into the app:install/uninstall/ directory.
     *
     * For some components that don't have an uninstaller script, we should
     * reference them into uninstaller.ini.php anyway, because we need their
     * informations because they are reverse dependencies of an other module
     * we should uninstall.
     *
     * @return bool true if there is a uninstall.php script
     */
    public function backupUninstallScript()
    {
        $targetPath = \jApp::appPath('install/uninstall/'.$this->moduleStatus->getName());
        \jFile::createDir($targetPath);
        copy($this->moduleStatus->getPath().'module.xml', $targetPath);
        $uninstallerIni = $this->globalSetup->getUninstallerIni();
        $this->moduleStatus->saveInfos($uninstallerIni);

        if (file_exists($this->moduleStatus->getPath().'install/uninstall.php')) {
            \jFile::createDir($targetPath.'/install');
            copy(
                $this->moduleStatus->getPath().'install/uninstall.php',
                $targetPath.'/install'
            );

            return true;
        }

        return false;
    }

    public function hasUninstallScript()
    {
        return file_exists($this->moduleStatus->getPath().'install/uninstall.php');
    }

    const CONFIGURATOR_TO_CONFIGURE = 0;
    const CONFIGURATOR_TO_UNCONFIGURE = 1;

    /**
     * instancies the object which is responsible to configure the module.
     *
     * @param int  $actionMode            one of CONFIGURATOR_TO_* constants
     * @param bool $forLocalConfiguration true if the configuration should be done
     *                                    with the local configuration, else it will be done with the
     *                                    main configuration
     * @param array install parameters
     * @param null|mixed $installParameters
     *
     * @throws Exception when configurator class not found
     *
     * @return null|Module\Configurator the configurator, or null
     *                                  if there isn't any configurator
     */
    public function getConfigurator($actionMode, $forLocalConfiguration = null, $installParameters = null)
    {
        if (!$this->moduleStatus->isEnabled) {
            if ($forLocalConfiguration !== null) {
                // if the module is configured for the first time, we take care
                // about the target configuration files. In the case of
                // configuring the module for local configuration, it means
                // that the module is installed by the user, not by the developer
                // so all of its configuration should be done on local configuration
                // files only
                if ($forLocalConfiguration) {
                    $this->moduleStatus->configurationScope = ModuleStatus::CONFIG_SCOPE_LOCAL;
                } else {
                    $this->moduleStatus->configurationScope = ModuleStatus::CONFIG_SCOPE_APP;
                }
            }
        }

        $this->moduleStatus->isEnabled = ($actionMode == self::CONFIGURATOR_TO_CONFIGURE);

        if ($actionMode == self::CONFIGURATOR_TO_CONFIGURE) {
            // if the module was unconfigured before, let's erase information
            // about it from the uninstaller.ini
            $uninstallerIni = $this->globalSetup->getUninstallerIni();
            $this->moduleStatus->clearInfos($uninstallerIni);
        }

        return $this->createConfigurator($installParameters);
    }

    protected function createConfigurator($installParameters = null)
    {
        // false means that there isn't an installer for the module
        if ($this->moduleConfigurator === false) {
            return null;
        }

        if ($this->moduleConfigurator === null) {
            if (!file_exists($this->moduleStatus->getPath().'install/configure.php') ||
                $this->moduleStatus->skipInstaller
            ) {
                $this->moduleConfigurator = false;

                return null;
            }

            require_once $this->moduleStatus->getPath().'install/configure.php';

            $cname = $this->name.'ModuleConfigurator';
            if (!class_exists($cname)) {
                throw new Exception('module.configurator.class.not.found', array($cname, $this->name));
            }

            $this->moduleConfigurator = new $cname(
                $this->name,
                $this->name,
                $this->moduleStatus->getPath(),
                $this->moduleInfos->version
            );

            // setup installation parameters
            $parameters = $this->moduleConfigurator->getDefaultParameters();
            $parameters = array_merge($parameters, $this->getInstallParameters());
            if ($installParameters) {
                $parameters = array_merge($parameters, $installParameters);
            }
            $this->moduleConfigurator->setParameters($parameters);
        }

        return $this->moduleConfigurator;
    }

    /**
     * instancies the object which is responsible to install the module.
     *
     * @throws Exception when install class not found
     *
     * @return null|\jIInstallerComponent|Module\InstallerInterface the installer, or null
     *                                                              if there isn't any installer
     */
    public function getInstaller()
    {

        // false means that there isn't an installer for the module
        if ($this->moduleInstaller === false) {
            return null;
        }

        if ($this->moduleInstaller === null) {
            if (!file_exists($this->moduleStatus->getPath().'install/install.php') ||
                $this->moduleStatus->skipInstaller
            ) {
                $this->moduleInstaller = false;

                return null;
            }

            require_once $this->moduleStatus->getPath().'install/install.php';

            $cname = $this->name.'ModuleInstaller';
            if (!class_exists($cname)) {
                throw new Exception('module.installer.class.not.found', array($cname, $this->name));
            }

            $this->moduleInstaller = new $cname(
                $this->name,
                $this->name,
                $this->moduleStatus->getPath(),
                $this->moduleInfos->version,
                true
            );
        }

        if ($this->moduleInstaller instanceof \jIInstallerComponent) {
            $this->moduleInstaller->setContext($this->globalSetup->getInstallerContexts($this->name));
            $mainEntryPoint = $this->globalSetup->getMainEntryPoint();
            if (!$mainEntryPoint->legacyInstallerEntryPoint) {
                $mainEntryPoint->legacyInstallerEntryPoint = new \jInstallerEntryPoint($mainEntryPoint, $this->globalSetup);
            }
            $this->moduleInstaller->setEntryPoint(
                $mainEntryPoint->legacyInstallerEntryPoint,
                $this->moduleStatus->dbProfile
            );
        }

        $configurator = $this->createConfigurator();
        if ($configurator) {
            $parameters = $configurator->getParameters();
        } else {
            $parameters = $this->moduleStatus->parameters;
        }
        $this->moduleInstaller->setParameters($parameters);

        return $this->moduleInstaller;
    }

    /**
     * instancies the object which is responsible to uninstall the module.
     *
     * @throws Exception when install class not found
     *
     * @return null|\jIInstallerComponent|Module\UninstallerInterface the uninstaller, or null
     *                                                                if there isn't any uninstaller
     */
    public function getUninstaller()
    {

        // false means that there isn't an installer for the module
        if ($this->moduleUninstaller === false) {
            return null;
        }

        if ($this->moduleUninstaller === null) {
            if ($this->moduleStatus->skipInstaller) {
                $this->moduleUninstaller = false;

                return null;
            }

            $installer = $this->getInstaller();
            if ($installer && $installer instanceof \jIInstallerComponent) {
                $this->moduleUninstaller = $installer;
                $this->moduleUninstaller->initDbProfile($this->moduleStatus->dbProfile);
                $this->moduleUninstaller->setParameters($this->getInstallParameters());

                return $this->moduleUninstaller;
            }

            if (!file_exists($this->moduleStatus->getPath().'install/uninstall.php')) {
                $this->moduleUninstaller = false;

                return null;
            }

            require_once $this->moduleStatus->getPath().'install/uninstall.php';

            $cname = $this->name.'ModuleUninstaller';
            if (!class_exists($cname)) {
                throw new Exception('module.uninstaller.class.not.found', array($cname, $this->name));
            }

            $this->moduleUninstaller = new $cname(
                $this->name,
                $this->name,
                $this->moduleStatus->getPath(),
                $this->moduleInfos->version,
                true
            );
        }

        if ($this->moduleUninstaller instanceof \jIInstallerComponent) {
            $this->moduleUninstaller->initDbProfile($this->moduleStatus->dbProfile);
        }

        $configurator = $this->createConfigurator();
        if ($configurator) {
            $installParameters = $configurator->getParameters();
        } else {
            $installParameters = $this->getInstallParameters();
        }

        $this->moduleUninstaller->setParameters($installParameters);

        return $this->moduleUninstaller;
    }

    /**
     * return the list of objects which are responsible to upgrade the module
     * from the current installed version of the module.
     *
     * this method should be called after verifying and resolving
     * dependencies. Needed modules should be
     * installed/upgraded before calling this method
     *
     * @throws Exception if an error occurs during the install
     *
     * @return \jIInstallerComponent[]|Module\InstallerInterface[]
     */
    public function getUpgraders()
    {
        $configurator = $this->createConfigurator();
        if ($configurator) {
            $installParameters = $configurator->getParameters();
        } else {
            $installParameters = $this->moduleStatus->parameters;
        }

        if ($this->moduleMainUpgrader === null) {
            // script name for Jelix 1.6 in modules compatibles with both Jelix 1.7 and 1.6
            if (file_exists($this->moduleStatus->getPath().'install/upgrade_1_6.php')) {
                $file = $this->moduleStatus->getPath().'install/upgrade_1_6.php';
            }
            // script name for modules compatible with Jelix <=1.6
            elseif (file_exists($this->moduleStatus->getPath().'install/upgrade.php')) {
                $file = $this->moduleStatus->getPath().'install/upgrade.php';
            } else {
                $file = '';
            }

            if ($file == '' || $this->moduleStatus->skipInstaller) {
                $this->moduleMainUpgrader = false;
            } else {
                require_once $file;

                $cname = $this->name.'ModuleUpgrader';
                if (!class_exists($cname)) {
                    throw new Exception('module.upgrader.class.not.found', array($cname, $this->name));
                }

                $this->moduleMainUpgrader = new $cname(
                    $this->name,
                    $this->name,
                    $this->moduleStatus->getPath(),
                    $this->moduleInfos->version,
                    false
                );

                $this->moduleMainUpgrader->setTargetVersions(array($this->moduleInfos->version));
                $this->moduleMainUpgrader->setParameters($installParameters);
            }
        }

        if ($this->moduleUpgraders === null) {
            $this->moduleUpgraders = array();

            $p = $this->moduleStatus->getPath().'install/';
            if (!file_exists($p) || $this->moduleStatus->skipInstaller) {
                return array();
            }

            // we get the list of files for the upgrade
            $fileList = array();
            if ($handle = opendir($p)) {
                while (($f = readdir($handle)) !== false) {
                    if (!is_dir($p.$f)) {
                        if (preg_match('/^upgrade_to_([^_]+)_([^\.]+)\.php$/', $f, $m)) {
                            $fileList[] = array($f, $m[1], $m[2]);
                        } elseif (preg_match('/^upgrade_([^\.]+)\.php$/', $f, $m)) {
                            $fileList[] = array($f, '', $m[1]);
                        }
                    }
                }
                closedir($handle);
            }

            // now we order the list of file
            foreach ($fileList as $fileInfo) {
                require_once $p.$fileInfo[0];
                $cname = $this->name.'ModuleUpgrader_'.$fileInfo[2];
                if (!class_exists($cname)) {
                    throw new Exception('module.upgrader.class.not.found', array($cname, $this->name));
                }

                $upgrader = new $cname(
                    $this->name,
                    $fileInfo[2],
                    $this->moduleStatus->getPath(),
                    $fileInfo[1],
                    false
                );

                if ($fileInfo[1] && count($upgrader->getTargetVersions()) == 0) {
                    $upgrader->setTargetVersions(array($fileInfo[1]));
                }
                if (count($upgrader->getTargetVersions()) == 0) {
                    throw new Exception('module.upgrader.missing.version', array($fileInfo[0], $this->name));
                }
                $this->moduleUpgraders[] = $upgrader;
            }
        }

        if ((count($this->moduleUpgraders) || $this->moduleMainUpgrader) && $this->moduleStatus->version == '') {
            throw new Exception('installer.ini.missing.version', array($this->name));
        }

        $list = array();

        foreach ($this->moduleUpgraders as $upgrader) {
            $foundVersion = '';
            // check the version
            foreach ($upgrader->getTargetVersions() as $version) {
                if (VersionComparator::compareVersion($this->moduleStatus->version, $version) >= 0) {
                    // we don't execute upgraders having a version lower than the installed version (they are old upgrader)
                    continue;
                }
                if (VersionComparator::compareVersion($this->moduleInfos->version, $version) < 0) {
                    // we don't execute upgraders having a version higher than the version indicated in the module.xml
                    continue;
                }
                $foundVersion = $version;
                // when multiple version are specified, we take the first one which is ok
                break;
            }
            if (!$foundVersion) {
                continue;
            }

            $upgrader->setVersion($foundVersion);

            // we have to check the date of versions
            // we should not execute the updater in some case.
            // for example, we have an updater for the 1.2 and 2.3 version
            // we have the 1.4 installed, and want to upgrade to the 2.5 version
            // we should not execute the update for 2.3 since modifications have already been
            // made into the 1.4. The only way to know that, is to compare date of versions
            if ($upgrader->getDate() != '') {
                $upgraderDate = $this->_formatDate($upgrader->getDate());
                // the date of the first version installed into the application
                $firstVersionDate = $this->_formatDate($this->globalSetup->getInstallerIni()
                    ->getValue($this->name.'.firstversion.date', 'modules'));

                if ($firstVersionDate !== null) {
                    if ($firstVersionDate >= $upgraderDate) {
                        continue;
                    }
                }

                // the date of the current installed version
                $currentVersionDate = $this->_formatDate($this->globalSetup->getInstallerIni()
                    ->getValue($this->name.'.version.date', 'modules'));
                if ($currentVersionDate !== null) {
                    if ($currentVersionDate >= $upgraderDate) {
                        continue;
                    }
                }
            }

            $class = get_class($upgrader);
            if (!isset($this->upgradersContexts[$class])) {
                $this->upgradersContexts[$class] = array();
            }

            if ($upgrader instanceof \jIInstallerComponent) {
                $upgrader->setContext($this->upgradersContexts[$class]);
                $mainEntryPoint = $this->globalSetup->getMainEntryPoint();
                if (!$mainEntryPoint->legacyInstallerEntryPoint) {
                    $mainEntryPoint->legacyInstallerEntryPoint = new \jInstallerEntryPoint($mainEntryPoint, $this->globalSetup);
                }
                $upgrader->setEntryPoint(
                    $mainEntryPoint->legacyInstallerEntryPoint,
                    $this->moduleStatus->dbProfile
                );
            }

            $upgrader->setParameters($installParameters);
            $list[] = $upgrader;
        }

        // now let's sort upgrader, to execute them in the right order (oldest before newest)
        usort($list, function ($upgA, $upgB) {
            return VersionComparator::compareVersion($upgA->getVersion(), $upgB->getVersion());
        });

        if ($this->moduleMainUpgrader && VersionComparator::compareVersion($this->moduleStatus->version, $this->moduleInfos->version) < 0) {
            $list[] = $this->moduleMainUpgrader;
        }

        return $list;
    }

    public function installFinished()
    {
        if ($this->moduleInstaller instanceof \jIInstallerComponent) {
            $this->globalSetup->updateInstallerContexts($this->name, $this->moduleInstaller->getContexts());
        } else {
            // remove legacy contexts
            $this->globalSetup->removeInstallerContexts($this->name);
        }
    }

    public function upgradeFinished($upgrader)
    {
        if ($upgrader instanceof \jIInstallerComponent) {
            $class = get_class($upgrader);
            $this->upgradersContexts[$class] = $upgrader->getContexts();
        }
    }

    public function uninstallFinished()
    {
        $this->globalSetup->removeInstallerContexts($this->name);
    }

    protected function _formatDate($date)
    {
        if ($date !== null) {
            if (strlen($date) == 10) {
                $date .= ' 00:00';
            } elseif (strlen($date) > 16) {
                $date = substr($date, 0, 16);
            }
        }

        return $date;
    }

    /**
     * @param mixed $forConfiguration
     *
     * @return Item
     */
    public function getResolverItem($forConfiguration = false)
    {
        if ($forConfiguration) {
            $action = $this->getConfigureAction();
        } else {
            $action = $this->getInstallAction();
        }
        if ($action == Resolver::ACTION_UPGRADE) {
            $item = new Item($this->name, $this->moduleStatus->version, true);
            $item->setAction(Resolver::ACTION_UPGRADE, $this->moduleInfos->version);
        } else {
            $item = new Item($this->name, $this->moduleStatus->version, $this->isInstalled());
            $item->setAction($action);
        }

        foreach ($this->moduleInfos->dependencies as $dep) {
            if ($dep['type'] == 'choice') {
                $list = array();
                foreach ($dep['choice'] as $choice) {
                    $list[$choice['name']] = $choice['version'];
                }
                $item->addAlternativeDependencies($list);
            } else {
                $item->addDependency($dep['name'], $dep['version']);
            }
        }

        foreach ($this->moduleInfos->incompatibilities as $dep) {
            $item->addIncompatibility($dep['name'], $dep['version']);
        }
        $item->setProperty('component', $this);

        return $item;
    }

    protected function getInstallAction()
    {
        if ($this->isInstalled()) {
            if (!$this->isEnabled()) {
                return Resolver::ACTION_REMOVE;
            }
            if ($this->isUpgraded()) {
                return Resolver::ACTION_NONE;
            }

            return Resolver::ACTION_UPGRADE;
        }
        if ($this->isEnabled()) {
            return Resolver::ACTION_INSTALL;
        }

        return Resolver::ACTION_NONE;
    }

    protected function getConfigureAction()
    {
        return Resolver::ACTION_NONE;
    }

    public function checkJelixVersion($jelixVersion)
    {
        return VersionComparator::compareVersionRange($jelixVersion, $this->jelixMinVersion.' - '.$this->jelixMaxVersion);
    }

    public function checkVersion($min, $max)
    {
        if ($max == '*') {
            return VersionComparator::compareVersionRange($this->moduleInfos->version, '>='.$min);
        }

        return VersionComparator::compareVersionRange($this->moduleInfos->version, $min.' - '.$max);
    }
}
