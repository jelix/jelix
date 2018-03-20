<?php
/**
* @author      Laurent Jouanneau
* @copyright   2008-2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer;


use Jelix\Version\VersionComparator;
use \Jelix\Dependencies\Resolver;
use \Jelix\Dependencies\Item;

/**
 * Manage status of a module and its installer/updaters
 *
 * @since 1.2
 */
class ModuleInstallLauncher {

    /**
     * @var GlobalSetup
     */
    protected $globalSetup = null;

    /**
     * code error of the installation
     */
    public $inError = 0;

    /**
     * list of installation information about the module for each entry points
     * @var array  key = epid, value = ModuleStatus
     */
    protected $moduleStatuses = array();

    /**
     * @var \Jelix\Core\Infos\ModuleInfos
     */
    protected $moduleInfos = null;

    /**
     * @var ModuleInstaller
     */
    protected $moduleInstaller = null;

    /**
     * @var ModuleInstaller[]
     */
    protected $moduleUpgraders = null;

    protected $upgradersContexts = array();

    /**
     * @param \Jelix\Core\Infos\ModuleInfos $moduleInfos
     * @param GlobalSetup $globalSetup
     */
    function __construct(\Jelix\Core\Infos\ModuleInfos $moduleInfos, GlobalSetup $globalSetup = null) {
        $this->moduleInfos = $moduleInfos;
        $this->globalSetup = $globalSetup;
    }

    public function getName() { return $this->moduleInfos->name; }
    public function getPath() { return $this->moduleInfos->getPath(); }
    public function getSourceVersion() { return $this->moduleInfos->version; }
    public function getSourceDate() { return $this->moduleInfos->versionDate; }

    public function getDependencies() {
        return $this->moduleInfos->dependencies;
    }

    /**
     * @param string $epId the id of the entrypoint
     * @param ModuleStatus $moduleStatus module status
     */
    public function addModuleStatus ($epId, $moduleStatus) {
        $this->moduleStatuses[$epId] = $moduleStatus;
    }

    public function getAccessLevel($epId) {
        return $this->moduleStatuses[$epId]->access;
    }

    public function isInstalled($epId) {
        return $this->moduleStatuses[$epId]->isInstalled;
    }

    public function isUpgraded($epId) {
        if (!$this->isInstalled($epId)) {
            return false;
        }
        if ($this->moduleStatuses[$epId]->version == '') {
            throw new Exception("installer.ini.missing.version", array($this->name));
        }
        return VersionComparator::compareVersion($this->moduleInfos->version, $this->moduleStatuses[$epId]->version) == 0;
    }

    public function isActivated($epId) {
        $access = $this->moduleStatuses[$epId]->access;
        return ($access == 1 || $access ==2);
    }

    public function getInstalledVersion($epId) {
        return $this->moduleStatuses[$epId]->version;
    }

    public function setInstalledVersion($epId, $version) {
        $this->moduleStatuses[$epId]->version = $version;
    }

    public function setInstallParameters($epId, $parameters) {
        $this->moduleStatuses[$epId]->parameters = $parameters;
    }

    public function getInstallParameters($epId) {
        return $this->moduleStatuses[$epId]->parameters;
    }

    protected function _setAccess(EntryPoint $ep)
    {
        $config = $ep->getConfigIni();
        $access = $config->getValue($this->moduleInfos->name . '.access', 'modules');

        $action = $this->getInstallAction($ep->getEpId());
        if ($action == Resolver::ACTION_INSTALL) {
            if ($access == 0 || $access == null) {
                $config->setValue($this->moduleInfos->name . '.access', 2, 'modules');
                $config->save();
            } else if ($access == 3) {
                $config->setValue($this->moduleInfos->name . '.access', 1, 'modules');
                $config->save();
            }
        }
        else if ($action == Resolver::ACTION_REMOVE) {
            $config->setValue($this->moduleInfos->name . '.access', 0, 'modules');
            $config->save();
        }
    }

    /**
     * instancies the object which is responsible to install the module
     *
     * @param EntryPoint $ep the entry point
     * @param boolean $installWholeApp true if the installation is done during app installation
     * @return ModuleInstaller the installer, or null if there isn't any installer
     * @throws Exception when install class not found
     */
    function getInstaller(EntryPoint $ep, $installWholeApp) {

        $this->_setAccess($ep);

        // false means that there isn't an installer for the module
        if ($this->moduleInstaller === false) {
            return null;
        }

        $epId = $ep->getEpId();

        if ($this->moduleInstaller === null) {
            if (!file_exists($this->moduleInfos->getPath().'install/install.php') ||
                             $this->moduleStatuses[$epId]->skipInstaller) {
                $this->moduleInstaller = false;
                return null;
            }

            require_once($this->moduleInfos->getPath().'install/install.php');
            $cname = $this->moduleInfos->name.'ModuleInstaller';
            if (!class_exists($cname))
                throw new Exception("module.installer.class.not.found",array($cname,$this->moduleInfos->name));
            $this->moduleInstaller = new $cname($this->moduleInfos->name,
                                                $this->moduleInfos->name,
                                                $this->moduleInfos->getPath(),
                                                $this->moduleInfos->version,
                                                $installWholeApp
                                                );
            $this->moduleInstaller->setGlobalSetup($this->globalSetup);
        }

        $this->moduleInstaller->setContext($this->globalSetup->getInstallerContexts($this->moduleInfos->name));
        return $this->moduleInstaller;
    }

