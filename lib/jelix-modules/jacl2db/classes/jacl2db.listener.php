<?php
/**
* @package     jelix-modules
* @subpackage  jacl2db
* @author      Jouanneau Laurent
* @contributor Bastien Jaillot
* @copyright   2008 Jouanneau laurent, 2008 Bastien Jaillot
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
* @since 1.1
*/

/**
 * @package     jelix-modules
 * @subpackage  jacl2db
 * @since 1.1
 */
class jacl2dbListener extends jEventListener{

   /**
    * Called when a user is created : set up default rights on this user
    *
    * @param jEvent $event   the event
    */
   function onAuthNewUser($event){
        if($GLOBALS['gJConfig']->acl2['enableAcl2DbEventListener']) {
            $user = $event->getParam('user');
            jAcl2DbUserGroup::createUser($user->login);
        }
   }

   /**
    * Called when a user has been removed : delete rights about this user
    *
    * @param jEvent $event   the event
    */
   function onAuthRemoveUser($event){
        if($GLOBALS['gJConfig']->acl2['enableAcl2DbEventListener']) {
            $login = $event->getParam('login');
            jAcl2DbUserGroup::removeUser($login);
        }
   }
}
