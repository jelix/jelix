<?php


// autoload informations are defined in jelix-tests/module.xml
// we should then have into jApp::config() this informations
// let's check them

class moduleAutoloadTest extends \Jelix\UnitTests\UnitTestCase
{
    protected static $modulePath;
    public static function setUpBeforeClass() : void  {
        self::initJelixConfig();
        self::$modulePath = jApp::config()->_modulesPathList['jelix_tests'];
    }

    function testExistingSectionSetup() {
        $conf = jApp::config();
        $this->assertTrue(isset($conf->_autoload_class) && is_array($conf->_autoload_class), 'config should have a _autoload_class section');
        $this->assertTrue(isset($conf->_autoload_classpattern) && is_array($conf->_autoload_classpattern), 'config should have a _autoload_classpattern section');
        $this->assertTrue(isset($conf->_autoload_namespacepsr0) && is_array($conf->_autoload_namespacepsr0), 'config should have a _autoload_namespacepsr0 section');
        $this->assertTrue(isset($conf->_autoload_namespacepsr4) && is_array($conf->_autoload_namespacepsr4), 'config should have a _autoload_namespacepsr4 section');
        $this->assertTrue(isset($conf->_autoload_includepath) && is_array($conf->_autoload_includepath), 'config should have a _autoload_includepath section');
    }
    
    function testClassSection() {
        $conf = jApp::config();
        $this->assertEquals(26, count($conf->_autoload_class), '_autoload_class should have 23 declarations');
        $this->assertTrue(isset($conf->_autoload_class['myautoloadedclass']), '_autoload_class should declare info for myautoloadedclass');
        $this->assertEquals(self::$modulePath.'autoloadtest/autoloadtestclass.php', $conf->_autoload_class['myautoloadedclass'] , 'check path of file for myautoloadedclass');
    }

    function testClassPatternSection() {
        $conf = jApp::config();
        $this->assertEquals(2, count($conf->_autoload_classpattern), '_autoload_classpattern should have 2 properties');
        $this->assertTrue(isset($conf->_autoload_classpattern['regexp']), '_autoload_classpattern should have a regexp property');
        $this->assertTrue(isset($conf->_autoload_classpattern['path']), '_autoload_classpattern should have a path property');
        $this->assertEquals(3, count($conf->_autoload_classpattern['regexp']), '_autoload_classpattern[regexp] should have 3 declarations (for jelix_tests, jacldb and jacl2db modules)');
        $this->assertEquals(3, count($conf->_autoload_classpattern['path']), '_autoload_classpattern[path] should have 3 declarations (for jelix_tests, jacldb and jacl2db modules)');
        $this->assertTrue(in_array("/^myalclass/", $conf->_autoload_classpattern['regexp']), 'check the regexp');
        $this->assertTrue(in_array(self::$modulePath.'autoloadtest/withpattern/|.cl.php', $conf->_autoload_classpattern['path']), 'check path');
    }


    function testNamespaceSection() {
        $conf = jApp::config();
        $this->assertEquals(1, count($conf->_autoload_namespacepsr0), '_autoload_namespacepsr0 should have 1 declaration');
        $this->assertTrue(isset($conf->_autoload_namespacepsr0['jelixTests\foo']), '_autoload_namespacepsr0 should declare jelixTests\foo namespace');
        $this->assertEquals(array(self::$modulePath.'autoloadtest|.php'), $conf->_autoload_namespacepsr0['jelixTests\foo'] , 'check path');
    }

    function testNamespacePathMapSection() {
        $conf = jApp::config();
        $this->assertEquals(6, count($conf->_autoload_namespacepsr4), '_autoload_namespacepsr4 should have 6 declaration ');
        $this->assertTrue(isset($conf->_autoload_namespacepsr4['jelixTests\bar']), '_autoload_namespacepsr4 should declare jelixTests\bar namespace');
        $this->assertEquals([self::$modulePath.'autoloadtest/barns|.class.php'], $conf->_autoload_namespacepsr4['jelixTests\bar'] , 'check path');
        $this->assertTrue(isset($conf->_autoload_namespacepsr4['Jelix\Minify']), '_autoload_namespacepsr4 should declare Jelix\Minify namespace');
        $this->assertTrue(isset($conf->_autoload_namespacepsr4['Jelix\Acl2Db']), '_autoload_namespacepsr4 should declare Jelix\Minify namespace');
        $this->assertTrue(isset($conf->_autoload_namespacepsr4['Jelix\JelixModule']), '_autoload_namespacepsr4 should declare Jelix\JelixModule namespace');
        $this->assertTrue(isset($conf->_autoload_namespacepsr4['Testapp\Tests']), '_autoload_namespacepsr4 should declare Testapp\Tests namespace');
        $this->assertTrue(isset($conf->_autoload_namespacepsr4['JelixTests\Tests']), '_autoload_namespacepsr4 should declare JelixTests\Tests namespace');
    }

    function testIncludePathSection() {
        $conf = jApp::config();
        $this->assertEquals(1, count($conf->_autoload_includepath), '_autoload_includepath should have 1 property');
        $this->assertTrue(isset($conf->_autoload_includepath['path']), '_autoload_includepath should have a path property');
        $this->assertEquals(1, count($conf->_autoload_includepath['path']), '_autoload_includepath[path] should have 1 declaration');
        $this->assertEquals(self::$modulePath.'autoloadtest/incpath|.php', $conf->_autoload_includepath['path'][0] , 'check path');

    }


    function testAutoloaderSection() {
        $conf = jApp::config();
        $this->assertEquals(1, count($conf->_autoload_autoloader), '_autoload_autoloader should have 1 declaration (for jelix_tests)');
        $this->assertTrue(isset($conf->_autoload_autoloader[0]), '_autoload_autoloader should declare info for myautoloader');
        $this->assertEquals(self::$modulePath.'autoloadtest/myautoloader.php', $conf->_autoload_autoloader[0] , 'check path of file myautoloader.php');
    }

}
