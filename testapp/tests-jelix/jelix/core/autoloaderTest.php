<?php

class fakeAutoloader extends jAutoloader {

    function test_get_path($className) {
        return $this->getPath($className);
    }

}


/*class SplClassLoaderTest {
    private $_fileExtension = '.php';
    private $_namespace;
    private $_includePath;
    private $_namespaceSeparator = '\\';

    public function __construct($ns = null, $includePath = null) {
        $this->_namespace = $ns;
        $this->_includePath = $includePath;
    }

    public function loadClass($className)
    {
        if ($this->_namespace)
            $nsPart = substr($className, 0, strlen($this->_namespace.$this->_namespaceSeparator));
        else
            $nsPart = '';

        if (null === $this->_namespace
            || $this->_namespace.$this->_namespaceSeparator === $nsPart) {
            $fileName = '';
            $namespace = '';
            if (false !== ($lastNsPos = strripos($className, $this->_namespaceSeparator))) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName = str_replace($this->_namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . $this->_fileExtension;

            return ($this->_includePath !== null ? $this->_includePath . DIRECTORY_SEPARATOR : '') . ltrim($fileName, DIRECTORY_SEPARATOR);
        }
        return '';
    }
}*/




class autoloaderTest extends \PHPUnit\Framework\TestCase {

    function testPathWithoutNamespaces() {

        $autoloader = new fakeAutoloader();

        $autoloader->registerNamespace('', '/usr/lib/sea', '.php');
        $this->assertEquals(array('/usr/lib/sea/bateau.php'), $autoloader->test_get_path('bateau'));
        $this->assertEquals(array('/usr/lib/sea/bateauinconnu.php'), $autoloader->test_get_path('bateauinconnu'));
        $this->assertEquals(array('/usr/lib/sea/bateau/sous.php'), $autoloader->test_get_path('bateau_sous'));
        $this->assertEquals(array('/usr/lib/sea/bateau.php'), $autoloader->test_get_path('\bateau'));
        $this->assertEquals(array('/usr/lib/sea/foo/bateau.php'), $autoloader->test_get_path('\foo\bateau'));
    }

    function testClassPath() {
        $autoloader = new fakeAutoloader();
        $autoloader->registerClass('bateau', '/usr/lib/sea/bateau.php');
        $autoloader->registerClass('foo\bateau', '/usr/lib/sea/foobat.php');

        $this->assertEquals('/usr/lib/sea/bateau.php', $autoloader->test_get_path('bateau'));
        $this->assertEquals('', $autoloader->test_get_path('bateauinconnu'));
        $this->assertEquals('', $autoloader->test_get_path('bateau_sous'));
        $this->assertEquals('/usr/lib/sea/foobat.php', $autoloader->test_get_path('foo\bateau'));
        $this->assertEquals('', $autoloader->test_get_path('unknown'));
    }

    function testPathWithNamespacePSR0() {
        $autoloader = new fakeAutoloader();
        $autoloader->registerNamespace('\foo', '/my/path', '.php');
        $autoloader->registerNamespace('\blo_u\bl_i', '/my/path2', '.php');
        $autoloader->registerNamespace('\foobar', '/my/path3', '.php');
        $this->assertEquals('/my/path/foo.php', $autoloader->test_get_path('foo'));
        $this->assertEquals('/my/path/foo.php', $autoloader->test_get_path('\foo'));
        $this->assertEquals('/my/path/foo/bar/myclass.php', $autoloader->test_get_path('foo\bar\myclass'));
        $this->assertEquals('/my/path/foo/bar/my/class.php', $autoloader->test_get_path('\foo\bar\my_class'));
        $this->assertEquals('/my/path3/foobar/blob/myclass.php', $autoloader->test_get_path('foobar\blob\myclass'));
        $this->assertEquals('/my/path3/foobar/blob/my/class.php', $autoloader->test_get_path('\foobar\blob\my_class'));
        $this->assertEquals('/my/path2/blo_u/bl_i/bla/p.php', $autoloader->test_get_path('blo_u\bl_i\bla_p'));
        $this->assertEquals('/my/path2/blo_u/bl/i.php', $autoloader->test_get_path('blo_u\bl_i'));

    }

    function testPathWithNamespacePSR4() {
        $autoloader = new fakeAutoloader();
        $autoloader->registerNamespacePathMap('\foo', '/my/path', '.php');
        $autoloader->registerNamespacePathMap('\foobar', '/my/path2', '.php');
        $this->assertEquals('/my/path/bar/myclass.php', $autoloader->test_get_path('\foo\bar\myclass'));
        $this->assertEquals('/my/path/bar/my/class.php', $autoloader->test_get_path('\foo\bar\my_class'));
        $this->assertEquals('/my/path2/myclass.php', $autoloader->test_get_path('\foobar\myclass'));
        $this->assertEquals('/my/path2/my/class.php', $autoloader->test_get_path('\foobar\my_class'));
        $this->assertEquals('/my/path/myclass.php', $autoloader->test_get_path('\foo\myclass'));

        $autoloader = new fakeAutoloader();
        $autoloader->registerNamespacePathMap('foo', '/my/path', '.php');
        $this->assertEquals('/my/path/bar/myclass.php', $autoloader->test_get_path('\foo\bar\myclass'));
        $this->assertEquals('/my/path/bar/my/class.php', $autoloader->test_get_path('\foo\bar\my_class'));
        $this->assertEquals('/my/path/myclass.php', $autoloader->test_get_path('\foo\myclass'));
    }

    function testClassRegPath() {
        $autoloader = new fakeAutoloader();
        $autoloader->registerClassPattern('/^bat/', '/usr/lib/sea', '.php');

        $this->assertEquals('/usr/lib/sea/bateau.php', $autoloader->test_get_path('bateau'));
        $this->assertEquals('/usr/lib/sea/batman.php', $autoloader->test_get_path('batman'));
        $this->assertEquals('', $autoloader->test_get_path('unknown'));
    }

}
