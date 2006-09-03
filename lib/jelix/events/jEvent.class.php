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
* Classe orginellement issue du framework Copix 2.3dev20050901. http://www.copix.org (CopixEvent, CopixEventNotifier, CopixEventResponse)
* Une partie du code est sous Copyright 2001-2005 CopixTeam
* Auteurs initiaux : Croes Gérald, Patrice Ferlet
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*
*/

/**
 *
 */
require_once (JELIX_LIB_EVENTS_PATH . 'jEventListener.class.php');
require_once (JELIX_LIB_EVENTS_PATH . 'jEventListenerFactory.class.php');

/**
* classe des évènements passés aux listeners
* @package     jelix
* @subpackage  events
*/
class jEvent {
   /**
   * The name of the event.
   * @var string name
   */
   protected $_name = null;

   /**
   * the event parameters
   */
   protected $_params = null;

   /**
   * the listeners list.
   */
   protected static $_listeners = array ();

   /**
   * New event.
   */
   function __construct ($name, $params=array()){
      $this->_name   = $name;
      $this->_params = & $params;
   }

   /**
   * gets the name of the event
   *    will be used internally for optimisations
   */
   public function getName (){
      return $this->_name;
   }

   /**
   * gets the given param
   * @param string $name the param name
   */
   public function getParam ($name){
      if (isset ($this->_params[$name])){
         $ret = $this->_params[$name];
      }else{
         $ret = null;
      }
      return $ret;
   }



   /**
   * send a notification to all modules
   * @param $event string   the event name
   * @return jEvent
   */
   public static function notify ($eventname, $params=array()) {

      $event = new jEvent($eventname, $params);

      if(!isset(jEvent::$_listeners[$eventname])){
          jEvent::$_listeners[$eventname] = jEventListenerFactory::getListenersOf ($eventname);
      }

      if (isset (jEvent::$_listeners[$eventname])){
         foreach (array_keys (jEvent::$_listeners[$eventname]) as $key) {
            jEvent::$_listeners[$eventname][$key]->performEvent ($event);
         }
      }
      return $event;
   }


   /**
    * @var array of array
    */
   protected $_responses = array ();

   /**
    * add a response in the list
    * @param array response a single response
    */
   public function add ($response) {
      $this->_responses[] = & $response;
   }

   /**
    * look in all the responses if we have a parameter having value as its answer
    * eg, we want to know if we have failed = true, we do
    * @param string $responseName the param we're looking for
    * @param mixed $value the value we're looking for
    * @param ref $response the response that have this value
    * @return boolean wether or not we have founded the response value
    */
   public function inResponse ($responseName, $value, & $response){
      $founded  = false;
      $response = array ();

      foreach ($this->_responses as $key=>$listenerResponse){
         if (isset ($listenerResponse[$responseName]) && $listenerResponse[$responseName] == $value){
            $founded = true;
            $response[] = & $this->_responses[$key];
         }
      }

      return $founded;
   }

   /**
    * gets all the responses
    * @return array of associative array
    */
   public function getResponse () {
      return $this->_responses;
   }
}
?>