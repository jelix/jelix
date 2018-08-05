<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2008-2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

use Jelix\Version\VersionComparator;
use \Jelix\Dependencies\Resolver;
use \Jelix\Dependencies\Item;

/**
 * Manage status of a module and its installer/updaters
 *
 * @package     jelix
 * @subpackage  installer
 * @since 1.2
 */
class jInstallerComponentModule {

    /**
     *  @var string  name of the module
     */
    protected $name = '';

    /**
     * @var string the path of the directory of the module
     * it should be set by the constructor
     */
    protected $path = '';

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
     * @var jInstallerGlobalSetup
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
     * list of information about the module for each entry points
     * @var array  key = epid,  value = jInstallerModuleInfos
     */
    protected $moduleInfos = array();

    /**
     * @var jInstallerModule2|jInstallerModule
     */
    protected $moduleInstaller = null;

    /**
     * @var jInstallerModule2[]|jInstallerModule[]
     */
    protected $moduleUpgraders = null;

    protected $upgradersContexts = array();

    /**
     * @param string $name the name of the component
     * @param string $path the path of the component
     * @param jInstallerGlobalSetup $globalSetup
     */
    function __construct($name, $path, jInstallerGlobalSetup $globalSetup = null) {
        $this->path = $path;
        $this->name = $name;
        $this->globalSetup = $globalSetup;
    }

    public function getName() { return $this->name; }
    public function getPath() { return $this->path; }
    public function getSourceVersion() { return $this->sourceVersion; }
    public function getSourceDate() { return $this->sourceDate; }
    public function getJelixVersion() { return array($this->jelixMinVersion, $this->jelixMaxVersion);}

    public function getDependencies() {
        return $this->dependencies;
    }

    public function getIncompatibilities() {
        return $this->incompatibilities;
    }

    /**
     * @param jInstallerModuleInfos $module module infos
     */
    public function addModuleInfos ($epId, $module) {
        $this->moduleInfos[$epId] = $module;
    }

    public function getAccessLevel($epId) {
        return $this->moduleInfos[$epId]->access;
    }

    public function isInstalled($epId) {
        return $this->moduleInfos[$epId]->isInstalled;
    }

    public function isUpgraded($epId) {
        if (!$this->isInstalled($epId)) {
            return false;
        }
        if ($this->moduleInfos[$epId]->version == '') {
            throw new jInstallerException("installer.ini.missing.version", array($this->name));
        }
        return VersionComparator::compareVersion($this->sourceVersion, $this->moduleInfos[$epId]->version) == 0;
    }

    public function isActivated($epId) {
        $access = $this->moduleInfos[$epId]->access;
        return ($access == 1 || $access ==2);
    }

    public function getInstalledVersion($epId) {
        return $this->moduleInfos[$epId]->version;
    }

    public function setInstalledVersion($epId, $version) {
        $this->moduleInfos[$epId]->version = $version;
    }

    public function setInstallParameters($epId, $parameters) {
        $this->moduleInfos[$epId]->parameters = $parameters;
    }

    public function getInstallParameters($epId) {
        return $this->moduleInfos[$epId]->parameters;
    }

    /**
     * Sets the access parameter in the right configuration file
     *
     * @param jInstallerEntryPoint2 $ep
     */
    protected function _setAccess(jInstallerEntryPoint2 $ep)
    {
        $config = $ep->getLiveConfigIni();
        $accessLocal =   $config['local']->getValue($this->name . '.access', 'modules');
        $accessMain =    $config['main']->getValue($this->name . '.access', 'modules');

        $action = $this->getInstallAction($ep->getEpId());

        if ($action == Resolver::ACTION_INSTALL) {
            if ($accessLocal == 1 ||
                $accessLocal == 2 ||
                $accessMain == 1 ||
                $accessMain == 2
            ) {
                return;
            }

            $config['main']->setValue($this->name.'.access', 2, 'modules');
            $config->save();
        }
        else if ($action == Resolver::ACTION_REMOVE) {

            if ($accessLocal !== null) {
                if ($accessLocal !== 0) {
                    $config['local']->setValue($this->name.'.access', 0, 'modules');
                    $config->save();
                }
                return;
            }

            if ($accessMain !== null) {
                if ($accessMain !== 0) {
                    $config['main']->setValue($this->name.'.access', 0, 'modules');
                    $config->save();
                }
                return;
            }
        }
    }

