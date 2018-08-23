<?php
/**
 * @package     jelix
 * @subpackage  jauthdb
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class jauthdbModuleConfigurator extends jInstallerModuleConfigurator {

    public function getDefaultParameters()
    {
        return array(
            'defaultuser' => true
        );
    }

    public function askParameters()
    {
        $this->parameters['defaultuser'] = $this->askConfirmation('Do you want to create an "admin" user in authdb?', true);
    }

    public function configure() {

    }

}