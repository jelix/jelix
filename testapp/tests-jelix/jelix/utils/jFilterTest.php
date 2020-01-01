<?php
/**
* @package     testapp
* @subpackage  testsjelix
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jFilterTest extends \PHPUnit\Framework\TestCase {

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
    }

    public function testCleanHtml(){

        $html='<div>lorem</div>';
        $result='<div>lorem</div>';
        $this->assertEquals($result, jFilter::cleanHtml($html));

        $html='<div>lorem<em>aaa</em></div>';
        $result="<div>lorem<em>aaa</em>\n</div>";
        $this->assertEquals($result, jFilter::cleanHtml($html));

        $html='<div>lorem <script> foo </script></div>';
        $result='<div>lorem </div>';
        $this->assertEquals($result, jFilter::cleanHtml($html));

        $html='<div>lorem <SCRIPT> foo </SCRIPT></div>';
        $result='<div>lorem </div>';
        $this->assertEquals($result, jFilter::cleanHtml($html));

        //$html='<div>lorem <![CDATA[<SCRIPT> foo </SCRIPT>]]></div>';
        //$result='<div>lorem <![CDATA[<SCRIPT> foo </SCRIPT>]]></div>';
        //$this->assertEquals($result, jFilter::satanizeHtml($html));

        $html='<div onclick="xss()">lorem</div>';
        $result='<div>lorem</div>';
        $this->assertEquals($result, jFilter::cleanHtml($html));

        $html='<div onclick="xss()">lorem <strong onMouseOver="toto()">ah ah </strong></div>';
        $result="<div>lorem <strong>ah ah </strong>\n</div>";
        $this->assertEquals($result, jFilter::cleanHtml($html));

        $html='<div onclick="xss()">lorem <a href="javascript:pirate()">ah ah </a></div>';
        $result="<div>lorem <a>ah ah </a>\n</div>";
        $this->assertEquals($result, jFilter::cleanHtml($html));

        $html='<div>lorem <a href="foo/bar">a</a> <a href="http://foo/bar">a</a> <a href="hTTps://foo/bar">a</a>
         <a href="ftp://foo/bar">a</a>  <a href="mailto:foo@bar.baz">a</a>  <a href="foo/bar:/bla">a</a>
         <a href="foo:bar/bla">a</a> <a href="data:bar/bla">a</a></div>';
        $result='<div>lorem <a href="foo/bar">a</a> <a href="http://foo/bar">a</a> <a href="hTTps://foo/bar">a</a>
         <a href="ftp://foo/bar">a</a>  <a href="mailto:foo@bar.baz">a</a>  <a href="foo/bar:/bla">a</a>
         <a>a</a> <a>a</a>
</div>';
        $this->assertEquals($result, jFilter::cleanHtml($html));

        // invalid html
        $html='<div>lorem<em>aaa</er></div>';
        $result="<div>lorem<em>aaa</em>\n</div>";
        $this->assertEquals($result, jFilter::cleanHtml($html));

        $html='<div lorem<em>aaa</er></div>';
        $result="<div lorem>aaa</div>";
        $this->assertEquals($result, jFilter::cleanHtml($html));

        $html='<div>lorem <br/> ipsum</div>';
        $result='<div>lorem <br> ipsum</div>';
        $this->assertEquals($result, jFilter::cleanHtml($html));

        // XHTML
        $html='<div>lorem <br/> ipsum</div>';
        $result="\n    <div>lorem <br/> ipsum</div>\n  ";
        $this->assertEquals($result, jFilter::cleanHtml($html, true));

        $html='<div lorem<em>aaa</er></div>';
        $result="\n    <div lorem=\"\">aaa</div>\n  ";
        $this->assertEquals($result, jFilter::cleanHtml($html, true));

        $html='<div>lorem<em>aaa</er></div>';
        $result="\n    <div>lorem<em>aaa</em></div>\n  ";
        $this->assertEquals($result, jFilter::cleanHtml($html, true));
    }

}