    public function setAsCurrentModuleInstaller(EntryPoint $ep) {
        if (!$this->moduleInstaller) {
            return;
        }
        $epId = $ep->getEpId();
        $this->moduleInstaller->setParameters($this->moduleStatuses[$epId]->parameters);
        $sparam = $ep->getLocalConfigIni()->getValue($this->moduleInfos->name.'.installparam','modules');
        if ($sparam === null) {
            $sparam = '';
        }

        $sp = $this->moduleStatuses[$epId]->serializeParameters();
        if ($sparam != $sp) {
            $ep->getLocalConfigIni()->setValue($this->moduleInfos->name.'.installparam', $sp, 'modules');
        }

        $ep->_setCurrentModuleInstaller($this->moduleInstaller);
        $this->moduleInstaller->initDbProfileForEntrypoint($this->moduleStatuses[$epId]->dbProfile);
    }


    /**
     * return the list of objects which are responsible to upgrade the module
     * from the current installed version of the module.
     *
     * this method should be called after verifying and resolving
     * dependencies. Needed modules should be
     * installed/upgraded before calling this method
     *
     * @param EntryPoint $ep the entry point
     * @throw \Jelix\Installer\Exception  if an error occurs during the install.
     * @return ModuleInstaller[]
     */
    function getUpgraders(EntryPoint $ep) {

        $epId = $ep->getEpId();

        if ($this->moduleUpgraders === null) {

            $this->moduleUpgraders = array();

            $p = $this->moduleInfos->getPath().'install/';
            if (!file_exists($p)  || $this->moduleStatuses[$epId]->skipInstaller)
                return array();

            // we get the list of files for the upgrade
            $fileList = array();
            if ($handle = opendir($p)) {
                while (false !== ($f = readdir($handle))) {
                    if (!is_dir($p.$f)) {
                        if (preg_match('/^upgrade_to_([^_]+)_([^\.]+)\.php$/', $f, $m)) {
                            $fileList[] = array($f, $m[1], $m[2]);
                        }
                        else if (preg_match('/^upgrade_([^\.]+)\.php$/', $f, $m)){
                            $fileList[] = array($f, '', $m[1]);
                        }
                    }
                }
                closedir($handle);
            }

            if (!count($fileList)) {
                return array();
            }

            // now we order the list of file
            foreach($fileList as $fileInfo) {
                require_once($p.$fileInfo[0]);
                $cname = $this->moduleInfos->name.'ModuleUpgrader_'.$fileInfo[2];
                if (!class_exists($cname))
                    throw new Exception("module.upgrader.class.not.found",array($cname,$this->moduleInfos->name));

                $upgrader = new $cname($this->moduleInfos->name,
                                        $fileInfo[2],
                                        $this->moduleInfos->getPath(),
                                        $fileInfo[1],
                                        false);

                if ($fileInfo[1] && count($upgrader->targetVersions) == 0) {
                    $upgrader->targetVersions = array($fileInfo[1]);
                }
                if (count($upgrader->targetVersions) == 0) {
                    throw new Exception("module.upgrader.missing.version",array($fileInfo[0], $this->moduleInfos->name));
                }
                $this->moduleUpgraders[] = $upgrader;
                $upgrader->setGlobalSetup($this->globalSetup);
            }
        }

        if (count($this->moduleUpgraders) && $this->moduleStatuses[$epId]->version == '') {
            throw new Exception("installer.ini.missing.version", array($this->moduleInfos->name));
        }

        $list = array();
        foreach($this->moduleUpgraders as $upgrader) {

            $foundVersion = '';
            // check the version
            foreach($upgrader->targetVersions as $version) {

                if (VersionComparator::compareVersion($this->moduleStatuses[$epId]->version, $version) >= 0 ) {
                    // we don't execute upgraders having a version lower than the installed version (they are old upgrader)
                    continue;
                }
                if (VersionComparator::compareVersion($this->moduleInfos->version, $version) < 0 ) {
                    // we don't execute upgraders having a version higher than the version indicated in the module.xml/jelix-module.json
                    continue;
                }
                $foundVersion = $version;
                // when multiple version are specified, we take the first one which is ok
                break;
            }
            if (!$foundVersion)
                continue;

            $upgrader->version = $foundVersion;

            // we have to check now the date of versions
            // we should not execute the updater in some case.
            // for example, we have an updater for the 1.2 and 2.3 version
            // we have the 1.4 installed, and want to upgrade to the 2.5 version
            // we should not execute the update for 2.3 since modifications have already been
            // made into the 1.4. The only way to now that, is to compare date of versions
            if ($upgrader->date != '' && $this->globalSetup) {
                $upgraderDate = $this->_formatDate($upgrader->date);

                // the date of the first version installed into the application
                $firstVersionDate = $this->_formatDate($this->globalSetup->getInstallerIni()->getValue($this->moduleInfos->name.'.firstversion.date', $epId));
                if ($firstVersionDate !== null) {
                    if ($firstVersionDate >= $upgraderDate)
                        continue;
                }

                // the date of the current installed version
                $currentVersionDate = $this->_formatDate($this->globalSetup->getInstallerIni()->getValue($this->moduleInfos->name.'.version.date', $epId));
                if ($currentVersionDate !== null) {
                    if ($currentVersionDate >= $upgraderDate)
                        continue;
                }
            }

            $class = get_class($upgrader);
            if (!isset($this->upgradersContexts[$class])) {
                $this->upgradersContexts[$class] = array();
            }
            $upgrader->setContext($this->upgradersContexts[$class]);

            $list[] = $upgrader;
        }
        // now let's sort upgrader, to execute them in the right order (oldest before newest)
        usort($list, function ($upgA, $upgB) {
                return VersionComparator::compareVersion($upgA->version, $upgB->version);
        });
        return $list;
    }

