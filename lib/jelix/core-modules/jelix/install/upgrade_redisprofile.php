<?php

/**
 * @package    jelix-modules
 * @subpackage jelix-module
* @author      Laurent Jouanneau
* @copyright   2017-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jelixModuleUpgrader_redisprofile extends \Jelix\Installer\Module\Installer {

    protected $targetVersions = array('1.6.14pre.3368');
    protected $date = '2017-01-31 18:51';

    function install() {
        $ini = new jIniFileModifier(jApp::configPath('profiles.ini.php'));
        foreach($this->getEntryPointsList() as $entryPoint) {
            foreach($ini->getSectionList() as $section) {
                if (strpos($section, 'jkvdb:') === 0) {
                    $driver = $ini->getValue('driver', $section);
                    if ($driver == 'redis' &&
                        isset ($entryPoint->getConfigObj()->_pluginsPathList_kvdb['redis_php'])
                    ) {
                        $ini->setValue('driver', 'redis_php', $section);
                    }
                }
                else if (strpos($section, 'jcache:') === 0) {
                    $driver = $ini->getValue('driver', $section);
                    if ($driver == 'redis' &&
                        isset ($entryPoint->getConfigObj()->_pluginsPathList_cache['redis_php'])
                    ) {
                        $ini->setValue('driver', 'redis_php', $section);
                    }
                }
            }
        }
        $ini->save();
    }
}