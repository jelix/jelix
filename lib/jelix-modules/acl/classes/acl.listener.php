<?php
/**
* @package     jelix-modules
* @subpackage  acl
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor neolao
* @copyright   2006 Jouanneau laurent, neolao
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class ListenerAcl extends jEventListener{

   function onFetchXulOverlay($event){
        if($event->getParam('tpl') == 'xulapp~main'){
            $event->Add('acl~xuladmin_xaovlay');
        }
   }

   /**
    * Création d'un nouvel utilisateur
    *
    * @var jEvent $event L'événement
    */
   function onAuthNewUser($event){
        $user = $event->getParam('user');
        jAclUserGroup::createUser($user->login);
   }

   /**
    * Suppression d'un utilisateur
    *
    * @var jEvent $event L'événement
    */
   function onAuthRemoveUser($event){
        $login = $event->getParam('login');
        jAclUserGroup::removeUser($login);
   }
}
?>