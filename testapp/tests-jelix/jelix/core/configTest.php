<?php

use Jelix\Core\App;
use Jelix\Core\Config\FrameworkConfigCompiler;
use Jelix\Core\Infos\FrameworkInfos;
use Jelix\Core\Infos\ModuleStatusDeclaration;
use Jelix\IniFile\IniModifier;

class fakeConfigCompiler extends FrameworkConfigCompiler {

    protected $installationData;

    public function __construct($installationData, $frameworkIniContent = '')
    {

        $this->installationData = $installationData;
        if ($frameworkIniContent !== '') {
            $fmkInfo = new testFrameworkInfosWithString($frameworkIniContent);
        }
        else {
            $fmkInfo = FrameworkInfos::load();
        }
        parent::__construct(App::app(), $fmkInfo, '');

    }

    protected function readInstallerFile($forInstaller)
    {
        return $this->installationData;
    }

    function test_read_module_info($moduleName, ?ModuleStatusDeclaration $moduleStatusDecl, $installationMode, $path, &$installation) {
        return $this->_readModuleInfo($moduleName, $moduleStatusDecl, $installationMode, $path, $installation);
    }

    function getAllModulesPathsList() {
        return $this->allModulesPathsList;
    }

    function getEnabledModulesPathsList() {
        return $this->enabledModulesPathList;
    }
}

class fakeConfig {

}

class testFrameworkInfosWithString extends FrameworkInfos {

    public function __construct($frameworkFileContent, $localFrameworkFileContent = '')
    {
        $this->iniFile = new IniModifier(jApp::tempPath('framework.test.ini.php'), $frameworkFileContent);
        $this->readIniFile($this->iniFile);

        if ($localFrameworkFileContent != '') {
            $this->iniLocalFile = new IniModifier(jApp::tempPath('localframework.test.ini.php'), $localFrameworkFileContent);
            $this->readIniFile($this->iniLocalFile, true);
        }

        if (!$this->defaultEntryPoint && count($this->entrypoints)) {
            $ep = $this->getEntryPointInfo('index');
            if (!$ep) {
                $epid = array_keys($this->entrypoints)[0];
                $ep = $this->getEntryPointInfo($epid);
            }
            $this->defaultEntryPoint = $ep->getId();
        }
    }

}

class configTest extends \Jelix\UnitTests\UnitTestCase {

    public function setUp() : void
    {
        jApp::saveContext();
        jApp::initPaths(__DIR__.'/app/');
        $tempPath = __DIR__.'/../../../temp/configapp';
        if (!file_exists($tempPath)) {
            mkdir($tempPath);
        }
        jApp::setTempBasePath(realpath($tempPath).'/');
        jApp::clearModulesPluginsPath();
        jApp::declareModulesDir(__DIR__.'/app/modules/');

        //self::initClassicRequest(TESTAPP_URL.'index.php');
        parent::setUp();
    }

    function tearDown() : void
    {
        jApp::restoreContext();
    }


    function testReadModuleInfoOldModuleNotActivated()
    {
        $installation = array('modules'=>array());
        $compiler = new fakeConfigCompiler($installation,
        "
[module:simple]
enabled=on
"
        );
        $fmkConfig = $compiler->compileForRuntime();

        // no data into installer, so not installed, so disable the module
        $this->assertFalse($fmkConfig->isModuleEnabled('simple'));

        $fmkConfig = $compiler->compileForInstaller();
        // For the installation, the module is enabled, so it can be installed
        $this->assertTrue($fmkConfig->isModuleEnabled('simple'));
        $this->assertEquals(array('jelix', 'package', 'complex', 'simple'), array_keys($fmkConfig->getAllModulesPathsList()));
        $this->assertEquals(['jelix', 'simple'], array_keys($fmkConfig->getEnabledModulesPathsList()));
    }

    function testReadModuleInfoOldModuleActivatedNotInstalled()
    {
        $installation = array('modules'=>array());
        $compiler = new fakeConfigCompiler($installation,
            "
[module:simple]
enabled=on
");
        $fmkConfig = $compiler->compileForRuntime();

        // no data into installer, so not installed, so disable the module
        $this->assertFalse($fmkConfig->isModuleEnabled('simple'));

        $fmkConfig = $compiler->compileForInstaller();

        // For the installation, the module is enabled, so it can be installed
        $this->assertTrue($fmkConfig->isModuleEnabled('simple'));
        $this->assertEquals(array('jelix', 'package', 'complex', 'simple'), array_keys($fmkConfig->getAllModulesPathsList()));
        $this->assertEquals(['jelix', 'simple'], array_keys($fmkConfig->getEnabledModulesPathsList()));

    }

    function testReadModuleInfoOldModuleActivatedInstalled()
    {
        $installation = array('modules'=>array('simple.installed'=>1));
        $compiler = new fakeConfigCompiler($installation,
            "
[module:simple]
enabled=on
");
        $fmkConfig = $compiler->compileForRuntime();

        $this->assertTrue($fmkConfig->isModuleEnabled('simple'));

        $fmkConfig = $compiler->compileForInstaller();
        $this->assertTrue($fmkConfig->isModuleEnabled('simple'));
        $this->assertEquals(array('jelix', 'package', 'complex', 'simple'), array_keys($fmkConfig->getAllModulesPathsList()));
        $this->assertEquals(['jelix', 'simple'], array_keys($fmkConfig->getEnabledModulesPathsList()));
    }

    function testReadModuleInfoNewModuleActivatedNotInstalled()
    {
        $installation = array('modules'=>array());
        $compiler = new fakeConfigCompiler($installation,
            "
[module:package]
enabled=on
"
        );
        $fmkConfig = $compiler->compileForRuntime();
        $this->assertFalse($fmkConfig->isModuleEnabled('package'));
    }

    function testReadModuleInfoNewModuleActivatedInstalled() {

        $installation = array('modules'=>array('package.installed'=>1));
        $compiler = new fakeConfigCompiler($installation,
            "
[module:package]
enabled=on
"
        );
        $fmkConfig = $compiler->compileForRuntime();
        $this->assertTrue($fmkConfig->isModuleEnabled('package'));

    }
}
