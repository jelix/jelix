<?php
/**
 * @package     jelix
 * @subpackage  jacl2db
 *
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jacl2dbModuleConfigurator extends \Jelix\Installer\Module\Configurator
{
    public function getDefaultParameters()
    {
        return array(
            'defaultgroups' => true,
            'defaultuser' => true,
        );
    }

    public function configure(Jelix\Installer\Module\API\ConfigurationHelpers $helpers)
    {
        $this->parameters['defaultgroups'] = $helpers->cli()
            ->askConfirmation(
                'Do you want to setup default "admins" and "users" groups in acl2?',
                $this->parameters['defaultgroups']
            )
        ;
        $this->parameters['defaultuser'] = $helpers->cli()
            ->askConfirmation(
                'Do you want to setup default "admin" user in acl2?',
                $this->parameters['defaultuser']
            )
        ;

        $config = $helpers->getConfigIni();
        $driver = $config->getValue('driver', 'acl2');
        if ($driver != 'db') {
            $config->setValue('driver', 'db', 'acl2');
        }
    }

    public function localConfigure(Jelix\Installer\Module\API\LocalConfigurationHelpers $helpers)
    {
        $helpers->declareDbProfile('jacl2_profile', null, false);
    }
}
