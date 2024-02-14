<?php
/**
 * @package     jelix
 * @subpackage  jacl2db_admin
 *
 * @author      Laurent Jouanneau
 * @copyright   2017-2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jacl2db_adminModuleUpgrader_webassets extends \Jelix\Installer\Module\Installer
{
    protected $targetVersions = array('1.7.0-beta.2');

    protected $date = '2017-02-07 19:18';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $helpers->declareGlobalWebAssets('jacl2_admin', array(
            'css' => array('$jelix/design/jacl2.css'),
            'js' => array(
                '$jelix/jquery/jquery.min.js',
                '$jelix/jquery/ui/jquery-ui.min.js'
            )), 'common', true);
    }
}
