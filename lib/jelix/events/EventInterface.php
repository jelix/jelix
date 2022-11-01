<?php
/**
 * @author      Laurent Jouanneau
 *
 * @copyright   2022 Laurent Jouanneau
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Events;

/**
 * Interface for event classes
 */
interface EventInterface
{
    /**
     * @return string the name of the event.
     */
    public function getName();

}