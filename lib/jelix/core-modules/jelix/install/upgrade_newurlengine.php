<?php
/**
* @package     jelix
* @subpackage  core-module
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require(__DIR__.'/UrlEngineUpgrader.php');

class jelixModuleUpgrader_newurlengine extends jInstallerModule {

    public $targetVersions = array('1.7.0-beta.1');
    public $date = '2016-06-19 11:05';

    function install() {

        $upgraderUrl = new UrlEngineUpgrader($this->config,
                                             $this->entryPoint->getEpId(),
                                             $this->getUrlMap());
        $upgraderUrl->upgrade();
    }

    function postInstall() {
        $upgraderUrl = new UrlEngineUpgrader($this->config,
                                             $this->entryPoint->getEpId(),
                                             $this->getUrlMap());
        
        $mainconfig = $this->entryPoint->getMainConfigIni()->getOverrider();
        $upgraderUrl->cleanConfig($mainconfig);
    }
}


