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
* a class to install an application.
* @package     jelix
* @subpackage  installer
* @experimental
* @since 1.1
*/
class jInstallerApp extends jInstallerBase {

    /**
     * 
     * @param string $path the path of the application directory
     */
    function __construct($path) {
        // read the project.xml
        // and set the $path property
    }

    /**
     * get on object to modify the config file of an entry point 
     * @param string $filename relative path to the var/config directory
     * @return jIniMultiFilesModifier
     */
    function getConfig($entrypoint) {
        
         throw new Exception('not implemented');
        
        //TODO
        // get the path of the config file corresponding to the entrypoint,
        // from the project.xml
        $filename = '';
        return new jIniMutliFilesModifier(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php',
                                          JELIX_APP_CONFIG_PATH.$filename);
    }

    function addEntryPoint($filename, $type, $configFilename) {
        throw new Exception('not implemented');
        // should modify the project.xml
        // if the config file doesn't exist, create it
        // if the entrypoint doesn't exist, create it, with the given type
    }


    function removeEntryPoint($filename) {
        throw new Exception('not implemented');
        // should modify the project.xml
        // if the config file is not used by another entrypoint, remove it
    }

}

