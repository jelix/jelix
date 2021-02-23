<?php
/**
 * @author     Gérald Croes
 * @contributor Laurent Jouanneau
 *
 * @copyright  2001-2005 CopixTeam, 2005-2014 Laurent Jouanneau
 * This class was get originally from the Copix project
 * (CopixListener, Copix 2.3dev20050901, http://www.copix.org)
 * Many of lines of code are copyrighted 2001-2005 CopixTeam (LGPL licence).
 * Initial author of this Copix class is Gerald Croes,
 * and this class was adapted/improved for Jelix by Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Event;

/**
 * base class for event listeners.
 */
class Listener
{
    /**
     * @var string[] mapping between event name and methods to execute
     *               keys are events, values are method name.
     *               useful when events name contains characters that are forbidden
     *               in a method name.
     *
     * @since 1.7.0
     */
    protected $eventMapping = array();

    /**
     * perform a given event.
     *
     * @param Jelix\Event\Event $event the event itself
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
