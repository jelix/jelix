<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2008-2009 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* EXPERIMENTAL
* a class to install a component (module or plugin) 
* @package     jelix
* @subpackage  installer
* @experimental
* @since 1.1
*/
abstract class jInstallerComponentBase {
    
    /**
     * the path of the directory of the component
     * it should be set by the constructor
     */
    protected $path = '';
    
    protected $access = 0;
    
    protected $isInstalled = false;
    
    protected $name = '';
    
    protected $installedVersion = "";
    
    protected $sourceVersion = '';
    
    public $dependencies = array();
    
    protected $jelixMinVersion = '*';
    protected $jelixMaxVersion = '*';

    // informations about the descriptor file
    protected $namespace = '';
    protected $rootName = '';
    protected $identityFile = '';
    
    protected $xmlDescriptor = null;
    
    protected $mainInstaller = null;
    
    /**
     * code error of the installation
     */
    public $inError = 0;
    
    /**
     * @param string $name the name of the component
     * @param string $path the path of the component
     * @param boolean $isInstalled true if the component is installed
     * @param integer $access 0=unused, 1=only by other module, 2=public
     * @param string $installedVersion the installed version
     */
    function __construct($name, $path, $isInstalled, $access, $installedVersion, $mainInstaller) {
        $this->path = $path;
        $this->name = $name;
        $this->access = $access;
        $this->isInstalled = $isInstalled;
        $this->installedVersion = $installedVersion;
        $this->mainInstaller = $mainInstaller;
    }
    
    public function getName() { return $this->name; }
    public function getPath() { return $this->path; }
    public function getInstalledVersion() { return $this->installedVersion; }
    public function getSourceVersion() { return $this->sourceVersion; }
    public function getJelixVersion() { return array($this->jelixMinVersion, $this->jelixMaxVersion);}
    public function getAccessLevel() { return $this->access; }

    public function isInstalled() {
        return $this->isInstalled;
    }

    public function isUpgraded() {
        return ($this->isInstalled && ($this->compareVersion($this->sourceVersion,$this->installedVersion) == 0));
    }


    /**
     * get the object which is responsible to install the component. this
     * object should implement jIInstallerComponent.
     *
     * @param jIniMultiFilesModifier $config the configuration of the entry point
     * @return jIInstallerComponent
     */
    abstract function getInstaller($config);

    /**
     * return the list of objects which are responsible to upgrade the component
     * from the current installed version of the component.
     * 
     * this method should be called after verifying and resolving
     * dependencies. Needed components (modules or plugins) should be
     * installed/upgraded before calling this method
     * 
     * @param jIniMultiFilesModifier $config the configuration of the entry point
     * @throw jInstallerException  if an error occurs during the install.
     * @return array   array of jIInstallerComponent
     */
    abstract function getUpgraders($config);

    public function init () {
        $this->readIdentity();
    }

    protected $identityReaded = false;
    
    protected function readIdentity() {
        if ($this->identityReaded)
            return;
        $this->identityReaded = true;
        $this->xmlDescriptor = new DOMDocument();

        if(!$this->xmlDescriptor->load($this->path.$this->identityFile)){
            throw new jException('jelix~install.invalid.xml.file',array($this->path.$this->identityFile));
        }
        
        // TODO : verify with the relaxNG schema ?
        
        
        $root = $this->xmlDescriptor->documentElement;

        if ($root->namespaceURI == $this->namespace) {
            
            $xml = simplexml_import_dom($this->xmlDescriptor);
            $this->sourceVersion = (string) $xml->info[0]->version[0];
            
            $this->readDependencies($xml);
        }


      /*  
<module xmlns="http://jelix.org/ns/module/1.0">
    <info id="jelix@modules.jelix.org" name="jelix" createdate="">
        <version stability="stable" date="">__LIB_VERSION__</version>
        <label lang="en-EN" locale="">Jelix Main Module</label>
        <description lang="en-EN" locale="" type="text/xhtml">Main module of jelix which contains some ressources needed by jelix classes</description>
        <license URL="http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html">LGPL 2.1</license>
        <copyright>2005-2008 Laurent Jouanneau and other contributors</copyright>
        <creator name="Laurent Jouanneau" nickname="" email="" active="true"/>
        <contributor name="hisname" email="hisemail@yoursite.undefined" active="true" since="" role=""/>
        <homepageURL>http://jelix.org</homepageURL>
        <updateURL>http://jelix.org</updateURL>
    </info>
    <dependencies>
        <jelix minversion="__LIB_VERSION__" maxversion="__LIB_VERSION__" edition="dev/opt/gold"/>
        <module id="" name="" minversion="" maxversion="" />
        <plugin id="" name="" minversion="" maxversion="" />
    </dependencies>
</module>
        
      */

    }

