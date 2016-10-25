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
use \Jelix\Dependencies\Item;
use \Jelix\Dependencies\Resolver;

/**
* a class to install a component (module or plugin) 
* @package     jelix
* @subpackage  installer
* @since 1.2
*/
abstract class jInstallerComponentBase {

    /**
     *  @var string  name of the component
     */
    protected $name = '';

    /**
     * @var string the path of the directory of the component
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
    protected $identityNamespace = '';
    
    /**
     * @var string the expected name of the root element in the xml file
     */
    protected $rootName = '';
    
    /**
     * @var string the name of the xml file
     */
    protected $identityFile = '';

    /**
     * @var jInstaller the main installer controller
     */
    protected $mainInstaller = null;
    
    /**
     * list of dependencies of the module
     */
    public $dependencies = array();
    
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
     * @param string $name the name of the component
     * @param string $path the path of the component
     * @param jInstaller $mainInstaller
     */
    function __construct($name, $path, $mainInstaller) {
        $this->path = $path;
        $this->name = $name;
        $this->mainInstaller = $mainInstaller;
    }

    public function getName() { return $this->name; }
    public function getPath() { return $this->path; }
    public function getSourceVersion() { return $this->sourceVersion; }
    public function getSourceDate() { return $this->sourceDate; }
    public function getJelixVersion() { return array($this->jelixMinVersion, $this->jelixMaxVersion);}

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
        return ($this->isInstalled($epId) &&
                (VersionComparator::compareVersion($this->sourceVersion, $this->moduleInfos[$epId]->version) == 0));
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
     * get the object which is responsible to install the component. this
     * object should implement jIInstallerComponent.
     *
     * @param jInstallerEntryPoint $ep the entry point
     * @param boolean $installWholeApp true if the installation is done during app installation
     * @return jIInstallerComponent the installer, or null if there isn't any installer
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
     * @throw jInstallerException  if an error occurs during the install.
     * @return array   array of jIInstallerComponent
     */
    abstract function getUpgraders($ep);

    public function installFinished($ep) { }

    public function upgradeFinished($ep, $upgrader) { }

    public function uninstallFinished($ep) { }

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
    public function getResolverItem($epId, $installedByDefault = false) {
        if ($this->isInstalled($epId)) {
            if (!$this->isActivated($epId)) {
                $item = new Item($this->name, true, $this->sourceVersion, Resolver::ACTION_REMOVE);
            }
            elseif ($this->isUpgraded($epId)) {
                $item = new Item($this->name, true, $this->sourceVersion, Resolver::ACTION_NONE);
            }
            else {
                $item = new Item($this->name, true, $this->sourceVersion, Resolver::ACTION_UPGRADE, $this->moduleInfos[$epId]->version);
            }
        }
        elseif ($this->isActivated($epId) || $installedByDefault) {
            $item = new Item($this->name, false, $this->sourceVersion, Resolver::ACTION_INSTALL);
        }
        else {
            $item = new Item($this->name, false, $this->sourceVersion, Resolver::ACTION_NONE);
        }
        foreach($this->dependencies as $dep) {
            $item->addDependency($dep['name'], $dep['version']);
        }
        $item->setProperty('component', $this);
        return $item;
    }

    /**
     * read the identity file
     */
    protected function readIdentity() {
        $xmlDescriptor = new DOMDocument();

        if(!$xmlDescriptor->load($this->path.$this->identityFile)){
            throw new jInstallerException('install.invalid.xml.file',array($this->path.$this->identityFile));
        }

        $root = $xmlDescriptor->documentElement;

        if ($root->namespaceURI == $this->identityNamespace) {
            $xml = simplexml_import_dom($xmlDescriptor);
            $this->sourceVersion = $this->fixVersion((string) $xml->info[0]->version[0]);
            if (isset($xml->info[0]->version['date']))
                $this->sourceDate = (string) $xml->info[0]->version['date'];
            else
                $this->sourceDate = '';
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
    </dependencies>
</module>
      */

        $this->dependencies = array();

        if (isset($xml->dependencies)) {
            foreach ($xml->dependencies->children() as $type=>$dependency) {
                if ($type != 'jelix' && $type != 'module') {
                    continue;
                }
                $versionRange = '';
                $minversion = isset($dependency['minversion'])?
                                $this->fixVersion((string)$dependency['minversion']):
                                '0';
                if (trim($minversion) == '') {
                    $minversion = '0';
                }
                if ($minversion != '0') {
                    $versionRange = '>='.$minversion;
                }
                $maxversion = isset($dependency['maxversion'])?
                                $this->fixVersion((string)$dependency['maxversion']):
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
                $name = (string)$dependency['name'];
                if (trim($name) == '' && $type != 'jelix')
                    throw new Exception('Name is missing in a dependency declaration in module '.$this->name);
                $id = (string)$dependency['id'];

                if ($type == 'jelix') {
                    $this->jelixMinVersion = $minversion;
                    $this->jelixMaxVersion = $maxversion;
                    if ($this->name != 'jelix') {
                        $this->dependencies[] = array(
                            'type'=> 'module',
                            'id' => 'jelix@jelix.org',
                            'name' => 'jelix',
                            'minversion' => $this->jelixMinVersion,
                            'maxversion' => $this->jelixMaxVersion,
                            'version' => $versionRange
                        );
                    }
                }
                else if ($type == 'module') {
                    $this->dependencies[] = array(
                            'type'=> 'module',
                            'id' => $id,
                            'name' => $name,
                            'minversion' => $minversion,
                            'maxversion' => $maxversion,
                            'version' => $versionRange
                            );
                }
            }
        }
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

