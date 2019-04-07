<?php
require_once (JELIX_LIB_PATH.'installer/jInstallerApplication.class.php');
require_once (JELIX_LIB_PATH.'core/jConfigCompiler.class.php');


class moduleParametersTest extends PHPUnit_Framework_TestCase
{

    function getSerialized() {
        return array(
            array(
                '',
                array()
            ),
            array(
                'foo',
                array('foo'=>true)
            ),
            array(
                'foo=',
                array('foo'=>'')
            ),
            array(
                'foo;bar',
                array('foo'=>true, 'bar'=>true)
            ),
            array(
                'foo=abc',
                array('foo'=>'abc')
            ),
            array(
                'foo=abc;bar;baz=2',
                array('foo'=>'abc', 'bar'=>true, 'baz'=>2)
            ),
            array(
                'foo=abc;bar=a,b,c;baz=2',
                array('foo'=>'abc', 'bar'=>array('a','b','c'), 'baz'=>2)
            ),
            array(
                'foo=false',
                array('foo'=>false)
            ),
        );
    }


    /**
     * @dataProvider getSerialized
     */
    function testUnserialize($serialized, $expected) {
        $this->assertEquals(
            $expected,
            \Jelix\Installer\ModuleStatus::unserializeParameters($serialized)
        );
    }


    /**
     * @dataProvider getSerialized
     */
    function testSerialize($expectedSerialized, $data) {
        $this->assertEquals(
            $expectedSerialized,
            \Jelix\Installer\ModuleStatus::serializeParameters($data)
        );
    }
}