<?php
require_once (JELIX_LIB_PATH.'installer/jInstallerApplication.class.php');
require_once (JELIX_LIB_PATH.'installer/jInstallerEntryPoint.class.php');
require_once (JELIX_LIB_PATH.'installer/jInstallerModuleInfos.class.php');
require_once (JELIX_LIB_PATH.'core/jConfigCompiler.class.php');


class testInstallApp extends jInstallerApplication {

}


class installAppTest extends PHPUnit_Framework_TestCase {

    function setUp() {
        jApp::saveContext();
        jApp::initPaths(__DIR__.'/app1/');
    }

    function tearDown() {
        jApp::restoreContext();
    }

    function testEntryPointsList () {
        $app = new testInstallApp('project_empty.xml');
        $this->assertEquals(array(), $app->getEntryPointsList());

        $app = new testInstallApp('project_empty2.xml');
        $this->assertEquals(array(), $app->getEntryPointsList());

        $app = new testInstallApp('project.xml');
        $list = $app->getEntryPointsList();
        $this->assertEquals(2, count($list));
        
        $ep = $app->getEntryPointInfo('index');
        $this->assertFalse($ep->isCliScript);
        $this->assertEquals('/index.php', $ep->scriptName);
        $this->assertEquals('index.php', $ep->file);
        $this->assertEquals('', $ep->type);
        $this->assertEquals('aaa', $ep->config->startModule);

        $ep = $app->getEntryPointInfo('foo');
        $this->assertFalse($ep->isCliScript);
        $this->assertEquals('/foo.php', $ep->scriptName);
        $this->assertEquals('foo.php', $ep->file);
        $this->assertEquals('', $ep->type);
        $this->assertEquals('foo', $ep->config->startModule);
    }
}
