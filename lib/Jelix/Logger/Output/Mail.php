<?php
/**
* @package    jelix
* @subpackage core_log
* @author     Laurent Jouanneau
* @copyright  2006-2014 Laurent Jouanneau
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Logger\Output;

/**
 * logger sending message by email
 */
class Mail implements \Jelix\Logger\OutputInterface {

    /**
     *  @var array messages to send
     */
    protected $messages = array();

    /**
     * @param \Jelix\Logger\MessageInterface $message the message to log
     */
    function logMessage($message) {
        $this->messages[] = $message;
    }

    /**
     * @param \Jelix\Routing\ServerResponse $response
     */
    function output($response) {

        if (!\Jelix\Core\App::router()->request)
            return;

        $email = \Jelix\Core\App::config()->mailLogger['email'];
        $headers = str_replace(array('\\r','\\n'),array("\r","\n"),\Jelix\Core\App::config()->mailLogger['emailHeaders']);
        $message = '';
        foreach($this->messages as $msg) {
            $message.= "\n\n".$msg->getFormatedMessage();
        }

        error_log(wordwrap($message,70),1, $email, $headers);
    }
}
