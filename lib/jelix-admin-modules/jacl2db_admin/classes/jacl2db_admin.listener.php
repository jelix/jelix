<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2008-2012 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
 */
class jacl2db_adminListener extends jEventListener
{
    /**
     * @param mixed $event
     */
    public function onmasteradminGetMenuContent($event)
    {
        if (jAcl2::check('acl.user.view')) {
            $item = new masterAdminMenuItem('usersrights', jLocale::get('jacl2db_admin~acl2.menu.item.rights'), jUrl::get('jacl2db_admin~rights:index'), 30, 'system');
            $item->icon = jApp::urlJelixWWWPath().'design/images/rights.png';
            $event->add($item);
        }
        if (jAcl2::check('acl.group.view')) {
            $item = new masterAdminMenuItem('usersgroups', jLocale::get('jacl2db_admin~acl2.menu.item.groups'), jUrl::get('jacl2db_admin~groups:index'), 20, 'system');
            $item->icon = jApp::urlJelixWWWPath().'design/images/group.png';
            $event->add($item);
        }
    }
}
