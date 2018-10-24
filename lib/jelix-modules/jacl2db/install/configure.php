<?php
/**
 * @package     jelix
 * @subpackage  jacl2db
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class jacl2dbModuleConfigurator extends \Jelix\Installer\Module\Configurator {

    public function getDefaultParameters()
    {
        return array(
            'defaultgroups' =>true,
            'defaultuser' => true
        );
    }

    public function askParameters()
    {
        $this->parameters['defaultgroups'] = $this->askConfirmation('Do you want to setup default "admins" and "users" groups in acl2?', true);
        $this->parameters['defaultuser'] = $this->askConfirmation('Do you want to setup default "admin" user in acl2?', true);
    }

    public function configure() {
        $this->declareDbProfile('jacl2_profile', null, false);
        $config = $this->getConfigIni();
        $driver = $config->getValue('driver','acl2');
        if ($driver != 'db') {
            $config->setValue('driver','db','acl2');
        }
    }

}