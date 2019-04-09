<?php
/**
 * @package     jelix
 * @subpackage  jacldb
 *
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jacldbModuleConfigurator extends \Jelix\Installer\Module\Configurator
{
    public function configure(Jelix\Installer\Module\API\ConfigurationHelpers $helpers)
    {
        $config = $helpers->getConfigIni();
        $driver = $config->getValue('driver', 'acl');
        if ($driver != 'db') {
            $config->setValue('driver', 'db', 'acl');
        }
    }

    public function localConfigure(Jelix\Installer\Module\API\LocalConfigurationHelpers $helpers)
    {
        $helpers->declareDbProfile('jacl_profile', null, false);
    }
}
