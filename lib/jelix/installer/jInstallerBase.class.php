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
abstract class jInstallerBase {
    
    /**
     * the path of the directory of the component
     * it should be set by the constructor
     */
    protected $path = '';
    
    protected $status = 0;
    
    protected $name = '';
    
    protected $installedVersion = "";
    
    protected $sourceVersion = '';
    
    public $dependencies = array();
    
    protected $jelixMinVersion = '';
    protected $jelixMaxVersion = '';

    // informations about the descriptor file
    protected $namespace = '';
    protected $rootName = '';
    protected $identityFile = '';
    
    protected $xmlDescriptor = null;
    
    
    /**
     * code error of the installation
     */
    public $inError = 0;
    
    /**
     * @param string $name the name of the component
     * @param string $path the path of the component
     * @param integer $status the status see jInstaller::STATUS_*
     */
    function __construct($name, $path, $status, $installedVersion) {
        $this->path = $path;
        $this->name = $name;
        $this->status = $status;
        $this->installedVersion = $installedVersion;
    }
    
    public function getName() { return $this->name; }
    public function getPath() { return $this->path; }
    public function getInstalledVersion() { return $this->installedVersion; }
    

    public function isInstalled() {
        return ($this->status >= jInstaller::STATUS_INSTALLED);
    }

    public function isActivated() {
        return ($this->status >= jInstaller::STATUS_ACTIVATED);
    }

    /**
     * install the component. It just call the _install.php of the component
     *
     * It should expose a variable $installer to the _install.php,
     * $installer should be the current instance of this class.
     */
    abstract function install();

    /**
     * uninstall the component. It just call the _uninstall.php
     * of the component
     *
     * It should expose a variable $installer to the _uninstall.php,
     * $installer should be the current instance of this class.
     */
    abstract function uninstall();

    /**
     * activate the component.
     * It just call the _activate.php of the component ?
     *
     * It should expose a variable $installer to the _activate.php,
     * $installer should be the current instance of this class.
     */
    abstract function activate();

    /**
     * deactivate the component. It just call the _deactivate.php
     * of the component
     *
     * It should expose a variable $installer to the _deactivate.php,
     * $installer should be the current instance of this class.
     */
    abstract function deactivate();


    public function init () {
        $this->readIdentity();
    }
    
    protected function updateVersion($newVersion) {
        $this->installedVersion = $newVersion;

        if ($this->compareVersion($newVersion, $this->sourceVersion) > 0) {
            $info = $this->xmlDescriptor->documentElement->getElementsByTagName('info')->item(0);
            $version = $info->getElementsByTagName('version')->item(0);
            $version->firstChild = $this->xmlDescriptor->createTextNode($newVersion);
            $this->xmlDescriptor->save($this->path.$this->identityFile);
        }

        jInstaller::$iniFile->setValue($this->name.'.version', $newVersion, 'modules');
        jInstaller::$iniFile->save();
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
        
        // TODO : verifier avec le schema relaxng
        
        
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
                    $this->jelixMinVersion = $dependency['minversion'];
                    $this->jelixMaxVersion = $dependency['maxversion'];
                }
                else if ($type == 'module') {
                    $this->dependencies[] = array(
                            'type'=> 'module',
                            'id' => $dependency['id'],
                            'name' => $dependency['name'],
                            'minversion' => $dependency['minversion'],
                            'maxversion' => $dependency['maxversion'],
                            ''
                            );
                }
                else if ($type == 'plugin') {
                    $this->dependencies[] = array(
                            'type'=> 'plugin',
                            'id' => $dependency['id'],
                            'name' => $dependency['name'],
                            'minversion' => $dependency['minversion'],
                            'maxversion' => $dependency['maxversion'],
                            );
                }
            }
        }
    }
    
    protected function compareVersion($version1, $version2) {

        if ($version1 == $version2)
            return 0;

        $v1 = explode('.', $version1);
        $v2 = explode('.', $version2);

        if (count($v1) > count($v2) ) {
            $reverse = true;
            $v = $v1;
            $v1 = $v2;
            $v2 = $v;
        }
        else
            $reverse = false;

        $r = '/^([0-9]+)([a-zA-Z]*|pre|-?dev)([0-9]*)(pre|-?dev)?$/';

        foreach ($v1 as $k=>$v) {

            if ($v == $v2[$k])
                continue;

            $pm = preg_match($r, $v, $m1);
            $pm2 = preg_match($r, $v2[$k], $m2);

            if ($pm && $pm2) {
                if ($m1[1] != $m2[1]) {
                    if ($reverse) {
                        return ($m1[1] < $m2[1] ? 1: -1);
                    }
                    else
                        return ($m1[1] < $m2[1] ? -1: 1);
                }

                $this->normalizeVersionNumber($m1);
                $this->normalizeVersionNumber($m2);
                if ($m1[2] != $m2[2]) {
                    if ($reverse) {
                        return ($m1[2] < $m2[2] ? 1: -1);
                    }
                    else
                        return ($m1[2] < $m2[2] ? -1: 1);
                }
                if ($m1[3] != $m2[3]) {
                    if ($reverse) {
                        return ($m1[3] < $m2[3] ? 1: -1);
                    }
                    else
                        return ($m1[3] < $m2[3] ? -1: 1);
                }

                $v1pre = ($m1[4] == 'dev');
                $v2pre = ($m2[4] == 'dev');
                
                if ($v1pre && !$v2pre) {
                    return ($reverse ? 1 : -1);
                }
                elseif ($v2pre && !$v1pre) {
                    return ($reverse ? -1 : 1);
                }
                else if (!isset($v1[$k+1]) && !isset($v2[$k+1])) {
                    return 0;
                }
            }
        }

        if (count($v1) != count($v2) ) {
            return ($reverse?1:-1);
        }

        return -5;
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

