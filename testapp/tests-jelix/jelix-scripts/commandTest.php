<?php
require_once(LIB_PATH.'jelix-scripts/includes/JelixScriptCommand.class.php');

class testCommand extends JelixScriptCommand {

    function run() {

    }

    function testGetOptions() {
        return $this->_options;
    }
    function testGetParameters() {
        return $this->_parameters;
    }
    function testGetRelativePath($path, $targetPath) {
        return $this->getRelativePath($path, $targetPath);
    }
    function testGetRealPath($path) {
        return $this->getRealPath($path);
    }

}


class commandTest extends PHPUnit_Framework_TestCase {

    function testGetOptions() {

        $cmd = new testCommand(null);


        $cmd->allowed_options = array();
        $cmd->allowed_parameters = array();

        $cmd->init(array());
        $this->assertEquals(0, count($cmd->testGetOptions()));
        $this->assertEquals(0, count($cmd->testGetParameters()));


        $cmd->allowed_options = array('-foo'=>false, '-bar'=>true);
        $cmd->allowed_parameters = array();

        $cmd->init(array("-bar", "uuu", "-foo"));
        $opt = $cmd->testGetOptions();
        $this->assertArrayHasKey('-bar', $opt);
        $this->assertArrayHasKey('-foo', $opt);
        $this->assertEquals('uuu', $opt['-bar']);
        $this->assertEquals(true, $opt['-foo']);
        $this->assertEquals(0, count($cmd->testGetParameters()));
    }

    function testGetParameters() {

        $cmd = new testCommand(null);
        $cmd->allowed_options = array();
        $cmd->allowed_parameters = array('name'=>true, 'other'=>false);

        $cmd->init(array("aaaa", "uuu"));
        $p = $cmd->testGetParameters();
        $this->assertArrayHasKey('name', $p);
        $this->assertArrayHasKey('other', $p);
        $this->assertEquals('aaaa', $p['name']);
        $this->assertEquals('uuu', $p['other']);
        $this->assertEquals(0, count($cmd->testGetOptions()));

        $cmd = new testCommand(null);
        $cmd->allowed_options = array();
        $cmd->allowed_parameters = array('name'=>true, 'other'=>false);
        $cmd->init(array("aaaa"));
        $p = $cmd->testGetParameters();
        $this->assertArrayHasKey('name', $p);
        $this->assertArrayNotHasKey('other', $p);
        $this->assertEquals('aaaa', $p['name']);
        $this->assertEquals(0, count($cmd->testGetOptions()));
    }

    function testGetRelativePath() {
        $cmd = new testCommand(null);

        $path = '/home/foo/bar/';
        $targetPath = '/home/machin';
        $this->assertEquals('../../machin/', $cmd->testGetRelativePath($path, $targetPath));

        $path = '/home/foo/bar';
        $targetPath = '/home/machin';
        $this->assertEquals('../../machin/', $cmd->testGetRelativePath($path, $targetPath));

        $path = '/home/machin';
        $targetPath = '/home/foo/bar';
        $this->assertEquals('../foo/bar/', $cmd->testGetRelativePath($path, $targetPath));

        $path = '/home/machin/';
        $targetPath = '/home/foo/bar';
        $this->assertEquals('../foo/bar/', $cmd->testGetRelativePath($path, $targetPath));

    }

    function testGetRealPath() {
        $cmd = new testCommand(null);

        if (DIRECTORY_SEPARATOR == '/') {
            $path = '/home/foo/bar/';
            $this->assertEquals('/home/foo/bar', $cmd->testGetRealPath($path));
            $path = '/home/./bar/';
            $this->assertEquals('/home/bar', $cmd->testGetRealPath($path));
            $path = '/home//bar/';
            $this->assertEquals('/home/bar', $cmd->testGetRealPath($path));
            $path = '/home/foo/../bar/';
            $this->assertEquals('/home/bar', $cmd->testGetRealPath($path));
            $path = '/home/foo/../bar';
            $this->assertEquals('/home/bar', $cmd->testGetRealPath($path));
            $path = '/home/foo/machin/../bar/../../truc';
            $this->assertEquals('/home/truc', $cmd->testGetRealPath($path));
            
        }
        else {
            $path = 'c:\\\\home\\foo\\bar\\';
            $this->assertEquals('c:\\\\home\\foo\\bar', $cmd->testGetRealPath($path));
            $path = 'c:\\\\home\\.\\bar\\';
            $this->assertEquals('c:\\\\home\\bar', $cmd->testGetRealPath($path));
            $path = 'c:\\\\home\\\\bar\\';
            $this->assertEquals('c:\\\\home\\bar', $cmd->testGetRealPath($path));
            $path = 'c:\\\\home\\foo\\..\\bar\\';
            $this->assertEquals('c:\\\\home\\bar', $cmd->testGetRealPath($path));
            $path = 'c:\\\\home\\foo\\..\\bar';
            $this->assertEquals('c:\\\\home\\bar', $cmd->testGetRealPath($path));
            $path = 'c:\\\\home\\foo\\machin\\..\\bar\\..\\..\\truc';
            $this->assertEquals('c:\\\\home\\truc', $cmd->testGetRealPath($path));
        }
    }
}
