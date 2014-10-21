<?php
/**
* @author      Laurent Jouanneau
* @copyright   2008-2014 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer\Reporter;

/**
 * a reporter which reports... nothing
 */
class NoOutput implements \Jelix\Installer\ReporterInterface {

    function start() {
    }

    /**
     * displays a message
     * @param string $message the message to display
     * @param string $type the type of the message : 'error', 'notice', 'warning', ''
     */
    function message($message, $type='') {
    }

    /**
     * called when the installation is finished
     * @param array $results an array which contains, for each type of message,
     * the number of messages
     */
    function end($results) {
    }
}
