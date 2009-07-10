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
class jInstallerModule extends jInstallerBase {
    
    protected $namespace = 'http://jelix.org/ns/module/1.0';
    protected $rootName = 'module';
    protected $identityFile = 'module.xml';

    /**
     * install the module.
     * this method should be called after verifying and resolving
     * dependencies. Needed components (modules or plugins) should be
     * installed before calling this method
     * @throw jException  if an error occurs during the install.
     */
    function install() {
        if (file_exists($this->path.'install/install.php')) {
            
        }
    }
    
    /**
     * upgrade the module.
     * this method should be called after verifying and resolving
     * dependencies. Needed components (modules or plugins) should be
     * installed/upgraded before calling this method
     * @throw jException  if an error occurs during the install.
     */
    function upgrade($fromVersion) {
        $p = $this->path.'install/';
        
        // we get the list of files for the upgrade
        $fileList = array();
        if ($handle = opendir($p)) {
            while (false !== ($f = readdir($handle))) {
                if (!is_dir($p.$f) && preg_match('/^upgrade_to_([^\.]*)\.php$/', $f, $m)) {
                    $fileList[] = array($f, $m[1]);
                }
            }
            closedir($handle);
        }
        
        if (!count($fileList)) {
            return;
        }
        
        // now we order the list of file
        sort($fileList, array($this, 'sortFileList'));
        
        foreach($fileList as $fileInfo) {
            if ($this->compareVersion($this->installedVersion, $fileInfo[1]))
                continue;
            include($p.$fileInfo[0]);
            $this->updateVersion($fileInfo[1]);
        }
    }

    function sortFileList($fileA, $fileB) {
        return $this->compareVersion($fileA[1], $fileB[1]);
    }
    
    
    
    
    /**
     * uninstall the module, by checking dependencies.
     * @throw jException  if an error occurs during the install.
     */
    function uninstall() {
        // * check that all dependencies are ok : the needed modules and plugins
        // should be present in the application
        // * start the uninstall of all needed modules and plugins before installing
        // the module. 
        // * if ok, uninstall the module, by calling the _uninstall.php script      
    }
    
    function activate() {
    }
    
    function deactivate() {
        
    }
}

