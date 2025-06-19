<?php
/**
 * @author   Gérald Croes, Patrice Ferlet, Laurent Jouanneau, Dominique Papin, Steven Jehannet
 *
 * @copyright 2001-2005 CopixTeam, 2005-2023 Laurent Jouanneau, 2009 Dominique Papin
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Event;


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

    protected $config;

    public function __construct(object $jelixConfig)
    {
        $this->config = $jelixConfig;
    }


    public function getListenersForEvent(object $event) : iterable
    {
        if ($event instanceof EventInterface) {
            $eventName = $event->getName();
        } else {
            $eventName = get_class($event);
        }

        if (!isset($this->hashListened[$eventName])) {
            $this->loadListenersFor($eventName);
        }

        return $this->hashListened[$eventName];
    }

    protected $compilerData = array(
        'jEventCompiler',
        'events/jEventCompiler.class.php',
        'events.xml',
        'events.php',
    );

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
            $compilerData = $this->compilerData;
            $compilerData[3] = $this->config->urlengine['urlScriptId'] . '.' . $compilerData[3];
            $this->listenersList = \jIncluder::incAll($compilerData, false, $this->config);
            if ($this->listenersList === null) {
                trigger_error('Compilation of event listeners list failed?', E_USER_WARNING);
                return;
            }
        }

        $this->hashListened[$eventName] = array();
        if (isset($this->listenersList[$eventName])) {
            $modules = & $this->config->_modulesPathList;
            $me = $this;
            foreach ($this->listenersList[$eventName] as $listener) {
                list($module, $listenerClass, $oldListenerName) = $listener;
                if (!isset($modules[$module])) {  // some modules could be unused
                    continue;
                }
                if (!isset($this->listenersSingleton[$module][$listenerClass])) {
                    if ($oldListenerName) {
                        require_once $modules[$module] . 'classes/' . $oldListenerName . '.listener.php';
                    }
                    $this->listenersSingleton[$module][$listenerClass] = new $listenerClass();
                }
                $this->hashListened[$eventName][] = function($event) use($me, $module, $listenerClass) {
                    $me->listenersSingleton[$module][$listenerClass]->performEvent($event);
                };
            }
        }
    }
}