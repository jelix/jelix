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
* a class to install a module.
* @package     jelix
* @subpackage  installer
* @experimental
* @since 1.1
*/
class jInstallerPlugin extends jInstallerBase {

    protected $application;

    /**
     * The plugin should be present in the application.
     * @param string $type the type of the plugin ('acl', 'auth', 'tpl', 'urls'...)
     * @param string $name the name of the plugin
     * @param jInstallerApp $application
     */
    function __construct($type, $name, $application) {
        // read the module.xml
        // and set the $path property
    }

}

