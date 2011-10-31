<?php

require_once(JELIX_LIB_CORE_PATH.'/request/jClassicRequest.class.php');

class httpcacheUnitTest extends PHPUnit_Framework_TestCase
{

    public function setUp(){
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['REMOTE_ADDR']);
        unset($_SERVER['HTTP_If_Modified_Since']);
    }

    /**
     * @covers jResponse::isValidCache
     */ 
    public function testIsValideCacheWithLastModified() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $good_date= gmdate('D, d M Y H:i:s \G\M\T', time());
        $wrong_date = gmdate('D, d M Y H:i:s \G\M\T', time() + 100);
        $_SERVER['HTTP_If_Modified_Since'] = $good_date;
        
        
        $GLOBALS['gJCoord']->request = new jClassicRequest();
        $rep = $GLOBALS['gJCoord']->request->getResponse('html');
        $this->assertNotNull($GLOBALS['gJCoord']->request);
        $this->assertInstanceOf('jResponse', $rep);
        $this->assertEquals($GLOBALS['gJCoord']->request->header('If-Modified-Since'), $good_date);
        
        $unusedHeaderValue = 'test123456';
        $rep->addHttpHeader('Content-Language', $unusedHeaderValue);

        $this->assertTrue($rep->isValidCache($good_date));
        $this->assertFalse($rep->isValidCache($wrong_date));
        
        //test the suppresion of unused headers
        $this->assertAttributeNotContains($unusedHeaderValue, '_httpHeaders', $rep);
    }
    
    /**
     * @covers jResponse::isValidCache
     */ 
    public function testIsValideCacheWithEtag() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $good_etag = 'abcdef';
        $wrong_etag = 'vwxyz';
        $_SERVER['HTTP_If_None_Match'] = $good_etag;
        
        
        $GLOBALS['gJCoord']->request = new jClassicRequest();
        $rep = $GLOBALS['gJCoord']->request->getResponse('html');
        $this->assertNotNull($GLOBALS['gJCoord']->request);
        $this->assertInstanceOf('jResponse', $rep);
        $this->assertEquals($GLOBALS['gJCoord']->request->header('If-None-Match'), $good_etag);
        
        $this->assertTrue($rep->isValidCache(null, $good_etag));
        $this->assertFalse($rep->isValidCache(null, $wrong_etag));
    }
    
    /**
     * @covers jResponse::setLifeTime
     */ 
    public function testSetLifeTime(){
        
        //prepare
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $GLOBALS['gJCoord']->request = new jClassicRequest();
        $rep = $GLOBALS['gJCoord']->request->getResponse('html');
        
       
        $rep->setLifeTime(30);
        $value = 'private, maxage=30';
        $expected_headers = array('Cache-Control' => $value, 'Expires' => '', 'Pragma' => '');
        $this->assertAttributeEquals($expected_headers, '_httpHeaders', $rep);
        
        $expected_headers = null;
        
        $rep->setLifeTime(10, true);
        $value = 'public, s-maxage=10';
        $expected_headers = array('Cache-Control' => $value, 'Expires' => '', 'Pragma' => '');
        $this->assertAttributeEquals($expected_headers, '_httpHeaders', $rep);
    }
    
    /**
     * @covers jResponse::setExpires
     */ 
    public function testSetExpires(){
        
        //prepare
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $GLOBALS['gJCoord']->request = new jClassicRequest();
        $rep = $GLOBALS['gJCoord']->request->getResponse('html');
        
        $good_date= gmdate('D, d M Y H:i:s \G\M\T', time());
        
        $rep->setExpires($good_date);
        
        $expected_headers = array('Cache-Control' => '', 'Expires' => $good_date, 'Pragma' => '');
        $this->assertAttributeEquals($expected_headers, '_httpHeaders', $rep);
    }
    
    /**
     * @covers jResponse::_normalizeDate
     */ 
    public function testNormalizeDate(){

        if(class_exists('ReflectionMethod')){
            $GLOBALS['gJCoord']->request = new jClassicRequest();
            $rep = $GLOBALS['gJCoord']->request->getResponse('html');
            
            $method = new ReflectionMethod('jResponse', '_normalizeDate');
            $method->setAccessible(TRUE);
            
            //case jDateTime
            $date1 = "2011-10-26 13:00:00";
            $dt = new jDateTime();
            $dt->setFromString($date1, jDateTime::DB_DTFORMAT);
            $this->assertEquals( $method->invoke($rep, $dt) , gmdate('D, d M Y H:i:s \G\M\T', strtotime($date1)));

            //case DateTime
            $date2 = '2011-10-26 10:00:00';
            $dt = new DateTime($date2);
            $this->assertEquals( $method->invoke($rep, $dt) , gmdate('D, d M Y H:i:s \G\M\T', strtotime($date2)));

            //case strtotime
            $date3 = '2011-10-26 05:02:02';
            $this->assertEquals( $method->invoke($rep, $date3) , gmdate('D, d M Y H:i:s \G\M\T', strtotime($date3)));
        }
    }
    
    
    /**
     * @covers jResponse::_checkRequestType
     * @expectedException PHPUnit_Framework_Error
     */ 
    public function testCheckRequestType(){
        
        if(class_exists('ReflectionMethod')){
            //prepare
            $GLOBALS['gJCoord']->request = new jClassicRequest();
            $rep = $GLOBALS['gJCoord']->request->getResponse('html');
        
            $method = new ReflectionMethod('jResponse', '_checkRequestType');
            $method->setAccessible(TRUE);
     
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $this->assertTrue($method->invoke($rep));
     
            $_SERVER['REQUEST_METHOD'] = 'HEAD';
            $this->assertTrue($method->invoke($rep));
            
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $method->invoke($rep);
        }
        else
            trigger_error('you dont support the ReflexionMethod class'); //for not fail at the assertion
    }
    
}