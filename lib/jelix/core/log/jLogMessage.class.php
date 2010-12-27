<?php
/**
* @package    jelix
* @subpackage core
* @author     Laurent Jouanneau
* @copyright  2006-2010 Laurent Jouanneau
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * class that handles a simple message for a logger
 */
class jLogMessage implements jILoggerMessage {
    /**
     * @var string the category of the message
     */
    protected $category;

    /**
     * @var string the message
     */
    protected $message;

    public function __construct($message, $category='default') {
        $this->category = $category;
        $this->message = $message;
    }

    public function getCategory() {
        return $this->category;
    }

    public function getFormatedMessage() {
        return $this->message;
    }
}
