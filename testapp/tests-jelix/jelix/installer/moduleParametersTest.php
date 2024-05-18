<?php

use Jelix\Core\Infos\ModuleStatusDeclaration;

class moduleParametersTest extends \PHPUnit\Framework\TestCase
{

    static function getSerializedToUnserialized() {
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
                'foo=abc;bar=[a,b,c];baz=2',
                array('foo'=>'abc', 'bar'=>array('a','b','c'), 'baz'=>2)
            ),
            array(
                'foo=abc;bar=[a];baz=2',
                array('foo'=>'abc', 'bar'=>array('a'), 'baz'=>2)
            ),
            array(
                'foo=false',
                array('foo'=>false)
            ),
            array(
                array('foo'=>'true', 'bar'=>true),
                array('foo'=>true, 'bar'=>true)
            ),
            array(
                array('foo'=>'abc'),
                array('foo'=>'abc')
            ),
            array(
                array('foo'=>'abc', 'bar'=>true, 'baz'=>2),
                array('foo'=>'abc', 'bar'=>true, 'baz'=>2)
            ),
            array(
                array('foo'=>'abc', 'bar'=>'[a,b,c]', 'baz'=>2),
                array('foo'=>'abc', 'bar'=>array('a','b','c'), 'baz'=>2)
            ),
            array(
                array('foo'=>'false'),
                array('foo'=>false)
            ),
        );
    }


    /**
     * @dataProvider getSerializedToUnserialized
     */
    function testUnserialize($serialized, $expected) {
        $this->assertEquals(
            $expected,
            ModuleStatusDeclaration::unserializeParameters($serialized)
        );
    }

    static function getUnserializedToSerializedAsString() {
        return array(
            array(
                array(),
                ''
            ),
            array(
                array('foo'=>true),
                'foo'
            ),
            array(
                array('foo'=>''),
                'foo='
            ),
            array(
                array('foo'=>true, 'bar'=>true),
                'foo;bar'
            ),
            array(
                array('foo'=>true, 'bar'=>'true'),
                'foo;bar',
            ),
            array(
                array('foo'=>'abc'),
                'foo=abc'
            ),
            array(
                array('foo'=>'abc', 'bar'=>true, 'baz'=>2),
                'foo=abc;bar;baz=2',
            ),
            array(
                array('foo'=>'abc', 'bar'=>array('a','b','c'), 'baz'=>2),
                'foo=abc;bar=[a,b,c];baz=2'
            ),
            array(
                array('foo'=>'abc', 'bar'=>array('a'), 'baz'=>2),
                'foo=abc;bar=[a];baz=2',
            ),
            array(
                array('foo'=>false),
                'foo=false',
            ),
        );
    }

    /**
     * @dataProvider getUnserializedToSerializedAsString
     */
    function testSerializeAsString($data, $expectedSerialized) {
        $this->assertEquals(
            $expectedSerialized,
            ModuleStatusDeclaration::serializeParametersAsString($data)
        );
    }

    static function getUnserializedToSerializedAsStringWithDefParam() {
        return array(
            array(
                array(),
                array(),
                ''
            ),
            array(
                array(),
                array('foo'=>true),
                'foo'
            ),
            array(
                array(),
                array('foo'=>false),
                ''
            ),
            array(
                array('foo'=>true),
                array(),
                'foo'
            ),
            array(
                array('foo'=>true),
                array('foo'=>true),
                'foo'
            ),
            array(
                array('foo'=>true),
                array('foo'=>false),
                'foo'
            ),
            array(
                array('foo'=>false),
                array('foo'=>true),
                ''
            ),
            array(
                array('foo'=>false),
                array('foo'=>false),
                ''
            ),
            array(
                array('foo'=>''),
                array('foo'=>'bar'),
                'foo='
            ),
            array(
                array(),
                array('foo'=>'bar'),
                ''
            ),
            array(
                array(),
                array('foo'=>''),
                ''
            ),
            array(
                array('foo'=>true, 'bar'=>true),
                array('foo'=>true, 'bar'=>true),
                'foo;bar'
            ),
            array(
                array('foo'=>false, 'bar'=>true),
                array('foo'=>true, 'bar'=>true),
                'bar'
            ),
            array(
                array('foo'=>true, 'bar'=>'true'),
                array(),
                'foo;bar',
            ),
            array(
                array('foo'=>'abc'),
                array('foo'=>'abc'),
                ''
            ),
            array(
                array('foo'=>'cba'),
                array('foo'=>'abc'),
                'foo=cba'
            ),
            array(
                array('foo'=>'abc', 'bar'=>true, 'baz'=>2),
                array(),
                'foo=abc;bar;baz=2',
            ),
        );
    }

    /**
     * @dataProvider getUnserializedToSerializedAsStringWithDefParam
     */
    function testSerializeAsStringWithDefParam($data, $defaultValues, $expectedSerialized) {
        $this->assertEquals(
            $expectedSerialized,
            ModuleStatusDeclaration::serializeParametersAsString($data, $defaultValues)
        );
    }

    static function getUnserializedToSerializedAsArray() {
        return array(
            array(
                array(),
                array(),
            ),
            array(
                array('foo'=>true),
                array('foo'=>true),
            ),
            array(
                array('foo'=>''),
                array('foo'=>''),
            ),
            array(
                array('foo'=>true, 'bar'=>true),
                array('foo'=>true, 'bar'=>true),
            ),
            array(
                array('foo'=>true, 'bar'=>'true'),
                array('foo'=>true, 'bar'=>'true'),
            ),
            array(
                array('foo'=>'abc'),
                array('foo'=>'abc'),
            ),
            array(
                array('foo'=>'abc', 'bar'=>true, 'baz'=>2),
                array('foo'=>'abc', 'bar'=>true, 'baz'=>2),
            ),
            array(
                array('foo'=>'abc', 'bar'=>array('a','b','c'), 'baz'=>2),
                array('foo'=>'abc', 'bar'=>'[a,b,c]', 'baz'=>2),
            ),
            array(
                array('foo'=>'abc', 'bar'=>array('a'), 'baz'=>2),
                array('foo'=>'abc', 'bar'=>'[a]', 'baz'=>2),
            ),
            array(
                array('foo'=>false),
                array('foo'=>false),
            ),
        );
    }

    /**
     * @dataProvider getUnserializedToSerializedAsArray
     */
    function testSerializeAsArray($data, $expectedSerialized) {
        $this->assertEquals(
            $expectedSerialized,
            ModuleStatusDeclaration::serializeParametersAsArray($data)
        );
    }

    static function getUnserializedToSerializedAsArrayWithDefParam() {
        return array(
            array(
                array(),
                array(),
                array(),
            ),
            array(
                array('foo'=>true),
                array(),
                array(),
            ),
            array(
                array('foo'=>false),
                array(),
                array(),
            ),
            array(
                array('foo'=>false),
                array('foo'=>false),
                array(),
            ),
            array(
                array(),
                array('foo'=>true),
                array('foo'=>true),
            ),
            array(
                array('foo'=>true),
                array('foo'=>true),
                array(),
            ),
            array(
                array('foo'=>false),
                array('foo'=>true),
                array('foo'=>true),
            ),
            array(
                array('foo'=>true),
                array('foo'=>false),
                array('foo'=>false),
            ),
            array(
                array('foo'=>'bar'),
                array('foo'=>''),
                array('foo'=>''),
            ),
            array(
                array('foo'=>'bar'),
                array(),
                array(),
            ),
            array(
                array('foo'=>''),
                array(),
                array(),
            ),
            array(
                array('foo'=>true, 'bar'=>true),
                array('foo'=>true, 'bar'=>true),
                array(),
            ),
            array(
                array('foo'=>true, 'bar'=>true),
                array('foo'=>false, 'bar'=>true),
                array('foo'=>false),
            ),
            array(
                array(),
                array('foo'=>true, 'bar'=>'true'),
                array('foo'=>true, 'bar'=>'true'),
            ),
            array(
                array('foo'=>'abc'),
                array('foo'=>'abc'),
                array(),
            ),
            array(
                array('foo'=>'abc'),
                array('foo'=>'cba'),
                array('foo'=>'cba'),
            ),
            array(
                array('defaultgroups' => true,
                    'defaultuser' => false,
                ),
                array('defaultgroups' => true,
                    'defaultuser' => false,
                ),
                array(),

            )
        );
    }

    /**
     * @dataProvider getUnserializedToSerializedAsArrayWithDefParam
     */
    function testSerializeAsArrayWithDefParam($defaultParam, $data, $expectedSerialized) {
        $this->assertEquals(
            $expectedSerialized,
            ModuleStatusDeclaration::serializeParametersAsArray($data, $defaultParam)
        );
    }
}