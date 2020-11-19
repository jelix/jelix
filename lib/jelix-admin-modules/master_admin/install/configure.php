<?php
/**
 * @package     jelix
 * @subpackage  master_admin
 *
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class master_adminModuleConfigurator extends \Jelix\Installer\Module\Configurator
{
    public function configure(Jelix\Installer\Module\API\ConfigurationHelpers $helpers)
    {
        $helpers->declareGlobalWebAssets(
            'master_admin',
            array('css' => array('$jelix/design/master_admin.css')),
            'common',
            false
        );
    }
}
