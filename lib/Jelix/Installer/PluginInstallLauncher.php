<?php
/**
* @author      Laurent Jouanneau
* @copyright   2008-2014 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer;


/**
* EXPERIMENTAL
* a class to install a plugin.
* @experimental
* @since 1.2
*/
class PluginInstallLauncher extends AbstractInstallLauncher {

    protected $identityNamespace = 'http://jelix.org/ns/plugin/1.0';
    protected $rootName = 'plugin';
    protected $identityFile = 'plugin.xml';


}

