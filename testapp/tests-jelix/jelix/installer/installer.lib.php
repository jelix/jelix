<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2017 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.2
*/

require_once(JELIX_LIB_PATH.'installer/jInstaller.class.php');

class testInstallerGlobalSetup extends \Jelix\Installer\GlobalSetup {

    public $configContent = array();

    function __construct ($projectXmlFileName = null,
                          $mainConfigFileName = null,
                          $localConfigFileName = null,
                          $urlXmlFileName = null
    ) {
        foreach(array(
            'index', 'rest', 'soap', 'jsonrpc', 'xmlrpc', 'cmdline'
                ) as $epName
        ) {
            $this->configContent[$epName.'/config.ini.php'] = array(
                'dbProfils'=>"default",
                "disableInstallers"=>false,
                "enableAllModules"=>false,
                'modules'=>array(
                ),
                'urlengine'=>array('urlScriptId'=>$epName,
                    'urlScript'=>"/$epName.php",
                    'urlScriptPath'=>"/",
                    'urlScriptName'=>"$epName.php",
                    'urlScriptId'=>"$epName",
                    'urlScriptIdenc'=>"$epName"
                ),
                '_allModulesPathList'=>array(
                ),
                '_allBasePath'=>array(
                    0=>"/app/lib/jelix-modules/",
                    1=>"/app/testapp/modules/",
                    2=>"/app/lib/jelix-plugins/cache/",
                ),
                '_modulesPathList'=>array(
                ),
            );
        }
        parent::__construct($projectXmlFileName,
            $mainConfigFileName,
            $localConfigFileName,
            $urlXmlFileName
        );

    }


    function setInstallerIni($installerIni) {
        $this->installerIni = $installerIni;
    }

    function setProjectXml($projectXml) {
        $this->readEntryPointData(simplexml_load_string($projectXml));
        $this->readModuleInfos();
    }

    protected function createEntryPointObject($configFile, $file, $type) {
        return new testInstallerEntryPoint($this,
            $configFile, $file, $type,
            (object) $this->configContent[$configFile]);
    }

    protected function createComponentModule($name, $path) {
        $moduleSetupList = $this->mainEntryPoint->getConfigObj()->modules;
        $moduleInfos = new \Jelix\Installer\ModuleStatus($name, $path, $moduleSetupList);

        if (in_array($name, array('jelix','jacl', 'jacl2db','jacldb','jauth','jauthdb','jsoap'))) {
            return new \Jelix\Installer\ModuleInstallerLauncher($moduleInfos, $this);
        }
        else {
            return new testInstallerComponentModule($moduleInfos, $this);
        }
    }
}


class testInstallerComponentModule extends \Jelix\Installer\ModuleInstallerLauncher {

    protected function readIdentity() {
        $xml = simplexml_load_string($this->mainInstaller->moduleXMLDesc[$this->name]);
        $this->sourceVersion = (string) $xml->info[0]->version[0];
        $this->readDependencies($xml);
    }

}

class testInstallerEntryPoint extends \Jelix\Installer\EntryPoint {

    function __construct($globalSetup,
                         $epConfigFile, $file, $type, $configContent) {
        $this->type = $type;
        $this->_isCliScript = ($type == 'cmdline');
        $this->scriptName =  ($this->isCliScript()?$file:'/'.$file);
        $this->file = $file;
        $this->globalSetup = $globalSetup;

        if (!is_object($epConfigFile)) {
            $epConfigFile = new testInstallerIniFileModifier($epConfigFile);
        }
        $localEpConfigIni = new testInstallerIniFileModifier($epConfigFile->getFileName());


        $this->configFile = $epConfigFile->getFileName();
        $this->configIni = clone $globalSetup->getConfigIni();
        $this->configIni['entrypoint'] = $epConfigFile;

        $this->localConfigIni = clone $this->configIni;
        $this->localConfigIni['local'] = $globalSetup->getLocalConfigIni()['local'];
        $this->localConfigIni['localentrypoint'] = $localEpConfigIni;

        $this->liveConfigIni = clone $this->localConfigIni;
        $this->liveConfigIni['live'] = new testInstallerIniFileModifier(jApp::varConfigPath('localconfig.ini.php'));

        $this->config = $configContent;
    }
    