    protected function readDependencies($xml) {

        $this->dependencies = array();

        if (isset($xml->dependencies)) {
            foreach ($xml->dependencies->children() as $type=>$dependency) {
                if ($type == 'jelix') {
                    $this->jelixMinVersion = isset($dependency['minversion'])?(string)$dependency['minversion']:'*';
                    $this->jelixMaxVersion = isset($dependency['maxversion'])?(string)$dependency['maxversion']:'*';
                    if ($this->name != 'jelix') {
                        $this->dependencies[] = array(
                            'type'=> 'module',
                            'id' => 'jelix@jelix.org',
                            'name' => 'jelix',
                            'minversion' => $this->jelixMinVersion,
                            'maxversion' => $this->jelixMaxVersion,
                            ''
                        );
                    }
                }
                else if ($type == 'module') {
                    $this->dependencies[] = array(
                            'type'=> 'module',
                            'id' => (string)$dependency['id'],
                            'name' => (string)$dependency['name'],
                            'minversion' => isset($dependency['minversion'])?(string)$dependency['minversion']:'*',
                            'maxversion' => isset($dependency['maxversion'])?(string)$dependency['maxversion']:'*',
                            ''
                            );
                }
                else if ($type == 'plugin') {
                    $this->dependencies[] = array(
                            'type'=> 'plugin',
                            'id' => (string)$dependency['id'],
                            'name' => (string)$dependency['name'],
                            'minversion' => isset($dependency['minversion'])?(string)$dependency['minversion']:'*',
                            'maxversion' => isset($dependency['maxversion'])?(string)$dependency['maxversion']:'*',
                            ''
                            );
                }
            }
        }
    }
    
    
    public function checkJelixVersion ($jelixVersion) {
        return ($this->compareVersion($this->jelixMinVersion, $jelixVersion) <= 0 &&
                $this->compareVersion($jelixVersion, $this->jelixMaxVersion) <= 0);
    }
    
    public function checkVersion($min, $max) {
        return ($this->compareVersion($min, $this->sourceVersion) <= 0 &&
                $this->compareVersion($this->sourceVersion, $max) <= 0);
    }
    
    /**
     * return 1 if $version1 > $version2, 0 if equals, and -1 if $version1 < $version2
     */
    protected function compareVersion($version1, $version2) {

        if ($version1 == $version2)
            return 0;

        $v1 = explode('.', $version1);
        $v2 = explode('.', $version2);

        if (count($v1) > count($v2) ) {
            $v2 = array_pad($v2, count($v1), ($v2[count($v2)-1] == '*'?'*':'0'));
        }
        elseif (count($v1) < count($v2) ) {
            $v1 = array_pad($v1, count($v2), ($v1[count($v1)-1] == '*'?'*':'0'));
        }

        $r = '/^([0-9]+)([a-zA-Z]*|pre|-?dev)([0-9]*)(pre|-?dev)?$/';

        foreach ($v1 as $k=>$v) {

            if ($v == $v2[$k] || $v == '*' || $v2[$k] == '*')
                continue;

            $pm = preg_match($r, $v, $m1);
            $pm2 = preg_match($r, $v2[$k], $m2);

            if ($pm && $pm2) {
                if ($m1[1] != $m2[1]) {
                    return ($m1[1] < $m2[1] ? -1: 1);
                }

                $this->normalizeVersionNumber($m1);
                $this->normalizeVersionNumber($m2);
                if ($m1[2] != $m2[2]) {
                    return ($m1[2] < $m2[2] ? -1: 1);
                }
                if ($m1[3] != $m2[3]) {
                    return ($m1[3] < $m2[3] ? -1: 1);
                }

                $v1pre = ($m1[4] == 'dev');
                $v2pre = ($m2[4] == 'dev');
                
                if ($v1pre && !$v2pre) {
                    return -1;
                }
                elseif ($v2pre && !$v1pre) {
                    return 1;
                }
                else if (!isset($v1[$k+1]) && !isset($v2[$k+1])) {
                    return 0;
                }
            }
            elseif ($pm){
                throw new Exception ("bad version number :". $version2);
            }
            else
                throw new Exception ("bad version number :".$version1);
        }

        return 0;
    }
    
    protected function normalizeVersionNumber(&$n) {
        $n[2] = strtolower($n[2]);
        if ($n[2] == 'pre' || $n[2] == 'dev' || $n[2] == '-dev') {
            $n[2] = '';
            $n[3] = '';
            $n[4] = 'dev';
        }
        if (!isset($n[4]))
            $n[4] = '';
        else {
            $n[4] = strtolower($n[4]);
            if ($n[4] == 'pre' || $n[4] == '-dev' ) $n[4] = 'dev';
        }

        if ($n[2] == 'a') $n[2] = 'alpha';
        elseif($n[2] == 'b') $n[2] = 'beta';
        elseif($n[2] == '') $n[2] = 'zzz';
    }

}

