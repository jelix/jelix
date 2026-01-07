<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2008-2018 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Reporter;

/**
 * Interface for classes used as a reporter for installation or check etc...
 * This class is responsible for showing information to the user.
 *
 * @since 1.7
 */
interface ReporterInterface
{
    /**
     * start the process.
     */
    public function start();

    /**
     * displays a message.
     *
     * @param string $message the message to display
     * @param string $type    the type of the message : 'error', 'notice', 'warning', ''
     */
    public function message($message, $type = '');

    /**
     * called when the installation is finished.
     */
    public function end();

    /**
     * return the number of messages of a specific type.
     *
     * @param mixed $type
     *
     * @return int
     */
    public function getMessageCounter($type);
}