    function getEpId() {
        return str_replace('.php', '', $this->file);
    }
}

/**
 *
 */
class testInstallReporter implements jIInstallReporter {
    use jInstallerReporterTrait;

    public $startCounter = 0;

    public $endCounter = 0;
    
    public $messages = array();

    function start() {
        $this->startCounter ++;
    }

    function message($message, $type='') {
        $this->addMessageType($type);
        $this->messages[] = array($message, $type);
    }

    function end() {
        $this->endCounter ++;
    }
}


/**
 * ini file modifier without file load/save supports
 */
class testInstallerIniFileModifier extends \Jelix\IniFile\IniModifier {

    function __construct($filename) {}

    public function save($chmod=null) {
        $this->modified = false;
    }

    public function saveAs($filename) {}
}

/**
 * mockup class for Jelix\Installer\Installer
 */
class testInstallerMain extends \Jelix\Installer\Installer {

    public $moduleXMLDesc = array();

    function __construct ($reporter) {
        $this->reporter = $reporter;

        copy (jApp::appConfigPath('urls.xml'), jApp::tempPath('installer_urls.xml'));
        $this->globalSetup = new testInstallerGlobalSetup(null, null, null, jApp::tempPath('installer_urls.xml'));

        $this->messages = new jInstallerMessageProvider('en');
        $nativeModules = array('jelix','jacl', 'jacl2db','jacldb','jauth','jauthdb','jsoap');
        $config = jApp::config();
        foreach ($this->globalSetup->configContent as $ep=>$conf) {
            
            foreach($nativeModules as $module) {
                $this->globalSetup->configContent[$ep]['modules'][$module.'.enabled'] = ($module == 'jelix');
                $this->globalSetup->configContent[$ep]['modules'][$module.'.dbprofile'] = 'default';
                $this->globalSetup->configContent[$ep]['modules'][$module.'.installed'] = 0;
                $this->globalSetup->configContent[$ep]['modules'][$module.'.version'] = jFramework::version();
                $this->globalSetup->configContent[$ep]['_modulesPathList'][$module] = $config->_modulesPathList[$module];
                $this->globalSetup->configContent[$ep]['_allModulesPathList'][$module] = $config->_modulesPathList[$module];
            }
        }

    }

    function testAddModule($name, $moduleXML, $enabled = false, $installed = 0, $version = '1.0', $dbprofile='default') {
        $this->moduleXMLDesc[$name] = $moduleXML;
        foreach($this->globalSetup->configContent as $ep=>$conf) {
            $this->globalSetup->configContent[$ep]['_allModulesPathList'][$name] = "/app/test/modules/$name/";
            $this->globalSetup->configContent[$ep]['_modulesPathList'][$name] = "/app/test/modules/$name/";
            $this->globalSetup->configContent[$ep]['modules'][$name.'.enabled'] = $enabled;
            $this->globalSetup->configContent[$ep]['modules'][$name.'.dbprofile'] = $dbprofile;
            $this->globalSetup->configContent[$ep]['modules'][$name.'.installed'] = $installed;
            $this->globalSetup->configContent[$ep]['modules'][$name.'.version'] = $version;
        }   
    }

    function initForTest($projectXml='<entry file="index.php" config="index/config.ini.php" />') {

        $projectXml = '<?xml version="1.0" encoding="iso-8859-1"?>
<project xmlns="http://jelix.org/ns/project/1.0">
    <info id="test@jelix.org" name="test">
        <version stability="stable" date="">1.0</version>
        <label lang="en_US">Test</label>
        <description lang="en_US">Application to test Jelix</description>
        <copyright>2009 the company</copyright>
        <creator name="Me" email="me@jelix.org" active="true" />
    </info>
    <dependencies>
        <jelix minversion="'.jFramework::version().'" maxversion="'.jFramework::version().'" />
    </dependencies>
    <entrypoints>'.$projectXml.'
    </entrypoints>
</project>';

        $this->globalSetup->setInstallerIni(new testInstallerIniFileModifier(''));
        $this->globalSetup->setProjectXml($projectXml);
        $this->globalSetup->getInstallerIni()->save();
    }
}