    public function setAsCurrentModuleUpgrader(ModuleInstaller $upgrader, EntryPoint $ep) {
        $epId = $ep->getEpId();
        $upgrader->setParameters($this->moduleInfos[$epId]->parameters);
        $ep->_setCurrentModuleInstaller($upgrader);
        $upgrader->initDbProfileForEntrypoint($this->moduleStatuses[$epId]->dbProfile);
    }

    public function installEntryPointFinished(EntryPoint $ep) {
        if ($this->globalSetup)
            $this->globalSetup->updateInstallerContexts($this->moduleInfos->name, $this->moduleInstaller->getContexts());
    }

    public function upgradeEntryPointFinished(EntryPoint $ep, ModuleInstaller $upgrader) {
        $class = get_class($upgrader);
        $this->upgradersContexts[$class] = $upgrader->getContexts();
    }

    public function uninstallEntryPointFinished(EntryPoint $ep) {
        if ($this->globalSetup)
            $this->globalSetup->removeInstallerContexts($this->moduleInfos->name);
    }

    protected function _formatDate($date) {
        if ($date !== null) {
            if (strlen($date) == 10)
                $date.=' 00:00';
            else if (strlen($date) > 16) {
                $date = substr($date, 0, 16);
            }
        }
        return $date;
    }

    /**
     * @var boolean  indicate if the identify file has already been readed
     */
    protected $identityReaded = false;

    /**
     * initialize the object, by reading the identity file
     */
    public function init () {
        if ($this->identityReaded)
            return;
        $this->identityReaded = true;
        $this->readIdentity();
    }

    /**
     * @param string $epId
     * @param bool $installedByDefault
     * @return Item
     */
    public function getResolverItem($epId) {
        $action = $this->getInstallAction($epId);
        if ($action == Resolver::ACTION_UPGRADE) {
            $item = new Item($this->moduleInfos->name, true, $this->moduleInfos->version, Resolver::ACTION_UPGRADE, $this->moduleInfos[$epId]->version);
        }
        else {
            $item = new Item($this->moduleInfos->name, $this->isInstalled($epId), $this->moduleInfos->version, $action);
        }

        foreach($this->moduleInfos->dependencies as $dep) {
            $item->addDependency($dep['name'], $dep['version']);
        }
        $item->setProperty('component', $this);
        return $item;
    }

    protected function getInstallAction($epId) {
        if ($this->isInstalled($epId)) {
            if (!$this->isActivated($epId)) {
                return Resolver::ACTION_REMOVE;
            }
            elseif ($this->isUpgraded($epId)) {
                return Resolver::ACTION_NONE;
            }
            return Resolver::ACTION_UPGRADE;
        }
        elseif ($this->isActivated($epId)) {
            return Resolver::ACTION_INSTALL;
        }
        return Resolver::ACTION_NONE;
    }
}
