<?php
/**
* @author      Laurent Jouanneau
* @copyright   2008-2014 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer { 
// enclose namespace here because this file is inserted into checker.php in some builds

/**
* interface for classes used as reporter for installation or check etc...
* This classes are responsible to show informations to the user
* @since 1.2
*/
interface ReporterInterface {

    /**
     * start the process
     */
    function start();

    /**
     * displays a message
     * @param string $message the message to display
     * @param string $type the type of the message : 'error', 'notice', 'warning', ''
     */
    function message($message, $type='');

    /**
     * called when the installation is finished
     * @param array $results an array which contains, for each type of message,
     * the number of messages
     */
    function end($results);

}

}// end of namespace
