<?php

class test1Test extends PHPUnit\Framework\TestCase
{

    function testFirst () {
        $this->assertTrue(true);

        $this->assertTrue(jApp::coord() != null);
        $this->assertTrue(jApp::config() != null);
    }
}