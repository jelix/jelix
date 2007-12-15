<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Florian Hatat
* @contributor
* @copyright   2007 Florian Hatat
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class testjDateTime extends UnitTestCase {

    function testValidConstruct(){
        // Retraite des Suisses à Marignan
        $dt = new jDateTime(1515, 9, 14, 11, 01, 29);
        $this->assertEqual($dt->year, 1515);
        $this->assertEqual($dt->month, 9);
        $this->assertEqual($dt->day, 14);
        $this->assertEqual($dt->hour, 11);
        $this->assertEqual($dt->minute, 1);
        $this->assertEqual($dt->second, 29);
    }

    // A random test, to check that the internal representation is faithful.
    function testRandom(){
        $date = getdate(rand());
        $dt = new jDateTime($date['year'], $date['mon'], $date['mday'],
            $date['hours'], $date['minutes'], $date['seconds']);
        $this->assertEqual($dt->year, $date['year']);
        $this->assertEqual($dt->month, $date['mon']);
        $this->assertEqual($dt->day, $date['mday']);
        $this->assertEqual($dt->hour, $date['hours']);
        $this->assertEqual($dt->minute, $date['minutes']);
        $this->assertEqual($dt->second, $date['seconds']);
    }

    // Tests for leap years
    function testLeap(){
        $dt = new jDateTime(1996, 2, 29, 0, 0, 0);
        $this->pass();
    }

    function testLeapCentury(){
        try{
            $dt = new jDateTime(1900, 2, 29, 0, 0, 0);
            $this->fail("Date 1900-02-29 has been incorrectly validated");
        }
        catch(jException $e){
          $this->pass();
        }
    }

    function testLeap400(){
        $dt = new jDateTime(2000, 2, 29, 0, 0, 0);
        $this->pass();
    }

    // Test on some out-of-range data
    function testInvalidMonth(){
        try{
            $dt = new jDateTime(2003, 14, 1, 0, 0, 0);
            $this->fail("Month '14' incorrectly accepted");
        }
        catch(jException $e){
            $this->pass();
        }
    }

    function testInvalidDay(){
        try{
            $dt = new jDateTime(2005, 4, 31, 0, 0, 0);
            $this->fail("Day '31' in April incorrectly accepted");
        }
        catch(jException $e){
            $this->pass();
        }
    }

    function testInvalidHour(){
        try{
            $dt = new jDateTime(0, 0, 0, 42, 17, 6);
            $this->fail("Hour '42' wrongly accepted");
        }
        catch(jException $e){
            $this->pass();
        }
    }

    function testInvalidMinute(){
        try{
            $dt = new jDateTime(0, 0, 0, 13, 61, 12);
            $this->fail("Minute '61' wrongly accepted");
        }
        catch(jException $e){
            $this->pass();
        }
    }

    function testInvalidSecond(){
        try{
            $dt = new jDateTime(0, 0, 0, 14, 37, 78);
            $this->fail("Second '78' wrongly accepted");
        }
        catch(jException $e){
            $this->pass();
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
        $this->assertEqual($dt->toString(jDateTime::DB_DFORMAT), "2007-12-25");
        $this->assertEqual($dt->toString(jDateTime::DB_DTFORMAT),
            "2007-12-25 20:30:19");
        $this->assertEqual($dt->toString(jDateTime::DB_TFORMAT), "20:30:19");
        $this->assertEqual($dt->toString(jDateTime::ISO8601_FORMAT),
            "2007-12-25T20:30:19Z");
        $this->assertEqual($dt->toString(jDateTime::TIMESTAMP_FORMAT),
            "1198611019");
        $this->assertEqual($dt->toString(jDateTime::RFC822_FORMAT), "Tue, 25 Dec 2007 20:30:19 +0100");
    }

    // Tests string parsing.
    function testsetFromString(){
        // Time when my guests leave.
        $dt = new jDateTime();

        $dt->setFromString("2007-12-26", jDateTime::DB_DFORMAT);
        $this->assertEqual(new jDateTime(2007, 12, 26, 0, 0, 0), $dt);

        $dt->setFromString("2007-12-26 05:17:25", jDateTime::DB_DTFORMAT);
        $this->assertEqual(new jDateTime(2007, 12, 26, 5, 17, 25), $dt);

        $dt->setFromString("05:17:25", jDateTime::DB_TFORMAT);
        $this->assertEqual(new jDateTime(0, 0, 0, 5, 17, 25), $dt);

        $dt->setFromString("2007-12-26T05:17:25Z", jDateTime::ISO8601_FORMAT);
        $this->assertEqual(new jDateTime(2007, 12, 26, 5, 17, 25), $dt);

        $dt->setFromString("1198642645", jDateTime::TIMESTAMP_FORMAT);
        $this->assertEqual(new jDateTime(2007, 12, 26, 5, 17, 25), $dt);

        // Beware of the time zone
        $dt->setFromString("Wed, 26 Dec 2007 05:17:25 +0100",
            jDateTime::RFC822_FORMAT);
        $this->assertEqual(new jDateTime(2007, 12, 26, 4, 17, 25), $dt);
    }
}
?>