    /**
     * instancies the object which is responsible to install the module
     *
     * @param jInstallerEntryPoint2 $ep the entry point
     * @param boolean $installWholeApp true if the installation is done during app installation
     * @return jIInstallerComponent|jIInstallerComponent2|null|false the installer, or null
     *          if there isn't any installer
     *         or false if the installer is useless for the given parameter
     * @throws jInstallerException when install class not found
     */
    function getInstaller(jInstallerEntryPoint2 $ep, $installWholeApp) {

        $this->_setAccess($ep);

        // false means that there isn't an installer for the module
        if ($this->moduleInstaller === false) {
            return null;
        }

        $epId = $ep->getEpId();

        if ($this->moduleInstaller === null) {
            if (!file_exists($this->path.'install/install.php') || $this->moduleInfos[$epId]->skipInstaller) {
                $this->moduleInstaller = false;
                return null;
            }
            require_once($this->path.'install/install.php');
            $cname = $this->name.'ModuleInstaller';
            if (!class_exists($cname)) {
                throw new jInstallerException("module.installer.class.not.found", array($cname, $this->name));
            }
            $this->moduleInstaller = new $cname($this->name,
                                                $this->name,
                                                $this->path,
                                                $this->sourceVersion,
                                                $installWholeApp
                                                );
            if ($this->moduleInstaller instanceof jIInstallerComponent2) {
                $this->moduleInstaller->setGlobalSetup($this->globalSetup);
            }
        }

        $this->moduleInstaller->setContext($this->globalSetup->getInstallerContexts($this->name));
        return $this->moduleInstaller;
    }

    public function setAsCurrentModuleInstaller(jInstallerEntryPoint2 $ep)
    {
        if (!$this->moduleInstaller) {
            return;
        }
        $epId = $ep->getEpId();
        $this->moduleInstaller->setParameters($this->moduleInfos[$epId]->parameters);
        $sparam = $ep->getLocalConfigIni()->getValue($this->name.'.installparam','modules');
        if ($sparam === null) {
            $sparam = '';
        }

        $sp = $this->moduleInfos[$epId]->serializeParameters();
        if ($sparam != $sp) {
            $ep->getLocalConfigIni()->setValue($this->name.'.installparam', $sp, 'modules');
        }

        if ($this->moduleInstaller instanceof jIInstallerComponent) {
            $legacyEp = $ep->getLegacyInstallerEntryPoint();
            $this->moduleInstaller->setEntryPoint($legacyEp,
                $this->moduleInfos[$epId]->dbProfile);
        }
        else {
            $ep->_setCurrentModuleInstaller($this->moduleInstaller);
            $this->moduleInstaller->initDbProfileForEntrypoint($this->moduleInfos[$epId]->dbProfile);
        }

    }


