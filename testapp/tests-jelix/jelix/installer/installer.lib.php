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

use Jelix\Core\App;
use Jelix\Core\Infos\ModuleStatusDeclaration;
use Jelix\Installer\ModuleInstallerLauncher;
use Jelix\Installer\ModuleStatus;

class testInstallerAppInfos extends \Jelix\Core\Infos\AppInfos {

    function save() {
        return true;
    }
}

class testInstallerProjectParser extends \Jelix\Core\Infos\ProjectXmlParser {

    protected function createInfos() {
        return new testInstallerAppInfos($this->path, true);
    }
}

class testFrameworkInfos extends \Jelix\Core\Infos\FrameworkInfos
{
    function save() {
        return true;
    }
}

class testInstallerModuleInfos extends \Jelix\Core\Infos\ModuleInfos {

    function save() {
        return true;
    }
}

class testInstallerModuleParser extends \Jelix\Core\Infos\ModuleXmlParser {

    protected function createInfos() {
        return new testInstallerModuleInfos($this->path, true);
    }

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




class testInstallerGlobalSetup extends \Jelix\Installer\GlobalSetup {

    public $configContent = array();

    function __construct (
        $frameworkFileName = null,
        $localFrameworkFileName = null,
        $mainConfigFileName = null,
        $localConfigFileName = null,
        $urlXmlFileName = null,
        $urlLocalXmlFileName = null
    ) {
        foreach(array(
            'index', 'rest', 'soap', 'jsonrpc', 'xmlrpc', 'newep'
                ) as $epName
        ) {
            $this->configContent[$epName.'/config.ini.php'] = array(
                'dbProfils'=>"default",
                'urlengine'=>array(
                    'urlScript'=>"/$epName.php",
                    'urlScriptPath'=>"/",
                    'urlScriptName'=>"$epName.php",
                    'urlScriptId'=>"$epName",
                    'urlScriptIdenc'=>"$epName"
                ),
            );
        }

        if (!$frameworkFileName) {
            $frameworkFileName = testFrameworkInfos::load();
        }

        parent::__construct($frameworkFileName,
            $localFrameworkFileName,
            $mainConfigFileName,
            $localConfigFileName,
            $urlXmlFileName,
            $urlLocalXmlFileName
        );
    }

    function setInstallerIni($installerIni) {
        $this->installerIni = $installerIni;
    }

    protected function createEntryPointObject($configFile, $file, $type, $isLocalEp) {
        return new testInstallerEntryPoint($this,
            $configFile, $file, $type, $isLocalEp,
            (object) $this->configContent[$configFile]);
    }

    protected function createComponentModule($name, $path)
    {
        $fmkConfig = $this->getFrameworkConfig();
        $moduleStatus = $fmkConfig->getModuleStatus($name);

        $moduleDecl = $this->frameworkInfos->getModule($name);
        if (!$moduleDecl) {
            // module not installed yet
            $scope = $this->forLocalConfiguration() ? ModuleStatus::CONFIG_SCOPE_LOCAL : ModuleStatus::CONFIG_SCOPE_APP;
        }
        else {
            $scope = $moduleDecl->isNative ? ModuleStatus::CONFIG_SCOPE_APP : ModuleStatus::CONFIG_SCOPE_LOCAL;
        }
        $moduleStatus->configurationScope = $scope;

        if (in_array($name, array('jelix','jacl2db','jacldb','jauth','jauthdb','jsoap'))) {
            return new ModuleInstallerLauncher($moduleStatus, $this);
        }
        else {
            return new testInstallerComponentModule($moduleStatus, $this);
        }
    }
}


class testInstallerComponentModule extends \Jelix\Installer\ModuleInstallerLauncher {

}

class testInstallerEntryPoint extends \Jelix\Installer\EntryPoint {

    function __construct(\Jelix\Installer\GlobalSetup $globalSetup,
                         $configFile, $file, $type, $isLocalEp, $configContent) {
        $this->type = $type;
        $this->configFileName = $configFile;
        $this->scriptName = '/'.$file;
        $this->file = $file;
        $this->globalSetup = $globalSetup;
        $this->isLocalEp = $isLocalEp;

        $appSystemPath = App::appSystemPath($configFile);
        $varConfigPath = App::varConfigPath($configFile);

        if (!file_exists($appSystemPath)) {
            \jFile::createDir(dirname($appSystemPath));
            file_put_contents($appSystemPath, ';<' . '?php die(\'\');?' . '>');
        }

        $this->appEpConfigIni = new testInstallerIniFileModifier($appSystemPath);
        $this->localEpConfigIni = new testInstallerIniFileModifier($varConfigPath);
        $this->config = $configContent;
    }

    function getEpId() {
        return str_replace('.php', '', $this->file);
    }
}

/**
 *
 */
class testInstallReporter implements \Jelix\Installer\Reporter\ReporterInterface {
    use \Jelix\Installer\Reporter\ReporterTrait;

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

    function __construct($filename, $initialContent='') {
        $this->filename = $filename;
        if ($initialContent != '') {
            $this->parse(preg_split("/(\r\n|\n|\r)/", $initialContent));
        }
    }

    public function save($chmod=null, $format=0) {
        $this->modified = false;
    }

    public function saveAs($filename, $format=0) {}
}


class testInstaller extends \Jelix\Installer\Installer {
    protected function createInstallerIni()
    {
        file_put_contents(App::tempPath('dummyInstaller.ini'), '');
        return new \Jelix\IniFile\IniModifier(App::tempPath('dummyInstaller.ini'));
    }

}

/**
 * mockup class for Jelix\Installer\Installer
 */
class testInstallerMain extends \Jelix\Installer\Installer {

    public $moduleXMLDesc = array();

    function __construct ($reporter) {
        $this->reporter = $reporter;

        $this->messages = new \Jelix\Installer\Checker\Messages('en');

        copy (jApp::appSystemPath('urls.xml'), App::tempPath('installer_urls.xml'));
        $this->globalSetup = new testInstallerGlobalSetup(null,
            App::varConfigPath('localframework.ini.php.dist')
            , null, null,
            App::tempPath('installer_urls.xml'));

        $nativeModules = array('jelix', 'jacl2db','jauth','jauthdb','jsoap');

        foreach($nativeModules as $moduleName) {

            $module = new ModuleStatusDeclaration($moduleName, array(
                'enabled' => ($moduleName == 'jelix'),
                'dbprofile' => 'default',
                'installed' => 0,
                'version' => jFramework::version(),
            ), true);
            $this->globalSetup->getFrameworkInfos()->addModule($module);
        }
        $this->globalSetup->getFrameworkConfig(true);
    }

    function testAddModule($name, $moduleXML, $enabled = false, $installed = 0, $version = '1.0', $dbprofile='default') {
        $this->moduleXMLDesc[$name] = $moduleXML;

        $module = new ModuleStatusDeclaration($name, array(
            'enabled' => $enabled,
            'dbprofile' => $dbprofile,
            'installed' => $installed,
            'version' => $version,
        ), true);
        $this->globalSetup->getFrameworkInfos()->addModule($module);
        $this->globalSetup->getFrameworkConfig(true);
    }
}
