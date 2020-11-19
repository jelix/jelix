<?php
/**
 * @package     jelix
 * @subpackage  jacl2db module
 *
 * @author      Laurent Jouanneau
 * @contributor
 *
 * @copyright   2009-2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * parameters for this installer
 *    - defaultgroups    add default groups admin, users, anonymous
 *    - defaultuser      add a default user, admin and add default groups.
 */
class jacl2dbModuleInstaller extends \Jelix\Installer\Module\Installer
{
    protected $defaultDbProfile = 'jacl2_profile';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {

        /*
        $mapper = new jDaoDbMapper('jacl2_profile');
        $mapper->createTableFromDao("jacl2db~jacl2group");
        $mapper->createTableFromDao("jacl2db~jacl2usergroup");
        $mapper->createTableFromDao("jacl2db~jacl2subjectgroup");
        $mapper->createTableFromDao("jacl2db~jacl2subject");
        $mapper->createTableFromDao("jacl2db~jacl2rights");
        */

        $helpers->database()->execSQLScript('install_jacl2.schema');

        $helpers->database()->insertDaoData('data.json', jDbTools::IBD_INSERT_ONLY_IF_TABLE_IS_EMPTY);

        if ($this->getParameter('defaultuser') || $this->getParameter('defaultgroups')) {
            // declare some groups
            $helpers->database()->insertDaoData('groups.json', jDbTools::IBD_INSERT_ONLY_IF_TABLE_IS_EMPTY);
        }

        if ($this->getParameter('defaultuser')) {
            $helpers->database()->insertDaoData('users.groups.json', jDbTools::IBD_IGNORE_IF_EXIST);
            $helpers->database()->insertDaoData('users.json', jDbTools::IBD_INSERT_ONLY_IF_TABLE_IS_EMPTY);
        }
    }
}
