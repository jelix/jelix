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

class testInstallerGlobalSetup extends \Jelix\Installer\GlobalSetup {

    function setInstallerIni($installerIni) {
        $this->installerIni = $installerIni;
    }

}


/*class testInstallerComponentModule extends jInstallerComponentModule {

    protected function readIdentity() {
        $xml = simplexml_load_string($this->mainInstaller->moduleXMLDesc[$this->name]);
        $this->sourceVersion = (string) $xml->info[0]->version[0];
        $this->readDependencies($xml);
    }

}*/

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

        $compiler = new \Jelix\Core\Config\Compiler($this->configFile,
            $this->scriptName,
            $this->isCliScript());
        $this->config = $compiler->read(true, $configContent);
        $this->modulesInfos = $compiler->getModulesInfos();
    }

    function getEpId() {
        return str_replace('.php', '', $this->file);
    }

    function setModuleData($name, $modInfos) {
        $this->modulesInfos[$name] = $modInfos;
        $this->config->_allModulesPathList[$name]='/';
    }
}

class testInstallerModuleInfos extends \Jelix\Core\Infos\ModuleInfos {

    function __construct($path, $xmlstring) {
        $p = rtrim($path, '/');
        $this->path = $p.'/';
        $this->name = basename($p);

        $config = \Jelix\Core\App::config();
        if ($config) {
            $locale = $config->locale;
        }
        else {
            $locale = '';
        }
        $parser = new testInstallerModuleParser($this->path.'module.xml', 'en');
        $parser->parse2($this, $xmlstring);
    }
}

class testInstallerModuleParser extends \Jelix\Core\Infos\ModuleXmlParser {

    public function parse2(\Jelix\Core\Infos\InfosAbstract $object, $xmlstring){
        $xml = new XMLreader();
        $xml->xml($xmlstring, '', LIBXML_COMPACT);

        while ($xml->read()) {
            if($xml->nodeType == \XMLReader::ELEMENT) {
                $method = 'parse' . ucfirst($xml->name);
                if (method_exists ($this, $method)) {
                    $this->$method($xml, $object);
                }
            }
        }
        $xml->close();
        return $object;
    }
}

/**
 *
 */
class testInstallReporter implements jIInstallReporter {
    use \Jelix\Installer\ReporterTrait;

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

    function __construct($filename) {
        $this->filename = $filename;
    }

    public function save($chmod=null) {
        $this->modified = false;
    }

    public function saveAs($filename) {}
}


class testInstaller extends \Jelix\Installer\Installer {
    protected function createInstallerIni()
    {
        file_put_contents(jApp::tempPath('dummyInstaller.ini'), '');
        return new \Jelix\IniFile\IniModifier(jApp::tempPath('dummyInstaller.ini'));
    }

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

    protected function getEntryPointObject($configFile, $file, $type, $reuseSameConfig) {
        return new testInstallerEntryPoint($this->globalSetup,
                                           $configFile, $file, $type,
                                           (object) $this->configContent[$configFile]);
    }
    
    protected function getComponentModule($name, $path, \Jelix\Installer\GlobalSetup $setup) {
        if (in_array($name, array('jelix','jacl', 'jacl2db','jacldb','jauth','jauthdb','jsoap'))) {
            return new jInstallerComponentModule($name, $path, $setup);
        }
        else {
            return new testInstallerComponentModule2($name, $path, $setup);
        }
    }
    
}
