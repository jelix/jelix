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
* a class to install a module. 
* @package     jelix
* @subpackage  installer
* @experimental
* @since 1.1
*/
class jInstallerComponentModule extends jInstallerComponentBase {
    
    protected $namespace = 'http://jelix.org/ns/module/1.0';
    protected $rootName = 'module';
    protected $identityFile = 'module.xml';

    protected $moduleInstaller = null;
    
    protected $moduleUpgraders = null;

    function getInstaller($config) {
        if ($this->moduleInstaller == null) {
            if (file_exists($this->path.'install/install.php')) {
                include($this->path.'install/install.php');
                $cname = $this->name.'ModuleInstaller';
                if (!class_exists($cname))
                    throw new jInstallerException("module.installer.class.not.found",array($cname,$this->name));
                $this->moduleInstaller = new $cname($config, $this->path, $this->sourceVersion);
            }
            else
                $this->moduleInstaller = new jInstallerModule($config, $this->path, $this->sourceVersion);
        }
        else {
            $this->moduleInstaller->config = $config; 
        }
        return $this->moduleInstaller;
    }

    /**
     * upgrade the module.
     */
    function getUpgraders($config) {
        
        if ($this->moduleUpgraders !== null) {
            foreach($this->moduleUpgraders as $upgrader) {
                $upgrader->config = $config;
            }
            return $this->moduleUpgraders;
        }
        
        $this->moduleUpgraders = array();
        
        
        $p = $this->path.'install/';
        if (!file_exists($p))
            return array();

        // we get the list of files for the upgrade
        $fileList = array();
        if ($handle = opendir($p)) {
            while (false !== ($f = readdir($handle))) {
                if (!is_dir($p.$f) && preg_match('/^upgrade_to_([^_]+)_([^\.]+)\.php$/', $f, $m)) {
                    $fileList[] = array($f, $m[1], $m[2]);
                }
            }
            closedir($handle);
        }
        
        if (!count($fileList)) {
            return $this->moduleUpgraders;
        }
        
        // now we order the list of file
        usort($fileList, array($this, 'sortFileList'));
        foreach($fileList as $fileInfo) {
            if ($this->compareVersion($this->installedVersion, $fileInfo[1]) >= 0 )
                continue;
            include($p.$fileInfo[0]);
            $cname = $this->name.'ModuleUpgrader_'.$fileInfo[2];
            if (!class_exists($cname))
                throw new jInstallerException("module.upgrader.class.not.found",array($cname,$this->name));
            $this->moduleUpgraders[] = new $cname($config, $this->path, $fileInfo[1]);
        }

        return $this->moduleUpgraders;
    }

    function sortFileList($fileA, $fileB) {
        return $this->compareVersion($fileA[1], $fileB[1]);
    }
}
