<?php

class fakeConfigCompiler extends \Jelix\Core\Config\Compiler {

    static function test_read_module_info($config, $allModuleInfo, $path, &$installation, $section) {
        self::_readModuleInfo($config, $allModuleInfo, $path, $installation, $section);
    }
}

class fakeConfig {
    public $disableInstallers = false;
    public $enableAllModules = false;
    public $modules = array();
    public $_allModulesPathList = array();
    public $_externalModulesPathList = array();
    public $pluginsPath = '';
    public $modulesPath = '';
}


class configTest extends PHPUnit_Framework_TestCase {

    function testReadModuleInfoUnknowPath() {
        $config = new fakeConfig();
        $modulePath = '/foo/bar';
        $installation = array('index.php'=>array());
        $section = 'index.php';
        fakeConfigCompiler::test_read_module_info($config, false, $modulePath, $installation, $section);
        $this->assertEquals(0, count(array_keys($config->modules)));
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
        $this->assertEquals(0, count(array_keys($installation['index.php'])));
    }

    function testReadModuleInfoNotAModule() {
        $config = new fakeConfig();
        $modulePath = __DIR__;
        $installation = array('index.php'=>array());
        $section = 'index.php';
        fakeConfigCompiler::test_read_module_info($config, false, $modulePath, $installation, $section);
        $this->assertEquals(0, count(array_keys($config->modules)));
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
        $this->assertEquals(0, count(array_keys($installation['index.php'])));
    }

    function testReadModuleInfoOldModuleNotActivated() {
        $config = new fakeConfig();
        $modulePath = realpath(__DIR__.'/app/modules/simple');
        $installation = array('index.php'=>array());
        $section = 'index.php';
        fakeConfigCompiler::test_read_module_info($config, false, $modulePath, $installation, $section);
        $this->assertEquals(array(
                                'simple.access' => 0
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
        $this->assertEquals(array('simple.installed' => 0), $installation['index.php']);
    }

    function testReadModuleInfoOldModuleActivatedNotInstalled() {
        $config = new fakeConfig();
        $config->modules = array('simple.access'=>1);
        $modulePath = realpath(__DIR__.'/app/modules/simple');
        $installation = array('index.php'=>array());
        $section = 'index.php';
        fakeConfigCompiler::test_read_module_info($config, false, $modulePath, $installation, $section);
        $this->assertEquals(array('simple.installed' => 0), $installation['index.php']);
        $this->assertEquals(array(
                                'simple.access' => 0
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
    }

    function testReadModuleInfoOldModuleActivatedInstalled() {
        $config = new fakeConfig();
        $config->modules = array('simple.access'=>1);
        $modulePath = realpath(__DIR__.'/app/modules/simple');
        $installation = array('index.php'=>array('simple.installed'=>1));
        $section = 'index.php';
        fakeConfigCompiler::test_read_module_info($config, false, $modulePath, $installation, $section);
        $this->assertEquals(array('simple.installed' => 1), $installation['index.php']);
        $this->assertEquals(array(
                                'simple.access' => 1,
                                'simple.dbprofile' => 'default',
                                'simple.webalias' => 'simple'
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));

        // with all modules info
        $config = new fakeConfig();
        $config->modules = array('simple.access'=>1);
        $installation = array('index.php'=>array('simple.installed'=>1));
        fakeConfigCompiler::test_read_module_info($config, true, $modulePath, $installation, $section);
        $this->assertEquals(array('simple.installed' => 1,
                                    'simple.version' => '',
                                    'simple.dataversion' => ''
                                  ), $installation['index.php']);
        $this->assertEquals(array(
                                'simple.access' => 1,
                                'simple.dbprofile' => 'default',
                                'simple.webalias' => 'simple',
                                'simple.version' => '',
                                'simple.dataversion' => '',
                                'simple.installed' => 1
                                ), $config->modules);
        $this->assertEquals(array('simple'=>$modulePath.'/'), $config->_allModulesPathList);
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));

    }

    function testReadModuleInfoNewModuleNotActivated() {
        $config = new fakeConfig();
        $modulePath = realpath(__DIR__.'/app/modules/package');
        $installation = array('index.php'=>array());
        $section = 'index.php';
        fakeConfigCompiler::test_read_module_info($config, false, $modulePath, $installation, $section);
        $this->assertEquals(array(
                                'jelixtest/composerpackage.access' => 0
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
        $this->assertEquals(array('jelixtest/composerpackage.installed' => 0), $installation['index.php']);
    }

    function testReadModuleInfoNewModuleActivatedNotInstalled() {
        $config = new fakeConfig();
        $config->modules = array('jelixtest/composerpackage.access'=>1);
        $modulePath = realpath(__DIR__.'/app/modules/package');
        $installation = array('index.php'=>array());
        $section = 'index.php';
        fakeConfigCompiler::test_read_module_info($config, false, $modulePath, $installation, $section);
        $this->assertEquals(array('jelixtest/composerpackage.installed' => 0), $installation['index.php']);
        $this->assertEquals(array(
                                'jelixtest/composerpackage.access' => 0
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
    }

    function testReadModuleInfoNewModuleActivatedInstalled() {
        $config = new fakeConfig();
        $config->modules = array('jelixtest/composerpackage.access'=>1);
        $modulePath = realpath(__DIR__.'/app/modules/package');
        $installation = array('index.php'=>array('jelixtest/composerpackage.installed'=>1));
        $section = 'index.php';
        fakeConfigCompiler::test_read_module_info($config, false, $modulePath, $installation, $section);
        $this->assertEquals(array('jelixtest/composerpackage.installed' => 1), $installation['index.php']);
        $this->assertEquals(array(
                                'jelixtest/composerpackage.access' => 1,
                                'jelixtest/composerpackage.dbprofile' => 'default',
                                'jelixtest/composerpackage.webalias' => 'thepackage'
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));

        // with all modules info
        $config = new fakeConfig();
        $config->modules = array('jelixtest/composerpackage.access'=>1);
        $installation = array('index.php'=>array('jelixtest/composerpackage.installed'=>1));
        fakeConfigCompiler::test_read_module_info($config, true, $modulePath, $installation, $section);
        $this->assertEquals(array('jelixtest/composerpackage.installed' => 1,
                                'jelixtest/composerpackage.version' => '',
                                'jelixtest/composerpackage.dataversion' => ''
                                ), $installation['index.php']);
        $this->assertEquals(array(
                                'jelixtest/composerpackage.access' => 1,
                                'jelixtest/composerpackage.dbprofile' => 'default',
                                'jelixtest/composerpackage.webalias' => 'thepackage',
                                'jelixtest/composerpackage.version' => '',
                                'jelixtest/composerpackage.dataversion' => '',
                                'jelixtest/composerpackage.installed' => 1
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
        $this->assertEquals(array('jelixtest/composerpackage'=>$modulePath.'/'), $config->_allModulesPathList);

    }
}