<?php
require_once (JELIX_LIB_PATH.'installer/jInstallerApplication.class.php');
require_once (JELIX_LIB_PATH.'installer/jInstallerEntryPoint2.class.php');
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

    /**
     * @expectedException Exception
     */
    function testNoEntryPoint() {
        $app = new testInstallApp('project_empty.xml');
    }

    /**
     * @expectedException Exception
     */
    function testNoEntryPoint2() {
        $app = new testInstallApp('project_empty2.xml');
    }

    function testEntryPointsList () {

        $app = new testInstallApp('project.xml');
        $list = $app->getEntryPointsList();
        $this->assertEquals(2, count($list));
        
        $ep = $app->getEntryPointInfo('index');
        $this->assertFalse($ep->isCliScript());
        $this->assertEquals('/index.php', $ep->getScriptName());
        $this->assertEquals('index.php', $ep->getFileName());
        $this->assertEquals('', $ep->getType());
        $this->assertEquals('aaa', $ep->getConfigObj()->isitme);

        $ep = $app->getEntryPointInfo('foo');
        $this->assertFalse($ep->isCliScript());
        $this->assertEquals('/foo.php', $ep->getScriptName());
        $this->assertEquals('foo.php', $ep->getFileName());
        $this->assertEquals('', $ep->getType());
        $this->assertEquals('foo', $ep->getConfigObj()->isitme);
    }
}
