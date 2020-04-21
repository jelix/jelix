<?php

require_once(JELIX_LIB_CORE_PATH.'jConfigAutoloader.class.php');

class fakeConfigAutoloader extends jConfigAutoloader {

    function test_get_path($className) {
        return $this->getPath($className);
    }

}

class configautoloaderTest extends PHPUnit_Framework_TestCase {

    function testPathWithoutNamespaces() {

        $autoloader = new fakeConfigAutoloader((object) parse_ini_string('
[_autoload_class]
[_autoload_namespace]
[_autoload_namespacepathmap]
[_autoload_classpattern]
[_autoload_includepathmap]
[_autoload_includepath]
path[]="/usr/lib/sea|.php"
', true));
        $this->assertEquals(array('/usr/lib/sea/bateau.php'), $autoloader->test_get_path('bateau'));
        $this->assertEquals(array('/usr/lib/sea/bateauinconnu.php'), $autoloader->test_get_path('bateauinconnu'));
        $this->assertEquals(array('/usr/lib/sea/bateau/sous.php'), $autoloader->test_get_path('bateau_sous'));
        $this->assertEquals(array('/usr/lib/sea/bateau.php'), $autoloader->test_get_path('\bateau'));
        $this->assertEquals(array('/usr/lib/sea/foo/bateau.php'), $autoloader->test_get_path('\foo\bateau'));
    }

    function testClassPath() {
        $autoloader = new fakeConfigAutoloader((object) parse_ini_string('
[_autoload_namespace]
[_autoload_namespacepathmap]
[_autoload_classpattern]
[_autoload_includepathmap]
[_autoload_includepath]
[_autoload_class]
bateau="/usr/lib/sea/bateau.php"
foo\bateau="/usr/lib/sea/foobat.php"
', true));
        $this->assertEquals('/usr/lib/sea/bateau.php', $autoloader->test_get_path('bateau'));
        $this->assertEquals('', $autoloader->test_get_path('bateauinconnu'));
        $this->assertEquals('', $autoloader->test_get_path('bateau_sous'));
        $this->assertEquals('/usr/lib/sea/foobat.php', $autoloader->test_get_path('foo\bateau'));
        $this->assertEquals('', $autoloader->test_get_path('unknown'));
    }

    function testPathWithNamespacePSR0() {
        $autoloader = new fakeConfigAutoloader((object) parse_ini_string('
[_autoload_class]
[_autoload_namespacepathmap]
[_autoload_classpattern]
[_autoload_includepathmap]
[_autoload_includepath]
[_autoload_namespace]
foo = "/my/path|.php"
foobar = "/my/path3|.php"
blo_u\bl_i="/my/path2|.php"
', true));
        $this->assertEquals('/my/path/foo.php', $autoloader->test_get_path('foo'));
        $this->assertEquals('/my/path/foo.php', $autoloader->test_get_path('\foo'));
        $this->assertEquals('/my/path3/foobar.php', $autoloader->test_get_path('foobar'));
        $this->assertEquals('/my/path3/foobar.php', $autoloader->test_get_path('\foobar'));
        $this->assertEquals('/my/path/foo/bar/myclass.php', $autoloader->test_get_path('foo\bar\myclass'));
        $this->assertEquals('/my/path/foo/bar/my/class.php', $autoloader->test_get_path('\foo\bar\my_class'));
        $this->assertEquals('/my/path3/foobar/blob/myclass.php', $autoloader->test_get_path('foobar\blob\myclass'));
        $this->assertEquals('/my/path3/foobar/blob/my/class.php', $autoloader->test_get_path('\foobar\blob\my_class'));
        $this->assertEquals('/my/path2/blo_u/bl_i/bla/p.php', $autoloader->test_get_path('blo_u\bl_i\bla_p'));
        $this->assertEquals('/my/path2/blo_u/bl/i.php', $autoloader->test_get_path('blo_u\bl_i'));

    }

    function testPathWithNamespacePSR4() {
        $autoloader = new fakeConfigAutoloader((object) parse_ini_string('
[_autoload_class]
[_autoload_namespace]
[_autoload_classpattern]
[_autoload_includepathmap]
[_autoload_includepath]
[_autoload_namespacepathmap]
foo = "/my/path|.php"
foobar = "/my/path3|.php"
', true));
        $this->assertEquals('/my/path/bar/myclass.php', $autoloader->test_get_path('\foo\bar\myclass'));
        $this->assertEquals('/my/path/bar/my/class.php', $autoloader->test_get_path('\foo\bar\my_class'));
        $this->assertEquals('/my/path/myclass.php', $autoloader->test_get_path('\foo\myclass'));
        $this->assertEquals('/my/path3/bar/myclass.php', $autoloader->test_get_path('\foobar\bar\myclass'));
        $this->assertEquals('/my/path3/bar/my/class.php', $autoloader->test_get_path('\foobar\bar\my_class'));
        $this->assertEquals('/my/path3/myclass.php', $autoloader->test_get_path('\foobar\myclass'));

    }

    function testClassRegPath() {
        $autoloader = new fakeConfigAutoloader((object) parse_ini_string('
[_autoload_class]
[_autoload_namespace]
[_autoload_namespacepathmap]
[_autoload_includepathmap]
[_autoload_includepath]
[_autoload_classpattern]
regexp[]="/^bat/"
path[]="/usr/lib/sea|.php"
', true));

        $this->assertEquals('/usr/lib/sea/bateau.php', $autoloader->test_get_path('bateau'));
        $this->assertEquals('/usr/lib/sea/batman.php', $autoloader->test_get_path('batman'));
        $this->assertEquals('', $autoloader->test_get_path('unknown'));
    }

}
