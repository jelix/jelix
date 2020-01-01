<?php


class jPrefTest extends \Jelix\UnitTests\UnitTestCase {

    /**
     * @covers jPref::set
     * @covers jPref::get
     */
    public function testJPref(){
        self::initJelixConfig();
        $testArray = array();
        $testArray[] = array('value' => false, 'key' => 'my.bool.value');
        $testArray[] = array('value' => 123, 'key' => 'my.int.value');
        $testArray[] = array('value' => 12.34, 'key' => 'my.float.value');
        $testArray[] = array('value' => 'test', 'key' => 'my.string.value');
        
       foreach($testArray as $el){
            jPref::set($el['key'], $el['value']);
            jPref::clearCache();
            $result = jPref::get($el['key']);
            $this->assertEquals($el['value'], $result);
        }
    }

}