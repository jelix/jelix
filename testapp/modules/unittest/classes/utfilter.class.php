<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTfilter extends UnitTestCase {

    public function testHeader(){
        if(jFilter::usePhpFilter()){
            $this->sendMessage('use php filter');
        }else{
            $this->sendMessage('use jelix filter');
        }
    }

    public function testIsInt(){
        $this->assertTrue(jFilter::isInt('1'), "isInt('1')");
        $this->assertTrue(jFilter::isInt('13213313'), "isInt('13213313')");
        $this->assertTrue(jFilter::isInt('-13213313'), "isInt('-13213313')");
        $this->assertTrue(jFilter::isInt('9813',12,15246), "isInt('9813',12,15246)");
        $this->assertTrue(jFilter::isInt('11',5), "isInt('11',5)");
        $this->assertTrue(jFilter::isInt('8',null,9), "isInt('8',null,9)");

        $this->assertFalse(jFilter::isInt(''), "isInt('')");
        $this->assertFalse(jFilter::isInt('a'), "isInt('a')");
        $this->assertFalse(jFilter::isInt('465abd598'), "isInt('465abd598')");
        $this->assertFalse(jFilter::isInt('11',12,15246), "isInt('11',12,15246)");
        $this->assertFalse(jFilter::isInt('11',5,9), "isInt('11',5,9)");

    }

    public function testIsHexInt(){
        $this->assertFalse(jFilter::isHexInt(''), "isHexInt('')");
        $this->assertFalse(jFilter::isHexInt('a'), "isHexInt('a')");
        $this->assertFalse(jFilter::isHexInt('465abd598'), "isHexInt('465abd598')");
        $this->assertTrue(jFilter::isHexInt('0x1'), "isHexInt('0x1')");
        $this->assertTrue(jFilter::isHexInt('0x13213313'), "isHexInt('0x13213313')");
        $this->assertTrue(jFilter::isHexInt('0x132abD13'), "isHexInt('0x132abD13')");
        $this->assertFalse(jFilter::isHexInt('-13213313'), "isHexInt('-13213313')");
        $this->assertTrue(jFilter::isHexInt('0x9813',0x12,0x15246), "isHexInt('0x9813',0x12,0x15246)");
        $this->assertFalse(jFilter::isHexInt('0x11',0x12,0x15246), "isHexInt('0x11',0x12,0x15246)");
        $this->assertFalse(jFilter::isHexInt('0x11',0x5,0x9), "isHexInt('0x11',0x5,0x9)");
        $this->assertTrue(jFilter::isHexInt('0x11',0x5), "isHexInt('0x11',0x5)");
        $this->assertTrue(jFilter::isHexInt('0x8',null,0x9), "isHexInt('0x8',null,0x9)");
    }

    public function testIsBool(){
        $this->assertTrue(jFilter::isBool('true'), "isBool('true')");
        $this->assertTrue(jFilter::isBool('false'), "isBool('false')");
        $this->assertTrue(jFilter::isBool('1'), "isBool('1')");
        $this->assertTrue(jFilter::isBool('0'), "isBool('0')");
        $this->assertTrue(jFilter::isBool('TRUE'), "isBool('TRUE')");
        $this->assertTrue(jFilter::isBool('FALSE'), "isBool('FALSE')");
        $this->assertTrue(jFilter::isBool('on'), "isBool('on')");
        $this->assertTrue(jFilter::isBool('off'), "isBool('off')");
        $this->assertFalse(jFilter::isBool('offqsd'), "isBool('offqsd')");
        $this->assertFalse(jFilter::isBool('tru'), "isBool('tru')");
        $this->assertFalse(jFilter::isBool(''), "isBool('')");
    }

    public function testIsFloat(){
        $this->assertTrue(jFilter::isFloat('1'), "isFloat('1')");
        $this->assertTrue(jFilter::isFloat('13213313'), "isFloat('13213313')");
        $this->assertTrue(jFilter::isFloat('132.13313'), "isFloat('132.13313')");
        $this->assertTrue(jFilter::isFloat('-13213313'), "isFloat('-13213313')");
        $this->assertTrue(jFilter::isFloat('-132.13313'), "isFloat('-132.13313')");
        $this->assertTrue(jFilter::isFloat('9813',12,15246), "isFloat('9813',12,15246)");
        $this->assertTrue(jFilter::isFloat('98.13',12.5,152.46), "isFloat('98.13',12.5,152.46)");
        $this->assertTrue(jFilter::isFloat('11',5), "isFloat('11',5)");
        $this->assertTrue(jFilter::isFloat('8.9',null,9), "isFloat('8.9',null,9)");
        $this->assertFalse(jFilter::isFloat(''), "isFloat('')");
        $this->assertFalse(jFilter::isFloat('a'), "isFloat('a')");
        $this->assertFalse(jFilter::isFloat('465abd598'), "isFloat('465abd598')");
        $this->assertFalse(jFilter::isFloat('132.133.13'), "isFloat('132.133.13')");
        $this->assertFalse(jFilter::isFloat('11',12,15246), "isFloat('11',12,15246)");
        $this->assertFalse(jFilter::isFloat('11',5,9), "isFloat('11',5,9)");
    }

    public function testIsUrl(){
        $this->assertTrue(jFilter::isUrl('http://foo.com/bar.html?a=b&c=d'),
                                  "isUrl('http://foo.com/bar.html?a=b&c=d')");
        $this->assertTrue(jFilter::isUrl('http://foo.com/bar.html'),
                                  "isUrl('http://foo.com/bar.html')");
        $this->assertTrue(jFilter::isUrl('http://foo.com'),
                                  "isUrl('http://foo.com')");
        $this->assertTrue(jFilter::isUrl('foo.com'),
                                  "isUrl('foo.com')");
        $this->assertTrue(jFilter::isUrl('abcdfeh'),
                                  "isUrl('abcdfeh')");
        $this->assertTrue(jFilter::isUrl('bar.html?a=b&c=d'),
                                  "isUrl('bar.html?a=b&c=d')");
        $this->assertTrue(jFilter::isUrl('foo.com/bar.html?a=b&c=d'),
                                  "isUrl('foo.com/bar.html?a=b&c=d')");
        $this->assertTrue(jFilter::isUrl('foo$^.com/bar.html?a=b&c=d'),
                                  "isUrl('foo$^.com/bar.html?a=b&c=d')");
        $this->assertTrue(jFilter::isUrl('http://foo$^.com/bar.html?a=b&c=d'),
                                  "isUrl('http://foo$^.com/bar.html?a=b&c=d')");
        $this->assertFalse(jFilter::isUrl('http://'),
                                  "isUrl('http://')");

/*        $this->assertTrue(jFilter::isUrl(''),
                                  "isUrl('')");
        $this->assertTrue(jFilter::isUrl(''),
                                  "isUrl('')");
        $this->assertTrue(jFilter::isUrl(''),
                                  "isUrl('')");
        $this->assertTrue(jFilter::isUrl(''),
                                  "isUrl('')");
        $this->assertTrue(jFilter::isUrl(''),
                                  "isUrl('')");*/
    }

    public function testIsIpv4(){
        $this->assertTrue(jFilter::isIPv4('0.0.0.0'), "isIPv4('0.0.0.0')");
        $this->assertTrue(jFilter::isIPv4('127.0.0.1'), "isIPv4('127.0.0.1')");
        $this->assertTrue(jFilter::isIPv4('65.98.1.255'), "isIPv4('65.98.1.255')");
        $this->assertTrue(jFilter::isIPv4('255.255.255.255'), "isIPv4('255.255.255.255')");
        $this->assertFalse(jFilter::isIPv4('0'), "isIPv4('0')");
        $this->assertFalse(jFilter::isIPv4('0.0.0'), "isIPv4('0.0.0')");
        $this->assertFalse(jFilter::isIPv4('0.0.'), "isIPv4('0.0.')");
        $this->assertFalse(jFilter::isIPv4('289.127.54.387'), "isIPv4('289.127.54.387')");
        $this->assertFalse(jFilter::isIPv4('1111.1111.22222.3333'), "isIPv4('1111.1111.22222.3333')");
    }

    public function testIsIpv6(){
        $this->assertTrue(jFilter::isIPv6('0:0:0:0:0:0:0:0'), "isIPv6('0:0:0:0:0:0:0:0')");
        $this->assertTrue(jFilter::isIPv6('023:01:A0:cd0:8be0:ffff:0:0'), "isIPv6('023:01:A0:cd0:8be0:ffff:0:0')");
        $this->assertFalse(jFilter::isIPv6('0'), "isIPv6('0')");
        $this->assertFalse(jFilter::isIPv6('023:er1:A0:cd0:8be0:ffff:0:0'), "isIPv6('023:er1:A0:cd0:8be0:ffff:0:0')");
        $this->assertFalse(jFilter::isIPv6('023:8be0:ffff:0:0'), "isIPv6('023:8be0:ffff:0:0')");
        $this->assertFalse(jFilter::isIPv6('023:01:A0:cd0:8be0:ffff:0:0:98'), "isIPv6('023:01:A0:cd0:8be0:ffff:0:0:98')");
    }

}

?>