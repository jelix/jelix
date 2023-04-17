<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2022-2023 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jacl2db_adminModuleUpgrader_webassets3 extends \Jelix\Installer\Module\Installer
{
    protected $targetVersions = array('1.8.0-rc.5');

    protected $date = '2023-03-21';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        // reapply web assets because of bad apply from
        // jacl2db_adminModuleUpgrader_webassets2 may not be applied with Jelix < 1.8.0-rc.5

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
