<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2008-2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * a reporter which reports... nothing
 */
class ghostInstallReporter implements jIInstallReporter {
    use jInstallerReporterTrait;

    function start() {
    }

    /**
     * displays a message
     * @param string $message the message to display
     * @param string $type the type of the message : 'error', 'notice', 'warning', ''
     */
    function message($message, $type='') {
        $this->addMessageType($type);
    }

    /**
     * called when the installation is finished
     */
    function end() {
    }
}
