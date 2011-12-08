<?php

require_once(JELIX_LIB_CORE_PATH.'jAutoloader.class.php');

class fakeAutoloader extends jAutoloader {

    function test_get_path($className) {
        return $this->getPath($className);
    }

}


class autoloaderTest extends PHPUnit_Framework_TestCase {

    function testPathWithoutNamespaces() {

        $autoloader = new fakeAutoloader();

        $autoloader->registerNamespace('bateau', '/usr/lib/sea/', '.php', true);

        $this->assertEquals('/usr/lib/sea/bateau.php', $autoloader->test_get_path('bateau'));
        $this->assertEquals('/usr/lib/sea/bateauinconnu.php', $autoloader->test_get_path('bateauinconnu'));
        $this->assertEquals('/usr/lib/sea/bateau/sous.php', $autoloader->test_get_path('bateau_sous'));
        $this->assertEquals('', $autoloader->test_get_path('unknown'));
    }
    
    function testPathWithNamespacePSR0() {
        $autoloader = new fakeAutoloader();
        $autoloader->registerNamespace('\foo', '/my/path', '.php', true);
        $this->assertEquals('/my/path/foo/bar/myclass.php', $autoloader->test_get_path('\foo\bar\myclass'));
        $this->assertEquals('/my/path/foo/bar/my/class.php', $autoloader->test_get_path('\foo\bar\my_class'));
    }

    function testPathWithNamespaceNotPSR0() {
        $autoloader = new fakeAutoloader();
        $autoloader->registerNamespace('\foo', '/my/path', '.php', false);
        $this->assertEquals('/my/path/bar/myclass.php', $autoloader->test_get_path('\foo\bar\myclass'));
        $this->assertEquals('/my/path/bar/my/class.php', $autoloader->test_get_path('\foo\bar\my_class'));
        $this->assertEquals('/my/path/myclass.php', $autoloader->test_get_path('\foo\myclass'));
    }

}
