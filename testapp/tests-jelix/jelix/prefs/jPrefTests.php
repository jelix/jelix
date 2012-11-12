<?php


class jpref_main_apiTest extends PHPUnit_Framework_TestCase{
    
    /**
     * @covers jPref::set
     * @covers jPref::get
     */
    public function testJPref(){
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