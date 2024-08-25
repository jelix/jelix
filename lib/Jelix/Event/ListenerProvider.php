<?php
/**
 * @author   Gérald Croes, Patrice Ferlet, Laurent Jouanneau, Dominique Papin, Steven Jehannet
 *
 * @copyright 2001-2005 CopixTeam, 2005-2024 Laurent Jouanneau, 2009 Dominique Papin
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Event;


use Jelix\Core\App;
use Jelix\Core\Includer\Includer;

class ListenerProvider implements \Psr\EventDispatcher\ListenerProviderInterface
{
    /**
     * because a listener can listen several events, we should
     * create only one instance of a listener for performance, and
     * $hashListened will contain only reference to this listener.
     *
     * @var EventListener[][]
     */
    protected $listenersSingleton = array();

    /**
     * hash table for event listened.
     * $hashListened['eventName'] = array of events (by reference).
     *
     * @var EventListener[][]
     */
    protected $hashListened = array();

    /**
     * Jelix Configuration
     * @var object
     */
    protected $config;

    public function __construct(object $jelixConfig)
    {
        $this->config = $jelixConfig;
    }


    public function getListenersForEvent(object $event) : iterable
    {
        $eventClass = get_class($event);
        if ($eventClass == 'jEvent' || $eventClass == 'Jelix\Event\Event') {
            // if this is a jEvent or Jelix\Event\Event class, we search with the event name
            $eventName = $event->getName();
            if (!isset($this->hashListened[$eventName])) {
                $this->loadListenersFor($eventName);
            }
        }
        else if ($event instanceof EventInterface) {
            // if this is an inherited class from Jelix\Event\Event, we must
            // search listeners both by event name and event class name
            $eventName = $event->getName();
            if (!isset($this->hashListened[$eventName])) {
                $this->loadListenersFor($eventName);
            }
            $listeners = $this->hashListened[$eventName];
            if (!isset($this->hashListened[$eventClass])) {
                $this->loadListenersFor($eventClass);
            }
            return array_merge($listeners, $this->hashListened[$eventClass]);
        } else {
            // if the event is a class other than Jelix\Event\Event, we must
            // search listeners by event class name
            $eventName = get_class($event);
            if (!isset($this->hashListened[$eventName])) {
                $this->loadListenersFor($eventName);
            }
        }

        return $this->hashListened[$eventName];
    }

    /**
     * List of listeners for each event
     *  key = event name, value = array('moduleName', 'listener class name', 'listener name if class not autoloadable')
     * @var array|null
     */
    protected $listenersList = null;

    /**
     * construct the list of all listeners corresponding to an event.
     *
     * @param string $eventName the event name we want the listeners for
     */
    protected function loadListenersFor($eventName)
    {
        if ($this->listenersList === null) {

            $compiledFile = App::buildPath('listeners.php');
            if (!file_exists($compiledFile)) {
                trigger_error('Compilation of event listeners list failed?', E_USER_WARNING);
                return;
            }

            $this->listenersList = include ($compiledFile);
        }

        $this->hashListened[$eventName] = array();
        if (isset($this->listenersList[$eventName])) {

            // We check disabled listeners at runtime because the list of
            // disabled listeners can change from an entrypoint to another,
            // and the listeners compiler doesn't create the listeners list from
            // a specific entrypoint.
            $disabledListeners = [];
            $allDisabledListeners = $this->config->disabledListeners;
            if (isset($allDisabledListeners[$eventName]) && !empty($allDisabledListeners[$eventName])) {
                $disabledListeners = $allDisabledListeners[$eventName];

                if (!is_array($disabledListeners)) {
                    $disabledListeners = array($disabledListeners);
                }
            }

            foreach ($this->listenersList[$eventName] as $listener) {
                list($listenerClass, $method, $classPath, $selector) = $listener;

                if (in_array($selector, $disabledListeners)) {
                    continue;
                }

                if (!isset($this->listenersSingleton[$listenerClass])) {
                    if ($classPath) {
                        require_once $classPath;
                    }
                    $this->listenersSingleton[$listenerClass] = new $listenerClass();
                }
                $this->hashListened[$eventName][] = $this->listenersSingleton[$listenerClass]->$method(...);
            }
        }
    }
}