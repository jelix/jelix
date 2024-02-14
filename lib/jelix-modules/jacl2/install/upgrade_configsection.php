<?php
/**
 * @package     jelix
 * @subpackage  jacl2
 *
 * @author      Laurent Jouanneau
 * @contributor
 *
 * @copyright   2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jacl2ModuleUpgrader_configsection extends \Jelix\Installer\Module\Installer
{
    protected $targetVersions = array('1.8.0-alpha.1');

    protected $date = '2022-06-02';


    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        foreach($helpers->getEntryPointsList() as $ep) {
            $conf = $ep->getConfigIni();

            if ($conf->isSection('jacl2')) {
                $acl2ConfigOrig = $conf->getValues('jacl2');
            }
            else {
                $acl2ConfigOrig = array();
            }
            $acl2config = $acl2ConfigOrig;
            if ($conf->isSection('coordplugin_jacl2')) {
                $acl2config = array_merge($acl2ConfigOrig, $conf->getValues('coordplugin_jacl2'));
            }

            if ($ep->getType() != 'classic') {
                $onerror = 1;
            } else {
                $onerror = 2;
            }
            $acl2config = array_merge(array(
                'on_error'=>$onerror,
                'error_message'=>'jacl2~errors.action.right.needed',
                'on_error_action'=>'jelix~error:badright',
            ), $acl2config);

            if ($acl2config != $acl2ConfigOrig) {
                $conf->setValues($acl2config, 'jacl2');
            }

            $acl2ConfOrig = $conf->getValues('acl2');
            $acl2Conf = array_merge( array(
                'hiddenRights' => '',
                'hideRights' => false,
                'driver' => '',
                'authAdapterClass' => 'jAcl2JAuthAdapter'
            ), $acl2ConfOrig);
            if ($acl2ConfOrig != $acl2Conf) {
                $conf->setValues($acl2Conf, 'acl2');
            }
        }
    }
}
