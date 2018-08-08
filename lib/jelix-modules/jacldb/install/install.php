<?php
/**
* @package     jelix
* @subpackage  jacldb module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jacldbModuleInstaller extends jInstallerModule2 {

    protected $defaultDbProfile = 'jacl_profile';

    function install() {

        $this->declareDbProfile('jacl_profile', null, false);
        $config = $this->getLocalConfigIni();
        $driver = $config->getValue('driver','acl');
        if ($driver != 'db') {
            $config['main']->setValue('driver', 'db', 'acl');
        }

        $this->execSQLScript('install_jacl.schema');
        try {
            $this->execSQLScript('install_jacl.data');
        }
        catch (Exception $e) {
        }
    }
}
