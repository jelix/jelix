<?php

/**
 * @package     testapp
 * @subpackage  unittest module
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2007-2022 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

require_once(JELIX_LIB_UTILS_PATH . 'jDatatype.class.php');

class jDatatypeTest extends \PHPUnit\Framework\TestCase
{

    function testString()
    {
        $dt = new jDatatypeString();

        $this->assertTrue($dt->check('aaa'));
        $this->assertEquals('aaa', $dt->getFilteredValue());
        $this->assertTrue($dt->check(''));
        $this->assertTrue($dt->check(null));

        $dt->addFacet('length', 3);
        $this->assertFalse($dt->check(null));
        $this->assertFalse($dt->check(''));
        $this->assertFalse($dt->check('a'));
        $this->assertFalse($dt->check('aa'));
        $this->assertTrue($dt->check('aaa'));
        $this->assertEquals('aaa', $dt->getFilteredValue());

        $this->assertFalse($dt->check('aaaa'));
        $this->assertFalse($dt->check('aaaaa'));

        $dt = new jDatatypeString();
        $dt->addFacet('maxLength', 3);
        $this->assertTrue($dt->check(null));
        $this->assertTrue($dt->check(''));
        $this->assertTrue($dt->check('a'));
        $this->assertTrue($dt->check('aa'));
        $this->assertTrue($dt->check('aaa'));
        $this->assertFalse($dt->check('aaaa'));
        $this->assertFalse($dt->check('aaaaa'));

        $dt = new jDatatypeString();
        $dt->addFacet('minLength', 3);
        $this->assertFalse($dt->check(null));
        $this->assertFalse($dt->check(''));
        $this->assertFalse($dt->check('a'));
        $this->assertFalse($dt->check('aa'));
        $this->assertFalse($dt->check('aé'));
        $this->assertTrue($dt->check('aéa'));
        $this->assertTrue($dt->check('aaa'));
        $this->assertTrue($dt->check('aaaa'));
        $this->assertTrue($dt->check('aaaaa'));

        $dt = new jDatatypeString();
        $dt->addFacet('pattern', '/^\d+$/');
        $this->assertFalse($dt->check(null));
        $this->assertFalse($dt->check(''));
        $this->assertFalse($dt->check('a'));
        $this->assertFalse($dt->check('aéa'));
        $this->assertTrue($dt->check('123'));
        $this->assertTrue($dt->check('0'));
        $this->assertTrue($dt->check('654654654'));

        $dt = new jDatatypeString();
        $dt->addFacet('filterHtml', true);
        $this->assertTrue($dt->check('aaa'));
        $this->assertTrue($dt->check(''));
        $this->assertTrue($dt->check(null));
        $this->assertTrue($dt->check("aa<b>ccc</b>ddd<br/>\n<strong>enough</strong>"));
        $this->assertEquals("aacccddd enough", $dt->getFilteredValue());
    }

    function testBoolean()
    {
        $dt = new jDatatypeBoolean();
        $this->assertTrue($dt->check('true'));
        $this->assertTrue($dt->check('false'));
        $this->assertTrue($dt->check('1'));
        $this->assertTrue($dt->check('0'));
        $this->assertTrue($dt->check('TRUE'));
        $this->assertTrue($dt->check('FALSE'));
        $this->assertTrue($dt->check('on'));
        $this->assertTrue($dt->check('off'));
        $this->assertFalse($dt->check('offqsd'));
        $this->assertFalse($dt->check('tru'));
        $this->assertFalse($dt->check(''));
    }

    function testDecimal()
    {
        $dt = new jDatatypeDecimal();

        $this->assertTrue($dt->check('1'), "jDatatypeDecimal::check('1')");
        $this->assertTrue($dt->check('13213313'), "jDatatypeDecimal::check('13213313')");
        $this->assertTrue($dt->check('132.13313'), "jDatatypeDecimal::check('132.13313')");
        $this->assertTrue($dt->check('-13213313'), "jDatatypeDecimal::check('-13213313')");
        $this->assertTrue($dt->check('-132.13313'), "jDatatypeDecimal::check('-132.13313')");
        $this->assertTrue($dt->check('9813'), "jDatatypeDecimal::check('9813')");
        $this->assertTrue($dt->check('98.13'), "jDatatypeDecimal::check('98.13')");
        $this->assertTrue($dt->check('11'), "jDatatypeDecimal::check('11')");
        $this->assertTrue($dt->check('8.9'), "jDatatypeDecimal::check('8.9')");
        $this->assertFalse($dt->check(''), "jDatatypeDecimal::check('')");
        $this->assertFalse($dt->check('a'), "jDatatypeDecimal::check('a')");
        $this->assertFalse($dt->check('465abd598'), "jDatatypeDecimal::check('465abd598')");
        $this->assertFalse($dt->check('132.133.13'), "jDatatypeDecimal::check('132.133.13')");

        $dt->addFacet('maxValue', 150);
        $this->assertTrue($dt->check('1'), "jDatatypeDecimal::check('1')");
        $this->assertFalse($dt->check('13213313'), "jDatatypeDecimal::check('13213313')");
        $this->assertTrue($dt->check('132.13313'), "jDatatypeDecimal::check('132.13313')");
        $this->assertTrue($dt->check('-13213313'), "jDatatypeDecimal::check('-13213313')");
        $this->assertTrue($dt->check('-132.13313'), "jDatatypeDecimal::check('-132.13313')");
        $this->assertFalse($dt->check('9813'), "jDatatypeDecimal::check('9813')");
        $this->assertTrue($dt->check('98.13'), "jDatatypeDecimal::check('98.13')");
        $this->assertTrue($dt->check('11'), "jDatatypeDecimal::check('11')");
        $this->assertTrue($dt->check('8.9'), "jDatatypeDecimal::check('8.9')");
        $this->assertFalse($dt->check(''), "jDatatypeDecimal::check('')");
        $this->assertFalse($dt->check('a'), "jDatatypeDecimal::check('a')");
        $this->assertFalse($dt->check('465abd598'), "jDatatypeDecimal::check('465abd598')");
        $this->assertFalse($dt->check('132.133.13'), "jDatatypeDecimal::check('132.133.13')");

        $dt->addFacet('minValue', 20);
        $this->assertFalse($dt->check('1'), "jDatatypeDecimal::check('1')");
        $this->assertFalse($dt->check('13213313'), "jDatatypeDecimal::check('13213313')");
        $this->assertTrue($dt->check('132.13313'), "jDatatypeDecimal::check('132.13313')");
        $this->assertFalse($dt->check('-13213313'), "jDatatypeDecimal::check('-13213313')");
        $this->assertFalse($dt->check('-132.13313'), "jDatatypeDecimal::check('-132.13313')");
        $this->assertFalse($dt->check('9813'), "jDatatypeDecimal::check('9813')");
        $this->assertTrue($dt->check('98.13'), "jDatatypeDecimal::check('98.13')");
        $this->assertFalse($dt->check('11'), "jDatatypeDecimal::check('11')");
        $this->assertFalse($dt->check('8.9'), "jDatatypeDecimal::check('8.9')");
        $this->assertFalse($dt->check(''), "jDatatypeDecimal::check('')");
        $this->assertFalse($dt->check('a'), "jDatatypeDecimal::check('a')");
        $this->assertFalse($dt->check('465abd598'), "jDatatypeDecimal::check('465abd598')");
        $this->assertFalse($dt->check('132.133.13'), "jDatatypeDecimal::check('132.133.13')");
    }

    function testInt()
    {
        $dt = new jDatatypeInteger();

        $this->assertTrue($dt->check('1'), "jDatatypeInteger::check('1')");
        $this->assertTrue($dt->check('13213313'), "jDatatypeInteger::check('13213313')");
        $this->assertTrue($dt->check('-13213313'), "jDatatypeInteger::check('-13213313')");
        $this->assertTrue($dt->check('9813'), "jDatatypeInteger::check('9813')");
        $this->assertTrue($dt->check('11'), "jDatatypeInteger::check('11')");
        $this->assertTrue($dt->check('8'), "jDatatypeInteger::check('8')");
        $this->assertFalse($dt->check(''), "jDatatypeInteger::check('')");
        $this->assertFalse($dt->check('a'), "jDatatypeInteger::check('a')");
        $this->assertFalse($dt->check('465abd598'), "jDatatypeInteger::check('465abd598')");

        $dt->addFacet('maxValue', 150);
        $this->assertTrue($dt->check('1'), "jDatatypeInteger::check('1')");
        $this->assertFalse($dt->check('13213313'), "jDatatypeInteger::check('13213313')");
        $this->assertTrue($dt->check('-13213313'), "jDatatypeInteger::check('-13213313')");
        $this->assertFalse($dt->check('9813'), "jDatatypeInteger::check('9813')");
        $this->assertTrue($dt->check('11'), "jDatatypeInteger::check('11')");
        $this->assertTrue($dt->check('8'), "jDatatypeInteger::check('8')");
        $this->assertFalse($dt->check(''), "jDatatypeInteger::check('')");
        $this->assertFalse($dt->check('a'), "jDatatypeInteger::check('a')");
        $this->assertFalse($dt->check('465abd598'), "jDatatypeInteger::check('465abd598')");

        $dt->addFacet('minValue', 20);
        $this->assertFalse($dt->check('1'), "jDatatypeInteger::check('1')");
        $this->assertFalse($dt->check('13213313'), "jDatatypeInteger::check('13213313')");
        $this->assertFalse($dt->check('-13213313'), "jDatatypeInteger::check('-13213313')");
        $this->assertFalse($dt->check('9813'), "jDatatypeInteger::check('9813')");
        $this->assertFalse($dt->check('11'), "jDatatypeInteger::check('11')");
        $this->assertTrue($dt->check('130'), "jDatatypeInteger::check('8')");
        $this->assertFalse($dt->check(''), "jDatatypeInteger::check('')");
        $this->assertFalse($dt->check('a'), "jDatatypeInteger::check('a')");
        $this->assertFalse($dt->check('465abd598'), "jDatatypeInteger::check('465abd598')");
    }
    /*
    function testHexa() {
        $dt=new jDatatypeHexadecimal();
    }

    function testDateTime() {
        $dt=new jDatatypeDateTime();
    }

    function testDate() {
        $dt=new jDatatypeDate();
    }

    function testTime() {
        $dt=new jDatatypeTime();
    }

    function testLocalDateTime() {
        $dt=new jDatatypeLocaleDateTime();
    }

    function testLocalDate() {
        $dt=new jDatatypeLocaleDate();
    }

    function testLocalTime() {
        $dt=new jDatatypeLocaleTime();
    }
    */
}
