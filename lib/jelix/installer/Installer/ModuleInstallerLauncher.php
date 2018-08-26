<?php
/**
* @author      Laurent Jouanneau
* @copyright   2008-2018 Laurent Jouanneau
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
 * @since 1.7
 */
class ModuleInstallerLauncher {

    /**
     *  @var string  name of the module
     */
    protected $name = '';

    /**
     * @var string version of the current sources of the module
     */
    protected $sourceVersion = '';

    /**
     * @var string the date of the current sources of the module
     */
    protected $sourceDate = '';

    /**
     * @var string the namespace of the xml file
     */
    protected $identityNamespace = 'http://jelix.org/ns/module/1.0';

    /**
     * @var string the expected name of the root element in the xml file
     */
    protected $rootName = 'module';

    /**
     * @var string the name of the xml file
     */
    protected $identityFile = 'module.xml';

    /**
     * @var GlobalSetup
     */
    protected $globalSetup = null;

    /**
     * list of dependencies of the module
     */
    protected $dependencies = array();

    /**
     * list of incompatibilities of the module
     */
    protected $incompatibilities = array();

    /**
     * @var string the minimum version of jelix for which the component is compatible
     */
    protected $jelixMinVersion = '*';

    /**
     * @var string the maximum version of jelix for which the component is compatible
     */
    protected $jelixMaxVersion = '*';

    /**
     * code error of the installation
     */
    public $inError = 0;

    /**
     *
     * @var ModuleStatus
     */
    protected $moduleInfos = null;

    /**
     * @var Module\Configurator
     */
    protected $moduleConfigurator = null;

    /**
     * @var Module\Installer|\jInstallerModule
     */
    protected $moduleInstaller = null;

    /**
     * @var Module\Uninstaller|\jInstallerModule
     */
    protected $moduleUninstaller = null;

    /**
     * @var Module\Installer[]|\jInstallerModule[]
     */
    protected $moduleUpgraders = null;

    /**
     * @var Module\Installer|\jInstallerModule
     */
    protected $moduleMainUpgrader = null;

    protected $upgradersContexts = array();

    /**
     * @param ModuleStatus $moduleInfos
     * @param GlobalSetup $globalSetup
     */
    function __construct(ModuleStatus $moduleInfos, GlobalSetup $globalSetup) {
        $this->globalSetup = $globalSetup;
        $this->moduleInfos = $moduleInfos;
        $this->name = $moduleInfos->getName();
    }

    public function getName() { return $this->name; }
    public function getPath() { return $this->moduleInfos->getPath(); }
    public function getSourceVersion() { return $this->sourceVersion; }
    public function getSourceDate() { return $this->sourceDate; }
    public function getJelixVersion() { return array($this->jelixMinVersion, $this->jelixMaxVersion);}

    public function getDependencies() {
        return $this->dependencies;
    }

    public function getIncompatibilities() {
        return $this->incompatibilities;
    }

    public function isEnabled() {
        return $this->moduleInfos->isEnabled;
    }

