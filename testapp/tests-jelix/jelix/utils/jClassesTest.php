<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Christophe Thiriot
* @contributor Laurent Jouanneau
* @copyright   2008 Christophe Thiriot, 2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.1
*/

class jClassesTest extends \Jelix\UnitTests\UnitTestCase {

    function setUp() : void {
        self::initJelixConfig();
        jClasses::resetBindings();
        parent::setUp();
    }

    public function testClassNoBinding() {
        $this->assertTrue(class_exists('jClassBinding'));
        $class = jClasses::getBindedService('class:jelix_tests~myclass');
        $this->assertTrue($class instanceof myclass);

        // same test with an interface (raises an exception)
        try {
            $class = jClasses::getBindedService('iface:jelix_tests~test');
            $this->fail('An interface without binding should raise a jException');
        } catch (jException $e) {
            $this->assertTrue(true);
        }
    }

    public function testBindingInCodeTo() {
        jClasses::bind('jelix_tests~test')->to('jelix_tests~myclass');
        $class = jClasses::getBindedService('jelix_tests~test');
        $this->assertTrue($class instanceof myclass);

        jClasses::bind('jelix_tests~test')->to('jelix_tests~myclass');
        $classname = jClasses::bind('jelix_tests~test')->getClassName();
        $this->assertEquals('myclass', $classname);

        try {
            jClasses::bind('jelix_tests~test')->to('jelix_tests~notexistingclass');
            $this->fail('A binding to a non existing class should raise an exception');
        } catch (jExceptionSelector $e) {
            $this->assertTrue(true);
        }
    }

    public function testBindingInCodeToInstance() {
        $instance = jClasses::create('jelix_tests~myclass');
        jClasses::bind('jelix_tests~test')->toInstance($instance);
        $class = jClasses::getBindedService('jelix_tests~test');
        $this->assertTrue($class instanceof myclass);

        jClasses::bind('jelix_tests~test')->toInstance($instance);
        $classname = jClasses::bind('jelix_tests~test')->getClassName();
        $this->assertEquals('myclass', $classname);
    }

    // test with binding in jelix config file + get class name + non existing binded class
    public function testBindingInJelixConfigFile() {
        jApp::saveContext();

        jApp::config()->classbindings['jelix_tests-test'] = 'jelix_tests~myclass';
        $class = jClasses::getBindedService('jelix_tests~test');
        $this->assertTrue($class instanceof myclass);

        // test with long selector and test with parse_ini_file
        jApp::config()->classbindings['class:jelix_tests-myclass'] = 'jelix_tests~myclass';
        $class = jClasses::getBindedService('class:jelix_tests~myclass');
        $this->assertTrue($class instanceof myclass);

        jApp::restoreContext();
    }

    // test with binding in DEFAULT IMPLEMENTATION constant + get class name + non existing binded class
    public function testBindingInDefaultImplementation() {
        $classname = jClasses::bind('iface:jelix_tests~tests/foo')->getClassName();
        $this->assertEquals('bind', $classname);
        jClasses::resetBindings();

        $class = jClasses::getBindedService('iface:jelix_tests~tests/foo');
        $this->assertTrue($class instanceof bind);

        jClasses::resetBindings();

        try {
            $class = jClasses::getBindedService('class:jelix_tests~test/bind');
            $this->fail('A non existing default implementation should raise an exception');
        } catch (jExceptionSelector $e) {
            $this->assertTrue(true);
        }
    }

    // getBindedService should give the same instance if called twice
    public function testBindedServiceCalledTwice(){
        $obj1 = jClasses::getBindedService('class:jelix_tests~myclass');
        $obj2 = jClasses::getBindedService('class:jelix_tests~myclass');
        $this->assertTrue($obj1 === $obj2);

        jClasses::bind('jelix_tests~test')->to('jelix_tests~myclass');
        $obj1 = jClasses::getBindedService('jelix_tests~test');
        $obj2 = jClasses::getBindedService('jelix_tests~test');
        $this->assertTrue($obj1 === $obj2);
    }

    // createBinded called twice should give different instances if called twice
    public function testcreateBindedCalledTwice(){
        $obj1 = jClasses::createBinded('class:jelix_tests~myclass');
        $obj2 = jClasses::createBinded('class:jelix_tests~myclass');
        $this->assertTrue($obj1 instanceof myclass);
        $this->assertTrue($obj2 instanceof myclass);
        $this->assertTrue($obj1 !== $obj2);

        jClasses::bind('jelix_tests~test')->to('jelix_tests~myclass');
        $obj1 = jClasses::createBinded('jelix_tests~test');
        $obj2 = jClasses::createBinded('jelix_tests~test');
        $this->assertTrue($obj1 !== $obj2);
    }

    // createBinded called twice with a bind()->toInstance() before give diffent instances , too.
    // then toInstance has no effect... 
    public function testCreateBindedWithToInstanceCalledTwice(){
        $instance = new StdClass();
        jClasses::bind('class:jelix_tests~myclass')->toInstance($instance);
        $obj1 = jClasses::createBinded('class:jelix_tests~myclass');
        $obj2 = jClasses::createBinded('class:jelix_tests~myclass');
        $this->assertTrue($obj1 instanceof myclass);
        $this->assertTrue($obj2 instanceof myclass);
        $this->assertTrue($obj1 !== $obj2);
    }
}

?>
