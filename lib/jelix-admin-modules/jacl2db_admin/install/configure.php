<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jacl2db_adminModuleConfigurator extends \Jelix\Installer\Module\Configurator
{
    public function configure(Jelix\Installer\Module\API\ConfigurationHelpers $helpers)
    {
        $helpers->declareGlobalWebAssets(
            'jacl2_admin',
            array('css' => array('$jelix/design/jacl2.css'), 'js' => array('$jelix/jquery/jquery.min.js')),
            'common',
            false
        );
    }
}
