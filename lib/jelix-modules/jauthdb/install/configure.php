<?php
/**
 * @package     jelix
 * @subpackage  jauthdb
 *
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jauthdbModuleConfigurator extends \Jelix\Installer\Module\Configurator
{
    public function getDefaultParameters()
    {
        return array(
            'defaultuser' => true,
        );
    }

    public function configure(Jelix\Installer\Module\API\ConfigurationHelpers $helpers)
    {
        $this->parameters['defaultuser'] = $helpers->cli()
            ->askConfirmation(
                'Do you want to create an "admin" user in authdb?',
                $this->parameters['defaultuser']
            )
        ;
    }
}
