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
 * interface for loggers.
 */
interface OutputInterface
{
    /**
     * @param MessageInterface $message the message to log
     */
    public function logMessage($message);

    /**
     * output messages to the given response.
     *
     * @param \Jelix\Routing\ServerResponse $response
     */
    public function output($response);
}
