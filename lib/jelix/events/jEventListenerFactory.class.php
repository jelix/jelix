<?php
/**
* @package     jelix
* @subpackage  events
* @version     $Id:$
* @author      Croes Gérald, Patrice Ferlet
* @contributor Laurent Jouanneau
* @copyright 2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Classe orginellement issue du framework Copix 2.3dev20050901. http://www.copix.org (CopixListenerFactory)
* Une partie du code est sous Copyright 2001-2005 CopixTeam
* Auteurs initiaux : Croes Gérald, Patrice Ferlet
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*
*/
require_once (JELIX_LIB_EVENTS_PATH . 'jEventListener.class.php');

/**
* Listener Factory.
*/
class jEventListenerFactory {
    /**
    * handles the listeners singleton (all listeners will be stored in here)
    *    events are stored by events listened
    * @var array of CopixListener
    */
    var $_listenersSingleton = array ();

    /**
    * hash table for evet listened.
    * $_hash['eventName'] = array of events (by reference)
    * @var associative array of object
    */
    var $_hashListened = array ();

    /**
    * instanciation of a listener
    */
    function create ($module, $listenerName){
        $me = jEventListenerFactory::instance ();
        jIncluder::incAll(jIncluder::EVENTS());
        return $me->_createListener ($module, $listenerName);
    }

    /**
    * return the list of all listener corresponding to an event
    * @param string $eventName the event name we wants the listeners for.
    * @return array of objects
    */
    function getListenersOf ($eventName) {
        $me = jEventListenerFactory::instance ();
        jIncluder::incAll(jIncluder::EVENTS());
        $me->_createForEvent ($eventName);

        return $me->_hashListened[$eventName];
    }

    /**
    * singleton
    * @return CopixListenerFactory.
    */
    function & instance () {
        static $me = false;
        if ($me === false) {
            $me = new jEventListenerFactory ();
        }
        return $me;
    }

    /**
    * Creates listeners for the given eventName
    * @param string eventName the eventName we wants to create the listeners for
    */
    function _createForEvent ($eventName) {
        $inf = & $GLOBALS['JELIX_EVENTS'];
        if (! isset ($this->_hashListened[$eventName])){
            $this->_hashListened[$eventName] = array();
            if(isset($inf[$eventName])){
                foreach ($inf[$eventName] as $listener){
                    $this->_hashListened[$eventName][] = & $this->_createListener ($listener[0], $listener[1]);
                }
            }
        }
    }

    /**
    * creates a single listener
    */
    function  _createListener ($module, $listenerName){
        if (! isset ($this->_listenersSingleton[$module][$listenerName])){
            global $gJCoord;
            require_once ($gJCoord->modulePathList[$this->module].'classes/'.strtolower ($listenerName).'.listener.php');
            $className = 'Listener'.$listenerName;
            $this->_listenersSingleton[$module][$listenerName] = & new $className ();
        }
        return $this->_listenersSingleton[$module][$listenerName];
    }
}
?>