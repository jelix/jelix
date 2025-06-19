<?php
/**
 * @author     Laurent Jouanneau
 * @contributor F. Fernandez, Hadrien Lanneau
 *
 * @copyright  2006-2014 Laurent Jouanneau, 2007 F. Fernandez, 2011 Hadrien Lanneau
 *
 * @see       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Logger;

/**
 * interface for log message. A component which want to log
 * a message can use an object implementing this interface.
 * Classes that implements it are responsible to format
 * the message. Formatting a message depends on its type.
 */
interface MessageInterface
{
    /**
     * return the category of the message.
     *
     * @return string category name
     */
    public function getCategory();

    /**
     * @return string the message
     */
    public function getMessage();

    /**
     * return the full message, formated for simple text output (it can contain informations
     * other than the message itself).
     *
     * @return string the message
     */
    public function getFormatedMessage();
}
