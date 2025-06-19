<?php
/**
 * @package    jelix
 * @subpackage core_log
 *
 * @author     Laurent Jouanneau
 * @copyright  2006-2014 Laurent Jouanneau
 *
 * @see       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Logger\Message;

/**
 * class that handles a simple message for a logger.
 */
class Text implements \Jelix\Logger\MessageInterface
{
    /**
     * @var string the category of the message
     */
    protected $category;

    /**
     * @var string the message
     */
    protected $message;

    public function __construct($message, $category = 'default')
    {
        $this->category = $category;
        $this->message = $message;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getFormatedMessage()
    {
        return $this->message;
    }
}
