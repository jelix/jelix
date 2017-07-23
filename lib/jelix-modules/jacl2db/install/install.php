<?php
/**
* @package     jelix
* @subpackage  jacl2db module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * parameters for this installer
 *    - defaultgroups    add default groups admin, users, anonymous
 *    - defaultuser      add a default user, admin and add default groups
 */
class jacl2dbModuleInstaller extends jInstallerModule2 {


    protected $defaultDbProfile = 'jacl2_profile';

    function installEntrypoint(\Jelix\Installer\EntryPoint $entryPoint) {
        if ($entryPoint->isCliScript())
            return;

        if (!$this->firstDbExec())
            return;

        $this->declareDbProfile('jacl2_profile', null, false);
        $config = $entryPoint->getConfigIni();
        $driver = $config->getValue('driver','acl2');
        if ($driver != 'db') {
            $config->setValue('driver','db','acl2');
        }
        $this->execSQLScript('install_jacl2.schema');

        $this->execSQLScript('data.sql');

        if ($this->getParameter('defaultuser') || $this->getParameter('defaultgroups')) {
            // declare some groups
            $this->execSQLScript('groups.sql');
        }

        if ($this->getParameter('defaultuser')) {
            $this->execSQLScript('user.sql');
        }
    }
}
