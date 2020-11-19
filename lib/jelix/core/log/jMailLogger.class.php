<?php
/**
 * @package    jelix
 * @subpackage core_log
 *
 * @author     Laurent Jouanneau
 * @copyright  2006-2012 Laurent Jouanneau
 *
 * @see       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * logger sending message by email.
 */
class jMailLogger implements jILogger
{
    /**
     *  @var jILogMessage[] messages to send
     */
    protected $messages = array();

    /**
     * @param jILogMessage $message the message to log
     */
    public function logMessage($message)
    {
        $this->messages[] = $message;
    }

    /**
     * @param jResponse $response
     */
    public function output($response)
    {
        $email = jApp::config()->mailLogger['email'];
        $headers = str_replace(array('\\r', '\\n'), array("\r", "\n"), jApp::config()->mailLogger['emailHeaders']);
        $message = '';
        foreach ($this->messages as $msg) {
            $message .= "\n\n".$msg->getFormatedMessage();
        }

        error_log(wordwrap($message, 70), 1, $email, $headers);
    }
}
