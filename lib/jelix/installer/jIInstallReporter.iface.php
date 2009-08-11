<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2008-2009 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* interface for classes used as reporter for installation
* This classes are responsible to show information to the user
* @package     jelix
* @subpackage  installer
* @since 1.2
*/
interface jIInstallReporter {

    /**
     * start the installation
     */
    function start();

    /**
     * displays a message
     * @param string $message the message to display
     * @param string $type the type of the message : 'error', 'notice', 'warning', ''
     */
    function showMessage($message, $type='');

    /**
     * called when the installation is finished
     * @param object $installer
     */
    function end($installer);

}

