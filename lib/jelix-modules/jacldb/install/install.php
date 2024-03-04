<?php
/**
 * @package     jelix
 * @subpackage  jacldb
 *
 * @author      Laurent Jouanneau
 * @contributor
 *
 * @copyright   2009-2012 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jacldbModuleInstaller extends \Jelix\Installer\Module\Installer
{
    protected $defaultDbProfile = 'jacl_profile';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $helpers->database()->execSQLScript('install_jacl.schema');

        try {
            $helpers->database()->execSQLScript('install_jacl.data');
        } catch (Exception $e) {
        }
    }
}
