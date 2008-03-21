<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* EXPERIMENTAL
* a class to install an application. You should override it into a install/install.php file. The 
* class should be named appInstaller
* @package     jelix
* @subpackage  installer
* @experimental
* @since 1.1
*/
abstract class jInstallerApp extends jInstallerBase {




    /**
    * Install modules by respecting dependencies
    */
    function installModules() {
        throw new Exception("installModules not implemented");
    }

    function uninstallModules() {
        throw new Exception("uninstallModules not implemented");
    }

}

?>