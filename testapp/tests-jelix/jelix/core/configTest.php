<?php

class fakeConfigCompiler extends \Jelix\Core\Config\Compiler {

    function test_read_module_info($config, $allModuleInfo, $path, &$installation, $section) {
        $this->_readModuleInfo($config, $allModuleInfo, $path, $installation, $section);
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
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, false, $modulePath, $installation, $section);
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
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, false, $modulePath, $installation, $section);
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
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, false, $modulePath, $installation, $section);
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
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, false, $modulePath, $installation, $section);
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
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, false, $modulePath, $installation, $section);
        $this->assertEquals(array('simple.installed' => 1), $installation['index.php']);
        $this->assertEquals(array(
                                'simple.access' => 1,
                                'simple.dbprofile' => 'default'
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));

        // with all modules info
        $config = new fakeConfig();
        $config->modules = array('simple.access'=>1);
        $installation = array('index.php'=>array('simple.installed'=>1));
        $compiler->test_read_module_info($config, true, $modulePath, $installation, $section);
        $this->assertEquals(array('simple.installed' => 1,
                                    'simple.version' => '',
                                    'simple.dataversion' => ''
                                  ), $installation['index.php']);
        $this->assertEquals(array(
                                'simple.access' => 1,
                                'simple.dbprofile' => 'default',
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
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, false, $modulePath, $installation, $section);
        $this->assertEquals(array(
                                'thepackage.access' => 0
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
        $this->assertEquals(array('thepackage.installed' => 0), $installation['index.php']);
    }

    function testReadModuleInfoNewModuleActivatedNotInstalled() {
        $config = new fakeConfig();
        $config->modules = array('thepackage.access'=>1);
        $modulePath = realpath(__DIR__.'/app/modules/package');
        $installation = array('index.php'=>array());
        $section = 'index.php';
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, false, $modulePath, $installation, $section);
        $this->assertEquals(array('thepackage.installed' => 0), $installation['index.php']);
        $this->assertEquals(array(
                                'thepackage.access' => 0
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
    }

    function testReadModuleInfoNewModuleActivatedInstalled() {
        $config = new fakeConfig();
        $config->modules = array('thepackage.access'=>1);
        $modulePath = realpath(__DIR__.'/app/modules/package');
        $installation = array('index.php'=>array('thepackage.installed'=>1));
        $section = 'index.php';
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, false, $modulePath, $installation, $section);
        $this->assertEquals(array('thepackage.installed' => 1), $installation['index.php']);
        $this->assertEquals(array(
                                'thepackage.access' => 1,
                                'thepackage.dbprofile' => 'default',
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_allModulesPathList)));
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));

        // with all modules info
        $config = new fakeConfig();
        $config->modules = array('thepackage.access'=>1);
        $installation = array('index.php'=>array('thepackage.installed'=>1));
        $compiler = new fakeConfigCompiler();
        $compiler->test_read_module_info($config, true, $modulePath, $installation, $section);
        $this->assertEquals(array('thepackage.installed' => 1,
                                'thepackage.version' => '',
                                'thepackage.dataversion' => ''
                                ), $installation['index.php']);
        $this->assertEquals(array(
                                'thepackage.access' => 1,
                                'thepackage.dbprofile' => 'default',
                                'thepackage.version' => '',
                                'thepackage.dataversion' => '',
                                'thepackage.installed' => 1
                                ), $config->modules);
        $this->assertEquals(0, count(array_keys($config->_externalModulesPathList)));
        $this->assertEquals(array('thepackage'=>$modulePath.'/'), $config->_allModulesPathList);

    }
}