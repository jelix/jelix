<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Florian Hatat
* @contributor Laurent Jouanneau
* @copyright   2008 Florian Hatat, 2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jDurationTest extends \PHPUnit\Framework\TestCase {
    function testReportedBug(){
        // Get now date/time
        $dtNow = new jDateTime();
        $dtNow->setFromString("2008-02-08 00:27:22", jDateTime::DB_DTFORMAT);

        // Get expiration date
        $expirationDate = "2008-02-12 00:00:00";

        // Create date time object from DB date
        $dtExpirationDate = new jDateTime();
        $dtExpirationDate->setFromString($expirationDate, jDateTime::DB_DTFORMAT);

        // Compute difference between these dates
        try
        {
            $countdown = $dtNow->durationTo($dtExpirationDate);
            $dtNow->add($countdown);
            $this->assertEquals($dtExpirationDate, $dtNow);
        }
        catch (Exception $e)
        {
            $this->fail("Rejected duration construction");
        }
    }

    function testPositive(){
        $dur = new jDuration(20);
        $dt = new jDateTime(2007, 12, 25, 15, 28, 17);
        $dtExpected = new jDateTime(2007, 12, 25, 15, 28, 37);
        $dt->add($dur);
        $this->assertEquals($dtExpected, $dt);
    }

    function testRelative1(){
        $dur = new jDuration(array("month" => 1));
        $dt = new jDateTime(2007, 8, 14, 15, 28, 17);
        $dtExpected = new jDateTime(2007, 9, 14, 15, 28, 17);
        $dt->add($dur);
        $this->assertEquals($dtExpected, $dt);
    }

    function testRelative2(){
        $dur = new jDuration(array("month" => 1));
        $dt = new jDateTime(2007, 1, 14, 15, 28, 17);
        $dtExpected = new jDateTime(2006, 12, 14, 15, 28, 17);
        $dt->sub($dur);
        $this->assertEquals($dtExpected, $dt);
    }

    // Add one month to August, 31st, you would get something like "September, 
    // 31st", which gets "rounded" to October, 1st. For the moment, jDuration 
    // has no option to prevent from crossing the "month" barrier.
    function testRelativeTricky(){
        $dur = new jDuration(array("month" => 1));
        $dt = new jDateTime(2007, 8, 31, 15, 28, 17);
        $dtExpected = new jDateTime(2007, 10, 1, 15, 28, 17);
        $dt->add($dur);
        $this->assertEquals($dtExpected, $dt);
    }

    function testNegative(){
        $dur = new jDuration(-20);
        $dt = new jDateTime(2007, 12, 25, 15, 28, 17);
        $dtExpected = new jDateTime(2007, 12, 25, 15, 27, 57);
        $dt->add($dur);
        $this->assertEquals($dtExpected, $dt);
    }

    function testMultiplication(){
        $dur = new jDuration(array("year" => 1));
        $dur->mult(10); // Changed our mind, 10 years is better...
        $dt = new jDateTime(2007, 1, 1, 23, 58, 3);
        $dtExpected = new jDateTime(1997, 1, 1, 23, 58, 3);
        $dt->sub($dur);
        $this->assertEquals($dtExpected, $dt);
    }

    function testLeap1(){
        $dur = new jDuration(array("year" => 8));
        $dt = new jDateTime(2000, 2, 29, 12, 12, 12);
        $dtExpected = new jDateTime(2008, 2, 29, 12, 12, 12);
        $dt->add($dur);
        $this->assertEquals($dtExpected, $dt);
    }

    function testLeap2(){
        $dur = new jDuration(array("year" => 7));
        $dt = new jDateTime(2000, 2, 29, 12, 12, 12);
        $dtExpected = new jDateTime(1993, 3, 1, 12, 12, 12);
        $dt->sub($dur);
        $this->assertEquals($dtExpected, $dt);
    }
    
    function testManySeconds () {
        $dur = new jDuration(98320);
        $this->assertEquals(0, $dur->months);
        $this->assertEquals(1, $dur->days);
        $this->assertEquals(11920, $dur->seconds);
    }
}