    /**
     * return the list of objects which are responsible to upgrade the module
     * from the current installed version of the module.
     *
     * this method should be called after verifying and resolving
     * dependencies. Needed modules should be
     * installed/upgraded before calling this method
     *
     * @param jInstallerEntryPoint2 $ep the entry point
     * @return jIInstallerComponent[]|jIInstallerComponent2[]
     * @throws jInstallerException  if an error occurs during the install.
     */
    function getUpgraders(jInstallerEntryPoint2 $ep) {

        $epId = $ep->getEpId();

        if ($this->moduleUpgraders === null) {

            $this->moduleUpgraders = array();

            $p = $this->path.'install/';
            if (!file_exists($p)  || $this->moduleInfos[$epId]->skipInstaller) {
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

            if (!count($fileList)) {
                return array();
            }

            // now we order the list of file
            foreach($fileList as $fileInfo) {
                require_once($p.$fileInfo[0]);
                $cname = $this->name.'ModuleUpgrader_'.$fileInfo[2];
                if (!class_exists($cname))
                    throw new jInstallerException("module.upgrader.class.not.found",array($cname,$this->name));

                $upgrader = new $cname($this->name,
                                        $fileInfo[2],
                                        $this->path,
                                        $fileInfo[1],
                                        false);

                if ($fileInfo[1] && count($upgrader->targetVersions) == 0) {
                    $upgrader->targetVersions = array($fileInfo[1]);
                }
                if (count($upgrader->targetVersions) == 0) {
                    throw new jInstallerException("module.upgrader.missing.version",array($fileInfo[0], $this->name));
                }
                $this->moduleUpgraders[] = $upgrader;
                if ($upgrader instanceof jIInstallerComponent2) {
                    $upgrader->setGlobalSetup($this->globalSetup);
                }
            }
        }

        if (count($this->moduleUpgraders) && $this->moduleInfos[$epId]->version == '') {
            throw new jInstallerException("installer.ini.missing.version", array($this->name));
        }

        $list = array();

        foreach($this->moduleUpgraders as $upgrader) {

            $foundVersion = '';
            // check the version
            foreach($upgrader->targetVersions as $version) {
                if (VersionComparator::compareVersion($this->moduleInfos[$epId]->version, $version) >= 0 ) {
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
                $firstVersionDate = $this->_formatDate($this->globalSetup->getInstallerIni()->getValue($this->name.'.firstversion.date', $epId));
                if ($firstVersionDate !== null) {
                    if ($firstVersionDate >= $upgraderDate)
                        continue;
                }

                // the date of the current installed version
                $currentVersionDate = $this->_formatDate($this->globalSetup->getInstallerIni()->getValue($this->name.'.version.date', $epId));
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

    public function setAsCurrentModuleUpgrader($upgrader, jInstallerEntryPoint2 $ep) {
        $epId = $ep->getEpId();
        $upgrader->setParameters($this->moduleInfos[$epId]->parameters);

        if ($upgrader instanceof jIInstallerComponent) {
            $legacyEp = $ep->getLegacyInstallerEntryPoint();
            $upgrader->setEntryPoint($legacyEp,
                $this->moduleInfos[$epId]->dbProfile);
        }
        else {
            $ep->_setCurrentModuleInstaller($upgrader);
            $upgrader->initDbProfileForEntrypoint($this->moduleInfos[$epId]->dbProfile);
        }
    }


    public function installEntryPointFinished(jInstallerEntryPoint2 $ep) {
        if ($this->globalSetup)
            $this->globalSetup->updateInstallerContexts($this->name, $this->moduleInstaller->getContexts());
    }

    public function upgradeEntryPointFinished(jInstallerEntryPoint2 $ep, $upgrader) {
        $class = get_class($upgrader);
        $this->upgradersContexts[$class] = $upgrader->getContexts();
    }

    public function uninstallEntryPointFinished(jInstallerEntryPoint2 $ep) {
        if ($this->globalSetup)
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
     * @param string $epId
     * @param bool $installedByDefault
     * @return Item
     */
    public function getResolverItem($epId) {
        $action = $this->getInstallAction($epId);
        if ($action == Resolver::ACTION_UPGRADE) {
            $item = new Item($this->name, true, $this->sourceVersion, Resolver::ACTION_UPGRADE, $this->moduleInfos[$epId]->version);
        }
        else {
            $item = new Item($this->name, $this->isInstalled($epId), $this->sourceVersion, $action);
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

    /**
     * read the identity file
     * @throws \Exception
     */
    protected function readIdentity() {
        $xmlDescriptor = new DOMDocument();

        if(!$xmlDescriptor->load($this->path.$this->identityFile)){
            throw new jInstallerException('install.invalid.xml.file',array($this->path.$this->identityFile));
        }

        $root = $xmlDescriptor->documentElement;

        if ($root->namespaceURI == $this->identityNamespace) {
            $xml = simplexml_import_dom($xmlDescriptor);
            if (!isset($xml->info[0]->version[0])) {
                throw new jInstallerException('module.missing.version', array($this->name));
            }
            $this->sourceVersion = $this->fixVersion((string) $xml->info[0]->version[0]);
            if (trim($this->sourceVersion) == '') {
                throw new jInstallerException('module.missing.version', array($this->name));
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
     * @param SimpleXMLElement $comp
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
                return jFramework::versionMax();
            case '__LIB_VERSION__':
                return jFramework::version();
            case '__VERSION__':
                return jApp::version();
        }
        return trim($version);
    }
}
