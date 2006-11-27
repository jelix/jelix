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

/**
* Listener Factory.
* @package     jelix
* @subpackage  events
*/
class jEventListenerFactory {

    public static $compilerDatas = array('jEventCompiler',
                    'events/jEventCompiler.class.php',
                    'events.xml',
                    'events.php'
                    );

    /**
    * handles the listeners singleton (all listeners will be stored in here)
    *    events are stored by events listened
    * @var array of jListener
    */
    protected static $_listenersSingleton = array ();

    /**
    * hash table for event listened.
    * $_hash['eventName'] = array of events (by reference)
    * @var associative array of object
    */
    protected static $_hashListened = array ();

    private function __construct(){}
    /**
    * instanciation of a listener
    */
    public static function create ($module, $listenerName){

        jIncluder::incAll(jEventListenerFactory::$compilerDatas);
        return self::_createListener ($module, $listenerName);
    }

    /**
    * return the list of all listener corresponding to an event
    * @param string $eventName the event name we wants the listeners for.
    * @return array of objects
    */
    public static function getListenersOf ($eventName) {
        jIncluder::incAll(jEventListenerFactory::$compilerDatas);
        self::_createForEvent ($eventName);
        return self::$_hashListened[$eventName];
    }

    /**
    * Creates listeners for the given eventName
    * @param string eventName the eventName we wants to create the listeners for
    */
    protected static function _createForEvent ($eventName) {
        $inf = & $GLOBALS['JELIX_EVENTS'];
        if (! isset (self::$_hashListened[$eventName])){
            self::$_hashListened[$eventName] = array();
            if(isset($inf[$eventName])){
                foreach ($inf[$eventName] as $listener){
                    self::$_hashListened[$eventName][] =  self::_createListener ($listener[0], $listener[1]);
                }
            }
        }
    }

    /**
    * creates a single listener
    */
    protected static function  _createListener ($module, $listenerName){
        if (! isset (self::$_listenersSingleton[$module][$listenerName])){
            global $gJConfig;
            require_once ($gJConfig->_modulesPathList[$module].'classes/'.strtolower ($listenerName).'.listener.php');
#ifdef ENABLE_OLD_CLASS_NAMING
            $className = $listenerName.'Listener';
            if($gJConfig->enableOldClassNaming && !class_exists($className,false)){
                $className = 'Listener'.$listenerName;
            }
#else
            $className = $listenerName.'Listener';
#endif
            self::$_listenersSingleton[$module][$listenerName] =  new $className ();
        }
        return self::$_listenersSingleton[$module][$listenerName];
    }
}
?>