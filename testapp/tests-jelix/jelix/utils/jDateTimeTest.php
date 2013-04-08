<?php
/**
* @package     testapp
* @subpackage  testsjelix
* @author      Florian Hatat
* @contributor Rahal, Laurent Jouanneau
* @copyright   2007 Florian Hatat, 2008 Rahal, 2009-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jDateTimeTest extends PHPUnit_Framework_TestCase {

    function setUp(){
        date_default_timezone_set("Europe/Paris");
    }

    function tearDown(){
        date_default_timezone_set(jApp::config()->timeZone);
    }

    function testValidConstruct(){
        // Retraite des Suisses à Marignan
        $dt = new jDateTime(1515, 9, 14, 11, 01, 29);
        $this->assertEquals($dt->year, 1515);
        $this->assertEquals($dt->month, 9);
        $this->assertEquals($dt->day, 14);
        $this->assertEquals($dt->hour, 11);
        $this->assertEquals($dt->minute, 1);
        $this->assertEquals($dt->second, 29);
    }

    // A random test, to check that the internal representation is faithful.
    function testRandom(){
        $date = getdate(rand());
        $dt = new jDateTime($date['year'], $date['mon'], $date['mday'],
            $date['hours'], $date['minutes'], $date['seconds']);
        $this->assertEquals($dt->year, $date['year']);
        $this->assertEquals($dt->month, $date['mon']);
        $this->assertEquals($dt->day, $date['mday']);
        $this->assertEquals($dt->hour, $date['hours']);
        $this->assertEquals($dt->minute, $date['minutes']);
        $this->assertEquals($dt->second, $date['seconds']);
    }

    // Tests for leap years
    function testLeap(){
        $dt = new jDateTime(1996, 2, 29, 0, 0, 0);
        $this->assertTrue(true, 'no exception');
    }

    function testLeapCentury(){
        try{
            $dt = new jDateTime(1900, 2, 29, 0, 0, 0);
            $this->fail("Date 1900-02-29 has been incorrectly validated");
        }
        catch(jException $e){
            $this->assertTrue(true, 'expected exception occurred');
        }
    }

    function testLeap400(){
        $dt = new jDateTime(2000, 2, 29, 0, 0, 0);
        $this->assertTrue(true, 'no exception');
    }

    // Test on some out-of-range data
    function testInvalidMonth(){
        try{
            $dt = new jDateTime(2003, 14, 1, 0, 0, 0);
            $this->fail("Month '14' incorrectly accepted");
        }
        catch(jException $e){
            $this->assertTrue(true, 'expected exception occurred');
        }
    }

    function testInvalidDay(){
        try{
            $dt = new jDateTime(2005, 4, 31, 0, 0, 0);
            $this->fail("Day '31' in April incorrectly accepted");
        }
        catch(jException $e){
            $this->assertTrue(true, 'expected exception occurred');
        }
    }

    function testInvalidHour(){
        try{
            $dt = new jDateTime(0, 0, 0, 42, 17, 6);
            $this->fail("Hour '42' wrongly accepted");
        }
        catch(jException $e){
            $this->assertTrue(true, 'expected exception occurred');
        }
    }

    function testInvalidMinute(){
        try{
            $dt = new jDateTime(0, 0, 0, 13, 61, 12);
            $this->fail("Minute '61' wrongly accepted");
        }
        catch(jException $e){
            $this->assertTrue(true, 'expected exception occurred');
        }
    }

    function testInvalidSecond(){
        try{
            $dt = new jDateTime(0, 0, 0, 14, 37, 78);
            $this->fail("Second '78' wrongly accepted");
        }
        catch(jException $e){
            $this->assertTrue(true, 'expected exception occurred');
        }
    }

    // http://en.wikipedia.org/wiki/Leap_second
    // http://fr.wikipedia.org/wiki/Seconde_intercalaire
    // This test will probably often fail, since most application do not need the 
    // time representation to be that accurate, therefore jDateTime doesn't 
    // implement the leap seconds yet (also, there is no algorithm to compute 
    // when a leap second will occur).
    function testLeapSecond(){
        /*try{
          $dt = new jDateTime(1972, 6, 30, 23, 59, 60);
          $this->pass();
        }
        catch(jException $e){
            $this->fail("Leap second 1972-06-30 23:59:60 wrongly rejected, but this was a hard test");
        }*/
    }

    // Tests string outputs
    function testtoString(){
        // Time when my guests arrive.
        $dt = new jDateTime(2007, 12, 25, 20, 30, 19);
        $this->assertEquals($dt->toString(jDateTime::DB_DFORMAT), "2007-12-25");
        $this->assertEquals($dt->toString("Y-m-d"), "2007-12-25");
        $this->assertEquals($dt->toString(jDateTime::DB_DTFORMAT), "2007-12-25 20:30:19");
        $this->assertEquals($dt->toString("Y-m-d G:i:s"), "2007-12-25 20:30:19");
        $this->assertEquals($dt->toString(jDateTime::DB_TFORMAT), "20:30:19");
        $this->assertEquals($dt->toString("G:i:s"), "20:30:19");
        $this->assertEquals($dt->toString(jDateTime::ISO8601_FORMAT),
            "2007-12-25T20:30:19Z");
        $this->assertEquals($dt->toString(jDateTime::TIMESTAMP_FORMAT),
            "1198611019");
        $this->assertEquals($dt->toString(jDateTime::RFC822_FORMAT), "Tue, 25 Dec 2007 20:30:19 +0100");
    }

    // Tests string parsing.
    function testsetFromString(){
        // Time when my guests leave.
        $dt = new jDateTime();

        $dt->setFromString("2007-12-26", jDateTime::DB_DFORMAT);
        $this->assertEquals(new jDateTime(2007, 12, 26, 0, 0, 0), $dt);

        $dt->setFromString("2007-12-26 05:17:25", jDateTime::DB_DTFORMAT);
        $this->assertEquals(new jDateTime(2007, 12, 26, 5, 17, 25), $dt);

        $dt->setFromString("05:17:25", jDateTime::DB_TFORMAT);
        $this->assertEquals(new jDateTime(0, 0, 0, 5, 17, 25), $dt);

        $dt->setFromString("2007-12-26T05:17:25Z", jDateTime::ISO8601_FORMAT);
        $this->assertEquals(new jDateTime(2007, 12, 26, 5, 17, 25), $dt);

        $dt->setFromString("2007-12-26T05:17:25+01:30", jDateTime::ISO8601_FORMAT);
        $this->assertEquals(new jDateTime(2007, 12, 26, 3, 47, 25), $dt);

        $dt->setFromString("2007-12-26T05:17:25-01:15", jDateTime::ISO8601_FORMAT);
        $this->assertEquals(new jDateTime(2007, 12, 26, 6, 32, 25), $dt);

        $dt->setFromString("1198642645", jDateTime::TIMESTAMP_FORMAT);
        $this->assertEquals(new jDateTime(2007, 12, 26, 5, 17, 25), $dt);

        // Beware of the time zone
        $dt->setFromString("Wed, 26 Dec 2007 05:17:25 +0100",
            jDateTime::RFC822_FORMAT);
        $this->assertEquals(new jDateTime(2007, 12, 26, 4, 17, 25), $dt);
    }
    
    function testOffsetTimeZone() {
        
        $dt = new jDateTime();
        
        $dt->setFromString('2012-04-15T12:00:00+02:00', jDateTime::ISO8601_FORMAT);
        $this->assertEquals(2012, $dt->year);
        $this->assertEquals(4, $dt->month);
        $this->assertEquals(15, $dt->day);
        $this->assertEquals(10, $dt->hour);
        $this->assertEquals(0, $dt->minute);
        $this->assertEquals(0, $dt->second);

        $utc = $dt->toString(jDateTime::ISO8601_FORMAT);
        
        $this->assertEquals('2012-04-15T10:00:00Z', $utc);
        
    }   
}
