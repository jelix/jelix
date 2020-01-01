<?php

class jcacheTest extends \Jelix\UnitTests\UnitTestCase
{
    /**
     */
    public function testNormalizeKey() {
        $this->assertEquals("abé/foo@bar", jCache::normalizeKey("abé/foo@bar"));
        $this->assertEquals("abé/foo_@bar#cf6a9ed074f8e9125365631e1a6d92bca6d30e0e", jCache::normalizeKey("abé/foo€@bar"));
    }
}