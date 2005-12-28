<?php
/**
* @package    jelix
* @subpackage events
* @version    $Id:$
* @author     Croes Gérald
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Classe orginellement issue du framework Copix 2.3dev20050901. http://www.copix.org (CopixListener)
* Une grande partie du code est sous Copyright 2001-2005 CopixTeam
* Auteur initial : Laurent Jouanneau
* Adaptée pour Jelix par Laurent Jouanneau
*/

/**
* base class for event listeners
*/
class jEventListener {
   /**
   * perform a given event
   * @param jEvent $event the event itself
   * @return void
   */
   function performEvent (& $event) {
      $methodName = 'on'.$event->getName ();
      $this->$methodName ($event);
   }
}
?>