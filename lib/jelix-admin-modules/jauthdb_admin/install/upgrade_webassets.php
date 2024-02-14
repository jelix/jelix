<?php
/**
 * @package     jelix
 * @subpackage  jauthdb_admin
 * 
 * @author      Laurent Jouanneau
 * @copyright   2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jauthdb_adminModuleUpgrader_webassets extends \Jelix\Installer\Module\Installer
{
    protected $targetVersions = array('1.8.0-alpha.1');

    protected $date = '2022-06-02';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $helpers->declareGlobalWebAssets(
            'jauthdb_admin',
            array(
                //'css' => array('$jelix/design/jauthdb_admin.css'),
                'js' => array(
                    '$jelix/js/authdb_admin.js'
                ),
                'require' => array(
                    'jquery_ui'
                )
            ),
            'common',
            false
        );
    }
}
