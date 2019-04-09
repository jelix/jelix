<?php
/**
 * @package     jelix_admin_modules
 * @subpackage  jpref_admin
 *
 * @author    Florian Lonqueu-Brochard
 * @copyright 2012 Florian Lonqueu-Brochard
 *
 * @see        http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
class jpref_adminListener extends jEventListener
{
    /**
     * @param mixed $event
     */
    public function onmasteradminGetMenuContent($event)
    {
        if (jAcl2::check('jprefs.prefs.list')) {
            $item = new masterAdminMenuItem('pref', jLocale::get('jpref_admin~admin.item.title'), jUrl::get('jpref_admin~prefs:index'), 50, 'system');
            $item->icon = jApp::urlJelixWWWPath().'design/images/cog.png';
            $event->add($item);
        }
    }
}
