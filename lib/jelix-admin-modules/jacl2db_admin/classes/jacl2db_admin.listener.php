<?php
/**
* @package     jelix_admin_modules
* @subpackage  jacl2db_admin
* @author    Laurent Jouanneau
* @copyright 2008 Laurent Jouanneau
* @link        http://jelix.org
* @licence     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
*/

class jacl2db_adminListener extends jEventListener{

   /**
   *
   */
   function onmasteradminGetMenuContent ($event) {
        $event->add(new masterAdminMenuItem('usersrights', jLocale::get('jacl2db_admin~acl2.menu.item.rights'), jUrl::get('jacl2db_admin~users:index'), 30, 'system'));
        $event->add(new masterAdminMenuItem('usersgroups', jLocale::get('jacl2db_admin~acl2.menu.item.groups'), jUrl::get('jacl2db_admin~groups:index'), 20, 'system'));
   }
}
