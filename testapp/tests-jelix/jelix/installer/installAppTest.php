<?php
require_once (JELIX_LIB_PATH.'installer/jInstallerApplication.class.php');
require_once (JELIX_LIB_PATH.'core/jConfigCompiler.class.php');


class installAppTest extends \PHPUnit\Framework\TestCase {

    function setUp() : void {
        jApp::saveContext();
        jApp::initPaths(__DIR__.'/app1/');
    }

    function tearDown() : void {
        jApp::restoreContext();
    }

    /**
     */
    function testNoEntryPoint() {
        $this->expectException(Exception::class);
        $globalSetup = new \Jelix\Installer\GlobalSetup(\jApp::appSystemPath('framework_empty.ini.php'));
        $this->expectException(Exception::class);
        $app = new jInstallerApplication('project.xml', $globalSetup);
    }

    function testEntryPointsList () {

        $app = new jInstallerApplication('project.xml');
        $list = $app->getEntryPointsList();
        $this->assertEquals(2, count($list));
        
        $ep = $app->getEntryPointInfo('index');
        $this->assertFalse($ep->isCliScript());
        $this->assertEquals('/index.php', $ep->getScriptName());
        $this->assertEquals('index.php', $ep->getFileName());
        $this->assertEquals('classic', $ep->getType());
        $this->assertEquals('aaa', $ep->getConfigObj()->isitme);

        $ep = $app->getEntryPointInfo('foo');
        $this->assertFalse($ep->isCliScript());
        $this->assertEquals('/foo.php', $ep->getScriptName());
        $this->assertEquals('foo.php', $ep->getFileName());
        $this->assertEquals('classic', $ep->getType());
        $this->assertEquals('foo', $ep->getConfigObj()->isitme);
    }
}