    public function isInstalled() {
        return $this->moduleInfos->isInstalled;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isUpgraded() {
        if (!$this->isInstalled()) {
            return false;
        }
        if ($this->moduleInfos->version == '') {
            throw new Exception("installer.ini.missing.version", array($this->name));
        }
        return VersionComparator::compareVersion($this->sourceVersion, $this->moduleInfos->version) == 0;
    }

    public function getInstalledVersion() {
        return $this->moduleInfos->version;
    }

    public function setInstalledVersion($version) {
        $this->moduleInfos->version = $version;
    }

    /**
     * Set installation parameters into module infos
     * @param string[] $parameters
     */
    public function setInstallParameters($parameters) {
        $this->moduleInfos->parameters = $parameters;
    }

    /**
     * save module infos into the app config or the localconfig
     */
    public function saveModuleInfos() {

        if ($this->moduleInfos->configurationScope == ModuleStatus::CONFIG_SCOPE_LOCAL) {
            $conf = $this->globalSetup->getLocalConfigIni();
        }
        else {
            $this->moduleInfos->clearInfos($this->globalSetup->getConfigIni()['local']);
            $conf = $this->globalSetup->getConfigIni()['main'];
        }
        $this->moduleInfos->saveInfos($conf);
    }

    /**
     * @return string[]
     */
    public function getInstallParameters() {
        return $this->moduleInfos->parameters;
    }

    /**
     * Backup the uninstall.php outside the module
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
    public function backupUninstallScript() {
        $targetPath = \jApp::appPath('install/uninstall/'.$this->moduleInfos->getName());
        \jFile::createDir($targetPath);
        copy($this->moduleInfos->getPath().'module.xml', $targetPath);
        $uninstallerIni = $this->globalSetup->getUninstallerIni();
        $this->moduleInfos->saveInfos($uninstallerIni);

        if (file_exists($this->moduleInfos->getPath().'install/uninstall.php')) {
            \jFile::createDir($targetPath.'/install');
            copy($this->moduleInfos->getPath().'install/uninstall.php',
                $targetPath.'/install');
            return true;
        }
        return false;
    }

    public function hasUninstallScript() {
        return file_exists($this->moduleInfos->getPath().'install/uninstall.php');
    }

    /**
     * instancies the object which is responsible to configure the module
     *
     * @param bool $actionMode  true to configure, false to unconfigure
     * @param bool $forLocalConfiguration  true if the configuration should be done
     *             with the local configuration, else it will be done with the
     *             main configuration
     * @return Module\Configurator|null the configurator, or null
     *          if there isn't any configurator
     * @throws Exception when configurator class not found
     */
    function getConfigurator($actionMode = true, $forLocalConfiguration = null) {

        $this->moduleInfos->isEnabled = $actionMode;

        if ($actionMode) {
            $uninstallerIni = $this->globalSetup->getUninstallerIni();
            $this->moduleInfos->clearInfos($uninstallerIni);
        }

        // false means that there isn't an installer for the module
        if ($this->moduleConfigurator === false) {
            return null;
        }

        if ($this->moduleConfigurator === null) {
            if (!file_exists($this->moduleInfos->getPath().'install/configure.php') ||
                $this->moduleInfos->skipInstaller
            ) {
                $this->moduleConfigurator = false;
                return null;
            }

            require_once($this->moduleInfos->getPath().'install/configure.php');

            $cname = $this->name.'ModuleConfigurator';
            if (!class_exists($cname)) {
                throw new Exception("module.configurator.class.not.found", array($cname, $this->name));
            }

            if ($forLocalConfiguration === null) {
                $forLocalConfiguration = $this->moduleInfos->configurationScope;
            }
            else {
                $this->moduleInfos->configurationScope = $forLocalConfiguration;
            }


            $this->moduleConfigurator = new $cname($this->name,
                $this->name,
                $this->moduleInfos->getPath(),
                $this->sourceVersion,
                $forLocalConfiguration
            );
            $this->moduleConfigurator->setGlobalSetup($this->globalSetup);
        }
        return $this->moduleConfigurator;
    }

    /**
     * instancies the object which is responsible to install the module
     *
     * @return \jIInstallerComponent|Module\InstallerInterface|null the installer, or null
     *          if there isn't any installer
     * @throws Exception when install class not found
     */
    function getInstaller() {

        // false means that there isn't an installer for the module
        if ($this->moduleInstaller === false) {
            return null;
        }

        if ($this->moduleInstaller === null) {
            if (!file_exists($this->moduleInfos->getPath().'install/install.php') ||
                $this->moduleInfos->skipInstaller
            ) {
                $this->moduleInstaller = false;
                return null;
            }

            require_once($this->moduleInfos->getPath().'install/install.php');

            $cname = $this->name.'ModuleInstaller';
            if (!class_exists($cname)) {
                throw new Exception("module.installer.class.not.found", array($cname, $this->name));
            }

            $this->moduleInstaller = new $cname($this->name,
                                                $this->name,
                                                $this->moduleInfos->getPath(),
                                                $this->sourceVersion,
                                                true
                                                );
            if ($this->moduleInstaller instanceof \Jelix\Installer\Module\InstallerInterface) {
                $this->moduleInstaller->setGlobalSetup($this->globalSetup);
            }
        }

        if ($this->moduleInstaller instanceof \jIInstallerComponent) {
            $this->moduleInstaller->setContext($this->globalSetup->getInstallerContexts($this->name));
            $mainEntryPoint = $this->globalSetup->getMainEntryPoint();
            if (!$mainEntryPoint->legacyInstallerEntryPoint) {
                $mainEntryPoint->legacyInstallerEntryPoint = new \jInstallerEntryPoint($mainEntryPoint, $this->globalSetup);
            }
            $this->moduleInstaller->setEntryPoint($mainEntryPoint->legacyInstallerEntryPoint,
                $this->moduleInfos->dbProfile);
        }
        else {
            $this->moduleInstaller->initDbProfile($this->moduleInfos->dbProfile);
        }
        $this->moduleInstaller->setParameters($this->moduleInfos->parameters);

        return $this->moduleInstaller;
    }

    /**
     * instancies the object which is responsible to uninstall the module
     *
     * @return \jIInstallerComponent|Module\UninstallerInterface|null the uninstaller, or null
     *          if there isn't any uninstaller
     * @throws Exception when install class not found
     */
    function getUninstaller() {

        // false means that there isn't an installer for the module
        if ($this->moduleUninstaller === false) {
            return null;
        }

        if ($this->moduleUninstaller === null) {

            if ($this->moduleInfos->skipInstaller) {
                $this->moduleUninstaller = false;
                return null;
            }

            $installer = $this->getInstaller();
            if ($installer && $installer instanceof \jIInstallerComponent) {
                $this->moduleUninstaller = $installer;
                $this->moduleUninstaller->setParameters($this->moduleInfos->parameters);
                return $this->moduleUninstaller;
            }

            if (!file_exists($this->moduleInfos->getPath().'install/uninstall.php')) {
                $this->moduleUninstaller = false;
                return null;
            }

            require_once($this->moduleInfos->getPath().'install/uninstall.php');

            $cname = $this->name.'ModuleUninstaller';
            if (!class_exists($cname)) {
                throw new Exception("module.uninstaller.class.not.found", array($cname, $this->name));
            }

            $this->moduleUninstaller = new $cname($this->name,
                $this->name,
                $this->moduleInfos->getPath(),
                $this->sourceVersion,
                true
            );
            $this->moduleUninstaller->setGlobalSetup($this->globalSetup);
        }

        $this->moduleUninstaller->initDbProfile($this->moduleInfos->dbProfile);
        $this->moduleUninstaller->setParameters($this->moduleInfos->parameters);
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
     * @return \jIInstallerComponent[]|Module\InstallerInterface[]
     * @throws Exception  if an error occurs during the install.
     */
    function getUpgraders() {

        if ($this->moduleMainUpgrader === null) {
            if (!file_exists($this->moduleInfos->getPath() . 'install/upgrade.php') ||
                $this->moduleInfos->skipInstaller
            ) {
                $this->moduleMainUpgrader = false;
            }
            else {
                require_once($this->moduleInfos->getPath().'install/upgrade.php');

                $cname = $this->name.'ModuleUpgrader';
                if (!class_exists($cname)) {
                    throw new Exception("module.upgrader.class.not.found", array($cname, $this->name));
                }

                $this->moduleMainUpgrader = new $cname($this->name,
                    $this->name,
                    $this->moduleInfos->getPath(),
                    $this->sourceVersion,
                    false
                );

                $this->moduleMainUpgrader->setTargetVersions(array($this->sourceVersion));

                if ($this->moduleMainUpgrader instanceof \Jelix\Installer\Module\InstallerInterface) {
                    $this->moduleMainUpgrader->setGlobalSetup($this->globalSetup);
                }
            }
        }

        if ($this->moduleUpgraders === null) {

            $this->moduleUpgraders = array();

            $p = $this->moduleInfos->getPath().'install/';
            if (!file_exists($p)  || $this->moduleInfos->skipInstaller) {
                return array();
            }

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

            // now we order the list of file
            foreach($fileList as $fileInfo) {
                require_once($p.$fileInfo[0]);
                $cname = $this->name.'ModuleUpgrader_'.$fileInfo[2];
                if (!class_exists($cname))
                    throw new Exception("module.upgrader.class.not.found",array($cname,$this->name));

                $upgrader = new $cname($this->name,
                                        $fileInfo[2],
                                        $this->moduleInfos->getPath(),
                                        $fileInfo[1],
                                        false);

                if ($fileInfo[1] && count($upgrader->getTargetVersions()) == 0) {
                    $upgrader->setTargetVersions(array($fileInfo[1]));
                }
                if (count($upgrader->getTargetVersions()) == 0) {
                    throw new Exception("module.upgrader.missing.version",array($fileInfo[0], $this->name));
                }
                $this->moduleUpgraders[] = $upgrader;
                if ($upgrader instanceof \Jelix\Installer\Module\InstallerInterface) {
                    $upgrader->setGlobalSetup($this->globalSetup);
                }
            }
        }

        if ((count($this->moduleUpgraders) || $this->moduleMainUpgrader) && $this->moduleInfos->version == '') {
            throw new Exception("installer.ini.missing.version", array($this->name));
        }

        $list = array();

        foreach($this->moduleUpgraders as $upgrader) {

            $foundVersion = '';
            // check the version
            foreach($upgrader->getTargetVersions() as $version) {
                if (VersionComparator::compareVersion($this->moduleInfos->version, $version) >= 0 ) {
                    // we don't execute upgraders having a version lower than the installed version (they are old upgrader)
                    continue;
                }
                if (VersionComparator::compareVersion($this->sourceVersion, $version) < 0 ) {
                    // we don't execute upgraders having a version higher than the version indicated in the module.xml
                    continue;
                }
                $foundVersion = $version;
                // when multiple version are specified, we take the first one which is ok
                break;
            }
            if (!$foundVersion)
                continue;

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
                    if ($firstVersionDate >= $upgraderDate)
                        continue;
                }

                // the date of the current installed version
                $currentVersionDate = $this->_formatDate($this->globalSetup->getInstallerIni()
                    ->getValue($this->name.'.version.date', 'modules'));
                if ($currentVersionDate !== null) {
                    if ($currentVersionDate >= $upgraderDate)
                        continue;
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
                $upgrader->setEntryPoint($mainEntryPoint->legacyInstallerEntryPoint,
                                            $this->moduleInfos->dbProfile);
            }
            else {
                $upgrader->initDbProfile($this->moduleInfos->dbProfile);
            }
            $upgrader->setParameters($this->moduleInfos->parameters);
            $list[] = $upgrader;
        }

        // now let's sort upgrader, to execute them in the right order (oldest before newest)
        usort($list, function ($upgA, $upgB) {
                return VersionComparator::compareVersion($upgA->getVersion(), $upgB->getVersion());
        });

        if ($this->moduleMainUpgrader && VersionComparator::compareVersion($this->moduleInfos->version, $this->sourceVersion) < 0 ) {
            $list[] = $this->moduleMainUpgrader;
        }
        return $list;
    }

    public function installFinished() {
        if ($this->moduleInstaller instanceof \jIInstallerComponent) {
            $this->globalSetup->updateInstallerContexts($this->name, $this->moduleInstaller->getContexts());
        }
        else {
            // remove legacy contexts
            $this->globalSetup->removeInstallerContexts($this->name);
        }
    }

    public function upgradeFinished($upgrader) {
        if ($upgrader instanceof \jIInstallerComponent) {
            $class = get_class($upgrader);
            $this->upgradersContexts[$class] = $upgrader->getContexts();
        }
    }

    public function uninstallFinished() {
        $this->globalSetup->removeInstallerContexts($this->name);
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
     * @return Item
     */
    public function getResolverItem() {
        $action = $this->getInstallAction();
        if ($action == Resolver::ACTION_UPGRADE) {
            $item = new Item($this->name, $this->sourceVersion, true);
            $item->setAction(Resolver::ACTION_UPGRADE, $this->moduleInfos->version);
        }
        else {
            $item = new Item($this->name, $this->sourceVersion, $this->isInstalled());
            $item->setAction($action);
        }

        foreach($this->dependencies as $dep) {
            if ($dep['type'] == 'choice') {
                $list = array();
                foreach($dep['choice'] as $choice) {
                    $list[$choice['name']] = $choice['version'];
                }
                $item->addAlternativeDependencies($list);
            }
            else {
                $item->addDependency($dep['name'], $dep['version']);
            }
        }

        foreach($this->incompatibilities as $dep) {
            $item->addIncompatibility($dep['name'], $dep['version']);
        }
        $item->setProperty('component', $this);
        return $item;
    }

    protected function getInstallAction() {
        if ($this->isInstalled()) {
            if (!$this->isEnabled()) {
                return Resolver::ACTION_REMOVE;
            }
            elseif ($this->isUpgraded()) {
                return Resolver::ACTION_NONE;
            }
            return Resolver::ACTION_UPGRADE;
        }
        elseif ($this->isEnabled()) {
            return Resolver::ACTION_INSTALL;
        }
        return Resolver::ACTION_NONE;
    }

    /**
     * read the identity file
     * @throws \Exception
     */
    protected function readIdentity() {
        $xmlDescriptor = new \DOMDocument();

        if(!$xmlDescriptor->load($this->moduleInfos->getPath().$this->identityFile)){
            throw new Exception('install.invalid.xml.file',array($this->moduleInfos->getPath().$this->identityFile));
        }

        $root = $xmlDescriptor->documentElement;

        if ($root->namespaceURI == $this->identityNamespace) {
            $xml = simplexml_import_dom($xmlDescriptor);
            if (!isset($xml->info[0]->version[0])) {
                throw new Exception('module.missing.version', array($this->name));
            }
            $this->sourceVersion = $this->fixVersion((string) $xml->info[0]->version[0]);
            if (trim($this->sourceVersion) == '') {
                throw new Exception('module.missing.version', array($this->name));
            }
            if (isset($xml->info[0]->version['date'])) {
                $this->sourceDate = (string)$xml->info[0]->version['date'];
                if ($this->sourceDate == '__TODAY__') { // for non-packages modules
                    $this->sourceDate = date('Y-m-d');
                }
            } else {
                $this->sourceDate = '';
            }
            $this->readDependencies($xml);
        }
    }

    protected function readDependencies($xml) {

        /*
  <module xmlns="http://jelix.org/ns/module/1.0">
      <info id="jelix@modules.jelix.org" name="jelix" createdate="">
          <version stability="stable" date="">1.0</version>
          <label lang="en_US" locale="">Jelix Main Module</label>
          <description lang="en_US" locale="" type="text/xhtml">Main module of jelix which contains some ressources needed by jelix classes</description>
          <license URL="http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html">LGPL 2.1</license>
          <copyright>2005-2008 Laurent Jouanneau and other contributors</copyright>
          <creator name="Laurent Jouanneau" nickname="" email=""/>
          <contributor name="hisname" email="hisemail@yoursite.undefined" since="" role=""/>
          <homepageURL>http://jelix.org</homepageURL>
          <updateURL>http://jelix.org</updateURL>
      </info>
      <dependencies>
          <jelix minversion="1.0" maxversion="1.0" edition="dev/opt/gold"/>
          <module id="" name="" minversion="" maxversion="" />
          <choice>
             <modules>
                <module id="" name="" minversion="" maxversion="" />
                <module id="" name="" minversion="" maxversion="" />
             </modules>
             <module id="" name="" minversion="" maxversion="" />
          </choice>
          <conflict>
                <module id="" name="" minversion="" maxversion="" />
          </conflict>
      </dependencies>
  </module>
        */

        $this->dependencies = array();
        $this->incompatibilities = array();

        if (isset($xml->dependencies)) {
            foreach ($xml->dependencies->children() as $type=>$dependency) {
                if ($type == 'conflict') {
                    foreach ($dependency->children() as $type2=>$component) {
                        if ($type2 == 'module') {
                            $info2 = $this->readComponentDependencyInfo($type2, $component);
                            $info2['forbiddenby'] = $this->name;
                            $this->incompatibilities[] = $info2;
                        }
                    }
                    continue;
                }

                if ($type == 'choice') {
                    $choice = array();
                    foreach ($dependency->children() as $type2=>$component) {
                        if ($type2 == 'module') {
                            $choice[] = $this->readComponentDependencyInfo($type2, $component);
                        }
                    }
                    if (count($choice) > 1) {
                        $this->dependencies[] = array(
                            'type'=> 'choice',
                            'choice' => $choice
                        );
                    }
                    else if (count($choice) == 1) {
                        $this->dependencies[] = $choice[0];
                    }
                    continue;
                }

                if ($type != 'jelix' && $type != 'module') {
                    continue;
                }

                $info = $this->readComponentDependencyInfo($type, $dependency);

                if ($type == 'jelix' || ($type == 'module' && $info['name'] == 'jelix')) {
                    $this->jelixMinVersion = $info['minversion'];
                    $this->jelixMaxVersion = $info['maxversion'];
                    if ($this->name != 'jelix') {
                        $this->dependencies[] = array(
                            'type'=> 'module',
                            'id' => 'jelix@jelix.org',
                            'name' => 'jelix',
                            'minversion' => $this->jelixMinVersion,
                            'maxversion' => $this->jelixMaxVersion,
                            'version' => $info['version']
                        );
                    }
                }
                else if ($type == 'module') {
                    $this->dependencies[] = $info;
                }
            }
        }
    }

    /**
     * @param string $type
     * @param \SimpleXMLElement $comp
     * @return array
     * @throws Exception
     */
    protected function readComponentDependencyInfo($type, $comp)
    {
        $versionRange = '';
        $minversion = isset($comp['minversion'])?
            $this->fixVersion((string)$comp['minversion']):
            '0';
        if (trim($minversion) == '') {
            $minversion = '0';
        }
        if ($minversion != '0') {
            $versionRange = '>='.$minversion;
        }
        $maxversion = isset($comp['maxversion'])?
            $this->fixVersion((string)$comp['maxversion']):
            '*';
        if (trim($maxversion) == '') {
            $maxversion = '*';
        }
        if ($maxversion != '*') {
            $v = '<='.$maxversion;
            if ($versionRange != '') {
                $v = ','.$v;
            }
            $versionRange .= $v;
        }

        if ($versionRange == '') {
            $versionRange = '*';
        }


        $name = (string)$comp['name'];
        if (trim($name) == '' && $type != 'jelix') {
            throw new Exception('Name is missing for "'.$type.'" in a dependency declaration in module '.$this->name);
        }
        $id = isset($comp['id'])?(string)$comp['id']: '';

        return array(
            'type'=> $type,
            'id' => $id,
            'name' => $name,
            'minversion' => $minversion,
            'maxversion' => $maxversion,
            'version' => $versionRange
        );
    }


    public function checkJelixVersion ($jelixVersion) {
        return VersionComparator::compareVersionRange($jelixVersion, $this->jelixMinVersion.' - '.$this->jelixMaxVersion);
    }

    public function checkVersion($min, $max) {
        if ($max == '*') {
            return VersionComparator::compareVersionRange($this->sourceVersion, '>='.$min);
        }
        return VersionComparator::compareVersionRange($this->sourceVersion, $min.' - '.$max);
    }

    /**
     * Fix version for non built lib
     */
    protected function fixVersion($version) {
        switch($version) {
            case '__LIB_VERSION_MAX__':
                return \jFramework::versionMax();
            case '__LIB_VERSION__':
                return \jFramework::version();
            case '__VERSION__':
                return \jApp::version();
        }
        return trim($version);
    }
}
