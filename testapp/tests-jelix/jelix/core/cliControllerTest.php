<?php



class cliControllerTest extends \PHPUnit\Framework\TestCase
{

    function testEmptyParameters() {
        $cliArgs = array();
        $optionsDecl = array(/* '-option' => true if option accepts a value */);
        $paramsDecl = array(/* 'parametername' => true if optional  */);
        list($options, $parameters) = jCmdUtils::getOptionsAndParams($cliArgs, $optionsDecl, $paramsDecl);
        $this->assertEquals(array(), $options);
        $this->assertEquals(array(), $parameters);
    }


    function testParameters() {
        $cliArgs = array('foo', 'bar');
        $optionsDecl = array();
        $paramsDecl = array('p1'=>true, 'p2'=>true);
        list($options, $parameters) = jCmdUtils::getOptionsAndParams($cliArgs, $optionsDecl, $paramsDecl);
        $this->assertEquals(array(), $options);
        $this->assertEquals(array('p1'=>'foo', 'p2'=>'bar'), $parameters);
    }

    /**
     */
    function testMissingParameters() {
        $cliArgs = array('foo');
        $optionsDecl = array();
        $paramsDecl = array('p1'=>true, 'p2'=>true);
        $this->expectException(jException::class);
        list($options, $parameters) = jCmdUtils::getOptionsAndParams($cliArgs, $optionsDecl, $paramsDecl);
    }

    function testOptionalParameters() {
        $cliArgs = array('foo');
        $optionsDecl = array();
        $paramsDecl = array('p1'=>true, 'p2'=>false);
        list($options, $parameters) = jCmdUtils::getOptionsAndParams($cliArgs, $optionsDecl, $paramsDecl);
        $this->assertEquals(array(), $options);
        $this->assertEquals(array('p1'=>'foo'), $parameters);

        $cliArgs = array('foo', 'bar');
        list($options, $parameters) = jCmdUtils::getOptionsAndParams($cliArgs, $optionsDecl, $paramsDecl);
        $this->assertEquals(array(), $options);
        $this->assertEquals(array('p1'=>'foo', 'p2'=>'bar'), $parameters);
    }


    function testOption() {
        $cliArgs = array('-f', 'foo');
        $optionsDecl = array('-f'=>false);
        $paramsDecl = array('p1'=>true, 'p2'=>false);
        list($options, $parameters) = jCmdUtils::getOptionsAndParams($cliArgs, $optionsDecl, $paramsDecl);
        $this->assertEquals(array('-f'=>true), $options);
        $this->assertEquals(array('p1'=>'foo'), $parameters);
    }

    function testNoOptionInCli() {
        $cliArgs = array('foo');
        $optionsDecl = array('-f'=>false);
        $paramsDecl = array('p1'=>true, 'p2'=>false);
        list($options, $parameters) = jCmdUtils::getOptionsAndParams($cliArgs, $optionsDecl, $paramsDecl);
        $this->assertEquals(array(), $options);
        $this->assertEquals(array('p1'=>'foo'), $parameters);
    }

    function testOptionValue() {
        $cliArgs = array('-f', 'hello', 'foo');
        $optionsDecl = array('-f'=>true);
        $paramsDecl = array('p1'=>true, 'p2'=>false);
        list($options, $parameters) = jCmdUtils::getOptionsAndParams($cliArgs, $optionsDecl, $paramsDecl);
        $this->assertEquals(array('-f'=>'hello'), $options);
        $this->assertEquals(array('p1'=>'foo'), $parameters);
    }

    function testTwoOptions() {
        $cliArgs = array('-truc', '-f', 'hello', 'foo');
        $optionsDecl = array('-f'=>true, '-truc'=>false);
        $paramsDecl = array('p1'=>true, 'p2'=>false);
        list($options, $parameters) = jCmdUtils::getOptionsAndParams($cliArgs, $optionsDecl, $paramsDecl);
        $this->assertEquals(array('-f'=>'hello', '-truc'=>true), $options);
        $this->assertEquals(array('p1'=>'foo'), $parameters);
    }

    function testOptionValueWithDash() {
        $cliArgs = array('-f', '-hello', 'foo');
        $optionsDecl = array('-f'=>true);
        $paramsDecl = array('p1'=>true, 'p2'=>false);
        list($options, $parameters) = jCmdUtils::getOptionsAndParams($cliArgs, $optionsDecl, $paramsDecl);
        $this->assertEquals(array('-f'=>'-hello'), $options);
        $this->assertEquals(array('p1'=>'foo'), $parameters);
    }

    /**
     */
    function testOptionMissingValueWithDash() {
        $cliArgs = array('-f', '-hello', 'foo');
        $optionsDecl = array('-f'=>true, '-hello'=>false);
        $paramsDecl = array('p1'=>true, 'p2'=>false);
        $this->expectException(jException::class);
        list($options, $parameters) = jCmdUtils::getOptionsAndParams($cliArgs, $optionsDecl, $paramsDecl);
    }

    /**
     */
    function testOptionMissingParameter() {
        $cliArgs = array('-f', 'foo');
        $optionsDecl = array('-f'=>true);
        $paramsDecl = array('p1'=>true, 'p2'=>false);
        $this->expectException(jException::class);
        list($options, $parameters) = jCmdUtils::getOptionsAndParams($cliArgs, $optionsDecl, $paramsDecl);
    }

}
