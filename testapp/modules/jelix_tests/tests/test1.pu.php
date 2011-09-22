<?php

class test1Test extends PHPUnit_Framework_TestCase
{

    function testFirst () {
        $this->assertTrue(true);

        $this->assertTrue($GLOBALS['gJCoord'] != null);
        $this->assertTrue($GLOBALS['gJConfig'] != null);
    }
}