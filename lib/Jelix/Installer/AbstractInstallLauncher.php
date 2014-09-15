<?php
/**
* @author      Laurent Jouanneau
* @copyright   2008-2014 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer;

/**
* a class to install a component (module or plugin)
* @since 1.2
*/
abstract class AbstractInstallLauncher {

    /**
     * @var jInstaller the main installer controller
     */
    protected $mainInstaller = null;

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
     * @param \Jelix\Core\Infos\ModuleInfos $moduleInfos
     * @param jInstaller $mainInstaller
     */
    function __construct($moduleInfos, $mainInstaller) {
        $this->moduleInfos = $moduleInfos;
        $this->mainInstaller = $mainInstaller;
    }

    public function getName() { return $this->moduleInfos->name; }
    public function getPath() { return $this->moduleInfos->getPath(); }
    public function getSourceVersion() { return $this->moduleInfos->version; }
    public function getSourceDate() { return $this->moduleInfos->versionDate; }

    /**
     * list of dependencies of the module
     */
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
        return ($this->isInstalled($epId) &&
                (\jVersionComparator::compareVersion($this->moduleInfos->version, $this->moduleStatuses[$epId]->version) == 0));
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

    /**
     * get the object which is responsible to install the component. this
     * object should implement InstallerInterface.
     *
     * @param jInstallerEntryPoint $ep the entry point
     * @param boolean $installWholeApp true if the installation is done during app installation
     * @return InstallerInterface the installer, or null if there isn't any installer
     *         or false if the installer is useless for the given parameter
     */
    abstract function getInstaller($ep, $installWholeApp);

    /**
     * return the list of objects which are responsible to upgrade the component
     * from the current installed version of the component.
     *
     * this method should be called after verifying and resolving
     * dependencies. Needed components (modules or plugins) should be
     * installed/upgraded before calling this method
     *
     * @param jInstallerEntryPoint $ep the entry point
     * @throw \Jelix\Installer\Exception  if an error occurs during the install.
     * @return InstallerInterface[]
     */
    abstract function getUpgraders($ep);

    public function installFinished($ep) { }

    public function upgradeFinished($ep, $upgrader) { }

    public function checkVersion($min, $max) {
        return (\jVersionComparator::compareVersion($min, $this->moduleInfos->version) <= 0 &&
                \jVersionComparator::compareVersion($this->moduleInfos->version, $max) <= 0);
    }
}

