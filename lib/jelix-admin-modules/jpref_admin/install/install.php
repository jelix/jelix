<?php
/**
 * @package     jelix
 * @subpackage  jpref_admin
 *
 * @author    Florian Lonqueu-Brochard
 * @contributor Laurent Jouanneau
 *
 * @copyright 2011 Florian Lonqueu-Brochard
 * @copyright 2018 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
class jpref_adminModuleInstaller extends \Jelix\Installer\Module\Installer
{
    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        jAcl2DbManager::createRightGroup('jprefs.prefs.management', 'jpref_admin~admin.acl.grp.prefs.management');
        jAcl2DbManager::createRight('jprefs.prefs.list', 'jpref_admin~admin.acl.prefs.list', 'jprefs.prefs.management');
        jAcl2DbManager::addRight('admins', 'jprefs.prefs.list'); // for admin group
    }
}
