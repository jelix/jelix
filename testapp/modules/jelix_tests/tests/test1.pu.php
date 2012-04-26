<?php

class test1Test extends PHPUnit_Framework_TestCase
{

    function testFirst () {
        $this->assertTrue(true);

        $this->assertTrue(jApp::coord() != null);
        $this->assertTrue(jApp::config() != null);
    }
}