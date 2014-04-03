<?php
/**
* @author     Laurent Jouanneau
* @copyright  2006-2014 Laurent Jouanneau
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Logger\Output;

/**
 * logger storing message into syslog
 */
class Syslog implements \Jelix\Logger\OutputInterface {
    /**
     * @param \Jelix\Logger\MessageInterface $message the message to log
     */
    function logMessage($message) {
        $type = $message->getCategory();

        if (\Jelix\Core\App::router()->request)
            $ip = \Jelix\Core\App::router()->request->getIP();
        else
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        error_log(date ("Y-m-d H:i:s")."\t".$ip."\t$type\t".$message->getFormatedMessage(), 0);
    }

    function output($response) {}

}
