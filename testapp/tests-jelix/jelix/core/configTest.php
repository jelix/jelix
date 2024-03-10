<?php

class fakeConfigCompiler extends \Jelix\Core\Config\Compiler {

    function test_read_module_info($config, $allModuleInfo, $path, &$installation) {
        $this->_readModuleInfo($config, $allModuleInfo, $path, $installation);
    }
}

class fakeConfig {
    public $modules = array();
    public $_allModulesPathList = array();
    public $_externalModulesPathList = array();
    public $_modulesPathList = array();
    public $pluginsPath = '';
    public $modulesPath = '';
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

    /**
     */
    function testReadModuleInfoUnknowPath() {
        $config = new fakeConfig();
        $modulePath = '/foo/bar';
        $installation = array('modules'=>array());
        $compiler = new fakeConfigCompiler();
        $this->expectException(\Exception::class);
        $compiler->test_read_module_info($config, false, $modulePath, $installation);
        $this->assertEquals(0, count(array_keys($config->modules)));
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
        $this->assertEquals(0, count(array_keys($installation['modules'])));
    }

    /**
     */
    function testReadModuleInfoNotAModule() {
        $config = new fakeConfig();
        $modulePath = __DIR__;
        $installation = array('modules'=>array());
        $compiler = new fakeConfigCompiler();
        $this->expectException(\Exception::class);
        $compiler->test_read_module_info($config, false, $modulePath, $installation);
        $this->assertEquals(0, count(array_keys($config->modules)));
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
        $this->assertEquals(0, count(array_keys($installation['modules'])));
    }

    function testReadModuleInfoOldModuleNotActivated() {
        $config = new fakeConfig();
        $modulePath = realpath(__DIR__.'/app/modules/simple');
        $installation = array('modules'=>array());
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, false, $modulePath, $installation);
        $this->assertEquals(array(
                                'simple.enabled' => 0
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
        $this->assertEquals(array('simple.installed' => 0), $installation['modules']);
    }

    function testReadModuleInfoOldModuleActivatedNotInstalled() {
        $config = new fakeConfig();
        $config->modules = array('simple.enabled'=>1);
        $modulePath = realpath(__DIR__.'/app/modules/simple');
        $installation = array('modules'=>array());
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, false, $modulePath, $installation);
        $this->assertEquals(array('simple.installed' => 0), $installation['modules']);
        $this->assertEquals(array(
                                'simple.enabled' => 0
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
    }

    function testReadModuleInfoOldModuleActivatedInstalled() {
        $config = new fakeConfig();
        $config->modules = array('simple.enabled'=>1);
        $modulePath = realpath(__DIR__.'/app/modules/simple').'/';
        $installation = array('modules'=>array('simple.installed'=>1));
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, false, $modulePath, $installation);
        $this->assertEquals(array('simple.installed' => 1), $installation['modules']);
        $this->assertEquals(array(
                                'simple.enabled' => true,
                                'simple.dbprofile' => 'default'
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));

        // with all modules info
        $config = new fakeConfig();
        $config->modules = array('simple.enabled'=>1);
        $installation = array('modules'=>array('simple.installed'=>1));
        $compiler->test_read_module_info($config, true, $modulePath, $installation);
        $this->assertEquals(array('simple.installed' => 1,
                                    'simple.version' => '',
                                    'simple.dataversion' => ''
                                  ), $installation['modules']);
        $this->assertEquals(array(
                                'simple.enabled' => true,
                                'simple.dbprofile' => 'default',
                                'simple.version' => '',
                                'simple.dataversion' => '',
                                'simple.installed' => 1,
                                'simple.installparam' => [ 'foo' => 'bar']
                                ), $config->modules);
        $this->assertEquals(array('simple'=>$modulePath), $config->_allModulesPathList);
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));

    }

    function testReadModuleInfoNewModuleNotActivated() {
        $config = new fakeConfig();
        $modulePath = realpath(__DIR__.'/app/modules/package').'/';
        $installation = array('modules'=>array());
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, false, $modulePath, $installation);
        $this->assertEquals(array(
                                'thepackage.enabled' => 0
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
        $this->assertEquals(array('thepackage.installed' => 0), $installation['modules']);
    }

    function testReadModuleInfoNewModuleActivatedNotInstalled() {
        $config = new fakeConfig();
        $config->modules = array('thepackage.enabled'=>1);
        $modulePath = realpath(__DIR__.'/app/modules/package').'/';
        $installation = array('modules'=>array());
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, false, $modulePath, $installation);
        $this->assertEquals(array('thepackage.installed' => 0), $installation['modules']);
        $this->assertEquals(array(
                                'thepackage.enabled' => 0
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
    }

    function testReadModuleInfoNewModuleActivatedInstalled() {

        // in the framework.ini, thepackage is disabled. Let's enable it for
        // this test.
        $module = new \Jelix\Core\Infos\ModuleStatusDeclaration(
            'thepackage',
            array(
                'enabled' => true
            ),
            true
        );
        $fmk = \Jelix\Core\App::getFrameworkInfo();
        $fmk->updateModule($module);

        $config = new fakeConfig();
        $config->modules = array('thepackage.enabled'=>1);
        $modulePath = realpath(__DIR__.'/app/modules/package').'/';
        $installation = array('modules'=>array('thepackage.installed'=>1));
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, false, $modulePath, $installation);
        $this->assertEquals(array('thepackage.installed' => 1), $installation['modules']);
        $this->assertEquals(array(
                                'thepackage.enabled' => 1,
                                'thepackage.dbprofile' => 'default',
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));

        // with all modules info
        $config = new fakeConfig();
        $config->modules = array('thepackage.enabled'=>1);
        $installation = array('modules'=>array('thepackage.installed'=>1));
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, true, $modulePath, $installation);
        $this->assertEquals(array('thepackage.installed' => 1,
                                'thepackage.version' => '',
                                'thepackage.dataversion' => ''
                                ), $installation['modules']);
        $this->assertEquals(array(
                                'thepackage.enabled' => 1,
                                'thepackage.dbprofile' => 'default',
                                'thepackage.version' => '',
                                'thepackage.dataversion' => '',
                                'thepackage.installed' => 1
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
        $this->assertEquals(array('thepackage'=>$modulePath), $config->_allModulesPathList);

    }
}
