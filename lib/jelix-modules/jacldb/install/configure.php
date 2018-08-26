<?php
/**
 * @package     jelix
 * @subpackage  jacldb
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class jacldbModuleConfigurator extends \Jelix\Installer\Module\Configurator {


    public function configure() {
        $this->declareDbProfile('jacl_profile', null, false);
        $config = $this->getConfigIni();
        $driver = $config->getValue('driver','acl');
        if ($driver != 'db') {
            $config['main']->setValue('driver', 'db', 'acl');
        }
    }

}