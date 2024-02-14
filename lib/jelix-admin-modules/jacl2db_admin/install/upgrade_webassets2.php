<?php
/**
 * @package     jelix
 * @subpackage  jacl2db_admin
 *
 * @author      Laurent Jouanneau
 * @copyright   2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jacl2db_adminModuleUpgrader_webassets2 extends \Jelix\Installer\Module\Installer
{
    protected $targetVersions = array('1.8.0-alpha.1');

    protected $date = '2022-06-28';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $helpers->declareGlobalWebAssets(
            'jacl2_admin',
            array(
                'css' => array('$jelix/design/jacl2.css'),
                'js' => array(
                    '$jelix/js/jacl2db_admin.js'
                ),
                'require' => array(
                    'jquery_ui'
                )
            ),
            'common',
            true
        );
    }
}
