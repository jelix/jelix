<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2012 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.2
*/

require_once(JELIX_LIB_PATH.'installer/jInstaller.class.php');

class testInstallerGlobalSetup extends jInstallerGlobalSetup {

    function setInstallerIni($installerIni) {
        $this->installerIni = $installerIni;
    }

}


class testInstallerComponentModule extends jInstallerComponentModule {

    protected function readIdentity() {
        $xml = simplexml_load_string($this->mainInstaller->moduleXMLDesc[$this->name]);
        $this->sourceVersion = (string) $xml->info[0]->version[0];
        $this->readDependencies($xml);
    }

}

class testInstallerEntryPoint extends jInstallerEntryPoint2 {

    function __construct($globalSetup,
                         $configFile, $file, $type, $configContent) {
        $this->type = $type;
        $this->globalSetup = $globalSetup;
        $this->_isCliScript = ($type == 'cmdline');
        
        if (is_object($configFile)) {
            $this->epConfigIni = $configFile;
            $this->localEpConfigIni = new testInstallerIniFileModifier($configFile->getFileName());
            $this->configFile = $configFile->getFileName();
        }
        else {
            $this->epConfigIni = new testInstallerIniFileModifier($configFile);
            $this->localEpConfigIni = new testInstallerIniFileModifier($configFile);
            $this->configFile = $configFile;
        }

        $this->fullConfigIni = new \Jelix\IniFile\MultiIniModifier(
            $globalSetup->getLocalConfigIni(),
            new \Jelix\IniFile\MultiIniModifier($this->epConfigIni, $this->localEpConfigIni));

        $this->scriptName =  ($this->isCliScript()?$file:'/'.$file);
        $this->file = $file;
        $this->config = $configContent;
        $this->mainConfigIni = $globalSetup->getMainConfigIni();
        $this->localConfigIni = $globalSetup->getLocalConfigIni();
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
 * mockup class for jInstaller
 */
class testInstallerMain extends jInstaller {

    public $moduleXMLDesc = array();

    public $configContent = array(
        'index/config.ini.php'=> array(
            'dbProfils'=>"default",
            "disableInstallers"=>false,
            "enableAllModules"=>false,
            'modules'=>array(
            ),
            'urlengine'=>array('urlScriptId'=>'index',
                'urlScript'=>"/index.php",
                'urlScriptPath'=>"/",
                'urlScriptName'=>"index.php",
                'urlScriptId'=>"index",
                'urlScriptIdenc'=>"index"
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
        ),
    );

    function __construct ($reporter) {
        $this->reporter = $reporter;

        copy (jApp::appConfigPath('urls.xml'), jApp::tempPath('installer_urls.xml'));
        $this->globalSetup = new testInstallerGlobalSetup(null, null, jApp::tempPath('installer_urls.xml'));

        $this->messages = new jInstallerMessageProvider('en');
        $nativeModules = array('jelix','jacl', 'jacl2db','jacldb','jauth','jauthdb','jsoap');
        $config = jApp::config();
        foreach ($this->configContent as $ep=>$conf) {
            
            foreach($nativeModules as $module) {
                $this->configContent[$ep]['modules'][$module.'.access'] = ($module == 'jelix'?2:0);
                $this->configContent[$ep]['modules'][$module.'.dbprofile'] = 'default';
                $this->configContent[$ep]['modules'][$module.'.installed'] = 0;
                $this->configContent[$ep]['modules'][$module.'.version'] = JELIX_VERSION;
                $this->configContent[$ep]['_modulesPathList'][$module] = $config->_modulesPathList[$module];
                $this->configContent[$ep]['_allModulesPathList'][$module] = $config->_modulesPathList[$module];
            }
        }

    }

    function testAddModule($name, $moduleXML, $access = 2, $installed = 0, $version = '1.0', $dbprofile='default') {
        $this->moduleXMLDesc[$name] = $moduleXML;
        foreach($this->configContent as $ep=>$conf) {
            $this->configContent[$ep]['_allModulesPathList'][$name] = "/app/test/modules/$name/";
            $this->configContent[$ep]['_modulesPathList'][$name] = "/app/test/modules/$name/";
            $this->configContent[$ep]['modules'][$name.'.access'] = $access;
            $this->configContent[$ep]['modules'][$name.'.dbprofile'] = $dbprofile;
            $this->configContent[$ep]['modules'][$name.'.installed'] = $installed;
            $this->configContent[$ep]['modules'][$name.'.version'] = $version;
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
        <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
    </dependencies>
    <entrypoints>'.$projectXml.'
    </entrypoints>
</project>';

        $this->globalSetup->setInstallerIni(new testInstallerIniFileModifier(''));
        $this->readEntryPointData(simplexml_load_string($projectXml));
        $this->globalSetup->getInstallerIni()->save();
    }

    protected function getEntryPointObject($configFile, $file, $type) {
        return new testInstallerEntryPoint($this->globalSetup,
                                           $configFile, $file, $type,
                                           (object) $this->configContent[$configFile]);
    }
    
    protected function getComponentModule($name, $path, jInstallerGlobalSetup $setup) {
        if (in_array($name, array('jelix','jacl', 'jacl2db','jacldb','jauth','jauthdb','jsoap'))) {
            return new jInstallerComponentModule($name, $path, $setup);
        }
        else {
            return new testInstallerComponentModule($name, $path, $setup);
        }
    }
    
}
