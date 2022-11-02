<?php
/**
 * @author      Gérald Croes, Laurent Jouanneau
 *
 * @copyright   2001-2005 CopixTeam, 2005-2022 Laurent Jouanneau
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Event;

/**
 * base class for event listeners.
 *
 */
class EventListener
{
    /**
     * @var string[] mapping between event name and methods to execute
     *               keys are events, values are method name.
     *               useful when events name contains characters that are forbidden
     *               in a method name.
     *
     */
    protected $eventMapping = array();

    /**
     * perform a given event.
     *
     * @param EventInterface $event the event itself
     */
    public function performEvent($event)
    {
        $eventName = $event->getName();
        if (isset($this->eventMapping[$eventName])) {
            $methodName = $this->eventMapping[$eventName];
        } else {
            $methodName = 'on'.$event->getName();
        }
        $this->{$methodName}($event);
    }
}