<?php

class fakeConfigAutoloader extends jConfigAutoloader {

    function test_get_path($className) {
        return $this->getPath($className);
    }

}

class configautoloaderTest extends \PHPUnit\Framework\TestCase {

    function testPathWithoutNamespaces() {

        $autoloader = new fakeConfigAutoloader((object) parse_ini_string('
[_autoload_class]
[_autoload_namespacepsr0]
[_autoload_namespacepsr4]
[_autoload_classpattern]
[_autoload_includepathmap]
[_autoload_includepath]
path[]="'.__DIR__.'/autoload/some|.php"
[_autoload_fallback]
', true));
        $this->assertEquals(__DIR__.'/autoload/some/bateau.php', $autoloader->test_get_path('bateau'));
        $this->assertFalse($autoloader->test_get_path('bateauinconnu'));
        $this->assertEquals(__DIR__.'/autoload/some/bateau/sous.php', $autoloader->test_get_path('bateau_sous'));
        $this->assertEquals(__DIR__.'/autoload/some/bateau.php', $autoloader->test_get_path('\bateau'));
        $this->assertEquals(__DIR__.'/autoload/some/foo/bateau.php', $autoloader->test_get_path('\foo\bateau'));
    }

    function testClassPath() {
        $autoloader = new fakeConfigAutoloader((object) parse_ini_string('
[_autoload_namespacepsr0]
[_autoload_namespacepsr4]
[_autoload_classpattern]
[_autoload_includepathmap]
[_autoload_includepath]
[_autoload_class]
bateau="'.__DIR__.'/autoload/some/bateau.php"
foo\bateau="'.__DIR__.'/autoload/foobat.php"
[_autoload_fallback]
', true));
        $this->assertEquals(__DIR__.'/autoload/some/bateau.php',$autoloader->test_get_path('bateau'));
        $this->assertFalse($autoloader->test_get_path('bateauinconnu'));
        $this->assertFalse($autoloader->test_get_path('bateau_sous'));
        $this->assertEquals(__DIR__.'/autoload/foobat.php',$autoloader->test_get_path('foo\bateau'));
        $this->assertFalse($autoloader->test_get_path('unknown'));
    }

    function testPathWithNamespacePSR0() {
        $autoloader = new fakeConfigAutoloader((object) parse_ini_string('
[_autoload_class]
[_autoload_namespacepsr4]
[_autoload_classpattern]
[_autoload_includepathmap]
[_autoload_includepath]
[_autoload_namespacepsr0]
foo = "'.__DIR__.'/autoload/ns/bar|.php"
foobar = "'.__DIR__.'/autoload/ns/bar3|.php"
blo_u\bl_i="'.__DIR__.'/autoload/ns/other|.php"
[_autoload_fallback]
psr0[]="'.__DIR__.'/autoload/some|.php"
', true));
        $this->assertEquals(__DIR__.'/autoload/ns/bar/foo.php', $autoloader->test_get_path('foo'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar/foo.php', $autoloader->test_get_path('\foo'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar3/foobar.php', $autoloader->test_get_path('foobar'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar3/foobar.php', $autoloader->test_get_path('\foobar'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar/foo/bar/myclass.php', $autoloader->test_get_path('foo\bar\myclass'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar/foo/bar/my/class.php', $autoloader->test_get_path('\foo\bar\my_class'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar3/foobar/blob/myclass.php', $autoloader->test_get_path('foobar\blob\myclass'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar3/foobar/blob/my/superclass.php', $autoloader->test_get_path('\foobar\blob\my_superclass'));
        $this->assertEquals(__DIR__.'/autoload/ns/other/blo_u/bl_i/bla/p.php', $autoloader->test_get_path('blo_u\bl_i\bla_p'));
        $this->assertEquals(__DIR__.'/autoload/ns/other/blo_u/bl/i.php', $autoloader->test_get_path('blo_u\bl_i'));
        $this->assertEquals(__DIR__.'/autoload/some/bateau.php',$autoloader->test_get_path('bateau'));
        $this->assertEquals(__DIR__.'/autoload/some/foo/bateau.php',$autoloader->test_get_path('foo\bateau'));
    }

    function testPathWithNamespacePSR0WithMultipleDir() {
        $autoloader = new fakeConfigAutoloader((object) parse_ini_string('
[_autoload_class]
[_autoload_namespacepsr4]
[_autoload_classpattern]
[_autoload_includepathmap]
[_autoload_includepath]
[_autoload_namespacepsr0]
foo[] = "'.__DIR__.'/autoload/ns/bar|.php"
foo[] = "'.__DIR__.'/autoload/some|.php"
[_autoload_fallback]
', true));
        $this->assertEquals(__DIR__.'/autoload/ns/bar/foo.php', $autoloader->test_get_path('foo'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar/foo.php', $autoloader->test_get_path('\foo'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar/foo/bar/myclass.php', $autoloader->test_get_path('foo\bar\myclass'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar/foo/bar/my/class.php', $autoloader->test_get_path('\foo\bar\my_class'));
        $this->assertEquals(__DIR__.'/autoload/some/foo/bateau.php',$autoloader->test_get_path('foo\bateau'));
    }

    function testPathWithNamespacePSR4WithMultipleDir() {
        $autoloader = new fakeConfigAutoloader((object) parse_ini_string('
[_autoload_class]
[_autoload_namespacepsr0]
[_autoload_classpattern]
[_autoload_includepathmap]
[_autoload_includepath]
[_autoload_namespacepsr4]
foo[] = "'.__DIR__.'/autoload/ns/bar/foo|.php"
foo[] = "'.__DIR__.'/autoload/some/foo|.php"
[_autoload_fallback]
', true));
        $this->assertEquals(__DIR__.'/autoload/ns/bar/foo/bar/myclass.php', $autoloader->test_get_path('\foo\bar\myclass'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar/foo/bar/my/class.php', $autoloader->test_get_path('\foo\bar\my_class'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar/foo/myclass2.php', $autoloader->test_get_path('\foo\myclass2'));
        $this->assertEquals(__DIR__.'/autoload/some/foo/bateau.php',$autoloader->test_get_path('foo\bateau'));
        $this->assertFalse($autoloader->test_get_path('foo\something'));
    }

    function testPathWithNamespacePSR4() {
        $autoloader = new fakeConfigAutoloader((object) parse_ini_string('
[_autoload_class]
[_autoload_namespacepsr0]
[_autoload_classpattern]
[_autoload_includepathmap]
[_autoload_includepath]
[_autoload_namespacepsr4]
foo = "'.__DIR__.'/autoload/ns/bar/foo|.php"
foobar = "'.__DIR__.'/autoload/ns/bar3/foobar|.php"
[_autoload_fallback]
psr0[]="'.__DIR__.'/autoload/some|.php"
', true));
        $this->assertEquals(__DIR__.'/autoload/ns/bar/foo/bar/myclass.php', $autoloader->test_get_path('\foo\bar\myclass'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar/foo/bar/my/class.php', $autoloader->test_get_path('\foo\bar\my_class'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar/foo/myclass2.php', $autoloader->test_get_path('\foo\myclass2'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar3/foobar/blob/myclass.php', $autoloader->test_get_path('\foobar\blob\myclass'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar3/foobar/blob/my/superclass.php', $autoloader->test_get_path('\foobar\blob\my_superclass'));
        $this->assertEquals(__DIR__.'/autoload/ns/bar3/foobar/myclass2.php', $autoloader->test_get_path('\foobar\myclass2'));
        $this->assertEquals(__DIR__.'/autoload/some/bateau.php',$autoloader->test_get_path('bateau'));
        $this->assertEquals(__DIR__.'/autoload/some/foo/bateau.php',$autoloader->test_get_path('foo\bateau'));
        $this->assertFalse($autoloader->test_get_path('foo\something'));
    }

    function testClassRegPath() {
        $autoloader = new fakeConfigAutoloader((object) parse_ini_string('
[_autoload_class]
[_autoload_namespacepsr0]
[_autoload_namespacepsr4]
[_autoload_includepathmap]
[_autoload_includepath]
[_autoload_classpattern]
regexp[]="/^bat/"
path[]="'.__DIR__.'/autoload/some|.php"
[_autoload_fallback]
', true));

        $this->assertEquals(__DIR__.'/autoload/some/bateau.php',  $autoloader->test_get_path('bateau'));
        $this->assertEquals(__DIR__.'/autoload/some/batman.php',  $autoloader->test_get_path('batman'));
        $this->assertFalse($autoloader->test_get_path('unknown'));
    }

}
