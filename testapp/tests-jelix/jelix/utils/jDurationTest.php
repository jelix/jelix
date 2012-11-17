<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Florian Hatat
* @copyright   2008 Florian Hatat
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class testjDuration extends UnitTestCase {
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
            $this->assertEqual($dtNow, $dtExpirationDate);
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
        $this->assertEqual($dt, $dtExpected);
    }

    function testRelative1(){
        $dur = new jDuration(array("month" => 1));
        $dt = new jDateTime(2007, 8, 14, 15, 28, 17);
        $dtExpected = new jDateTime(2007, 9, 14, 15, 28, 17);
        $dt->add($dur);
        $this->assertEqual($dt, $dtExpected);
    }

    function testRelative2(){
        $dur = new jDuration(array("month" => 1));
        $dt = new jDateTime(2007, 1, 14, 15, 28, 17);
        $dtExpected = new jDateTime(2006, 12, 14, 15, 28, 17);
        $dt->sub($dur);
        $this->assertEqual($dt, $dtExpected);
    }

    // Add one month to August, 31st, you would get something like "September, 
    // 31st", which gets "rounded" to October, 1st. For the moment, jDuration 
    // has no option to prevent from crossing the "month" barrier.
    function testRelativeTricky(){
        $dur = new jDuration(array("month" => 1));
        $dt = new jDateTime(2007, 8, 31, 15, 28, 17);
        $dtExpected = new jDateTime(2007, 10, 1, 15, 28, 17);
        $dt->add($dur);
        $this->assertEqual($dt, $dtExpected);
    }

    function testNegative(){
        $dur = new jDuration(-20);
        $dt = new jDateTime(2007, 12, 25, 15, 28, 17);
        $dtExpected = new jDateTime(2007, 12, 25, 15, 27, 57);
        $dt->add($dur);
        $this->assertEqual($dt, $dtExpected);
    }

    function testMultiplication(){
        $dur = new jDuration(array("year" => 1));
        $dur->mult(10); // Changed our mind, 10 years is better...
        $dt = new jDateTime(2007, 1, 1, 23, 58, 3);
        $dtExpected = new jDateTime(1997, 1, 1, 23, 58, 3);
        $dt->sub($dur);
        $this->assertEqual($dt, $dtExpected);
    }

    function testLeap1(){
        $dur = new jDuration(array("year" => 8));
        $dt = new jDateTime(2000, 2, 29, 12, 12, 12);
        $dtExpected = new jDateTime(2008, 2, 29, 12, 12, 12);
        $dt->add($dur);
        $this->assertEqual($dt, $dtExpected);
    }

    function testLeap2(){
        $dur = new jDuration(array("year" => 7));
        $dt = new jDateTime(2000, 2, 29, 12, 12, 12);
        $dtExpected = new jDateTime(1993, 3, 1, 12, 12, 12);
        $dt->sub($dur);
        $this->assertEqual($dt, $dtExpected);
    }
    
    function testManySeconds () {
        $dur = new jDuration(98320);
        $this->assertEqual($dur->months, 0);
        $this->assertEqual($dur->days, 1);
        $this->assertEqual($dur->seconds, 11920);
    }
}
?>
