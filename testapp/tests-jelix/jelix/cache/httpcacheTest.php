<?php

require_once(JELIX_LIB_CORE_PATH.'/request/jClassicRequest.class.php');

class httpcacheTest extends \Jelix\UnitTests\UnitTestCase
{

    protected $_server;

    function setUp() : void {
        jApp::saveContext();
        self::initClassicRequest(TESTAPP_URL.'index.php');
    }

    function tearDown() : void  {
        jApp::restoreContext();
    }

    /**
     * @covers jResponse::isValidCache
     */ 
    public function testIsValideCacheWithLastModified() {
        $good_date= gmdate('D, d M Y H:i:s \G\M\T', time());
        $wrong_date = gmdate('D, d M Y H:i:s \G\M\T', time() + 100);
        $_SERVER['HTTP_If_Modified_Since'] = $good_date;

        $rep = jApp::coord()->request->getResponse('html');
        $this->assertNotNull(jApp::coord()->request);
        $this->assertInstanceOf('jResponse', $rep);
        $this->assertEquals($good_date, jApp::coord()->request->header('If-Modified-Since'));
        
        $unusedHeaderValue = 'test123456';
        $rep->addHttpHeader('Content-Language', $unusedHeaderValue);

        $this->assertTrue($rep->isValidCache($good_date));
        $this->assertFalse($rep->isValidCache($wrong_date));
        
        //test the deletion of unused headers
        $this->assertNotContains($unusedHeaderValue, $rep->getHttpHeaders());
    }
    
    /**
     * @covers jResponse::isValidCache
     */ 
    public function testIsValideCacheWithEtag() {
        $good_etag = 'abcdef';
        $wrong_etag = 'vwxyz';
        $_SERVER['HTTP_If_None_Match'] = $good_etag;

        $rep = jApp::coord()->request->getResponse('html');
        $this->assertNotNull(jApp::coord()->request);
        $this->assertInstanceOf('jResponse', $rep);
        $this->assertEquals($good_etag, jApp::coord()->request->header('If-None-Match'));
        
        $this->assertTrue($rep->isValidCache(null, $good_etag));
        $this->assertFalse($rep->isValidCache(null, $wrong_etag));
    }
    
    /**
     * @covers jResponse::setLifeTime
     */ 
    public function testSetLifeTime(){

        $rep = jApp::coord()->request->getResponse('html');

        $rep->setLifeTime(30);
        $value = 'private, maxage=30';
        $expected_headers = array('Cache-Control' => $value, 'Expires' => '', 'Pragma' => '');
        $this->assertEquals($expected_headers, $rep->getHttpHeaders());
        
        $expected_headers = null;
        
        $rep->setLifeTime(10, true);
        $value = 'public, s-maxage=10';
        $expected_headers = array('Cache-Control' => $value, 'Expires' => '', 'Pragma' => '');
        $this->assertEquals($expected_headers, $rep->getHttpHeaders());
    }
    
    /**
     * @covers jResponse::setExpires
     */ 
    public function testSetExpires(){

        $rep = jApp::coord()->request->getResponse('html');

        $good_date= gmdate('D, d M Y H:i:s \G\M\T', time());

        $rep->setExpires($good_date);

        $expected_headers = array('Cache-Control' => '', 'Expires' => $good_date, 'Pragma' => '');
        $this->assertEquals($expected_headers, $rep->getHttpHeaders());
    }
    
    /**
     * @covers jResponse::_normalizeDate
     */ 
    public function testNormalizeDate(){

        if(class_exists('ReflectionMethod')){
            $rep = jApp::coord()->request->getResponse('html');

            $method = new ReflectionMethod('jResponse', '_normalizeDate');
            if (version_compare(phpversion(), '8.1.0', '<')) {
                $method->setAccessible(TRUE);
            }

            //case jDateTime
            $date1 = "2011-10-26 13:00:00";
            $dt = new jDateTime();
            $dt->setFromString($date1, jDateTime::DB_DTFORMAT);
            $this->assertEquals( gmdate('D, d M Y H:i:s \G\M\T', strtotime($date1)), $method->invoke($rep, $dt));

            //case DateTime
            $date2 = '2011-10-26 10:00:00';
            $dt = new DateTime($date2);
            $this->assertEquals( gmdate('D, d M Y H:i:s \G\M\T', strtotime($date2)), $method->invoke($rep, $dt));

            //case strtotime
            $date3 = '2011-10-26 05:02:02';
            $this->assertEquals(gmdate('D, d M Y H:i:s \G\M\T', strtotime($date3)), $method->invoke($rep, $date3));
        }
    }
    
    
    /**
     * @covers jResponse::_checkRequestType
     */
    public function testCheckRequestType(){
        
        $rep = jApp::coord()->request->getResponse('html');

        $method = new ReflectionMethod('jResponse', '_checkRequestType');
        if (version_compare(phpversion(), '8.1.0', '<')) {
            $method->setAccessible(TRUE);
        }

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertTrue($method->invoke($rep));

        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        $this->assertTrue($method->invoke($rep));

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertFalse($method->invoke($rep));
    }
    
}
