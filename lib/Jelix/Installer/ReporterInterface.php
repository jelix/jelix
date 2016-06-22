<?php
/**
* @author      Laurent Jouanneau
* @copyright   2008-2016 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer { 
// enclose namespace here because this file is inserted into jelix_check_server.php by a build tool

/**
* interface for classes used as reporter for installation or check etc...
* This classes are responsible to show informations to the user
* @since 1.2
*/
interface ReporterInterface {

    /**
     * start the process
     */
    public function start();

    /**
     * displays a message
     * @param string $message the message to display
     * @param string $type the type of the message : 'error', 'notice', 'warning', ''
     */
    public function message($message, $type='');

    /**
     * called when the installation is finished
     */
    public function end();

    /**
     * return the number of messages of a specific type
     * @return integer
     */
    public function getMessageCounter($type);
}

}// end of namespace
