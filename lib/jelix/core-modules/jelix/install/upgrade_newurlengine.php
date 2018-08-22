<?php
/**
* @package     jelix
* @subpackage  core-module
* @author      Laurent Jouanneau
* @copyright   2016-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require(__DIR__.'/UrlEngineUpgrader.php');

class jelixModuleUpgrader_newurlengine extends jInstallerModule2 {

    protected $targetVersions = array('1.7.0-beta.1');
    protected $date = '2016-06-19 11:05';

    function install() {

        foreach($this->getEntryPointsList() as $entryPoint) {
            $upgraderUrl = new UrlEngineUpgrader($entryPoint->getAppConfigIni(),
                                                 $entryPoint->getEpId(),
                                                 $entryPoint->getUrlMap());
            $upgraderUrl->upgrade();
        }
    }

    function postInstall() {
        foreach($this->getEntryPointsList() as $entryPoint) {
            $upgraderUrl = new UrlEngineUpgrader($entryPoint->getAppConfigIni(),
                                                 $entryPoint->getEpId(),
                                                 $entryPoint->getUrlMap());
            $upgraderUrl->cleanConfig($this->getConfigIni()['main']);
        }
    }
}


