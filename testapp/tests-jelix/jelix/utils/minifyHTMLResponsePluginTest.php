<?php

require_once(JELIX_LIB_PATH.'core/response/jResponseHtml.class.php');
require_once(JELIX_LIB_PATH.'plugins/htmlresponse/minify/minify.htmlresponse.php');

class testMinifyHTMLResponsePlugin extends minifyHTMLResponsePlugin {

    function testGenerateMinifyList($list, $exclude) {
        return $this->generateMinifyList($list, $exclude);
    }


    function setExcludeList( $excludeList, $excludeType ) {
        $this->$excludeType = $excludeList;
    }
}


class minifyHTMLResponsePluginTest extends jUnitTestCase
{
    public static function setUpBeforeClass() {
        self::initJelixConfig();
    }

    function testStaticJs () {
        $minOptions = array( 'type' => 'text/javascript' );
        ksort($minOptions);
        $inputUrls = array('http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js' => $minOptions,
                            'http://ajax.googleapis.com/ajax/libs/dojo/1.6.0/dojo/dojo.xd.js' => $minOptions);

        $htmlRep = new jResponseHtml();
        $minifyHTMLResponsePluginTester = new testMinifyHTMLResponsePlugin( $htmlRep );

        $minifyList = $minifyHTMLResponsePluginTester->testGenerateMinifyList( $inputUrls, 'excludeJS' );
        $this->assertEquals($inputUrls, $minifyList);
    }

    function testStaticCss () {
        $minOptions = array( 'media' => 'screen' , 'type' => 'text/css' );
        ksort($minOptions);
        $inputUrls = array('http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.0/themes/base/jquery-ui.css' => $minOptions,
                            'http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.0/themes/black-tie/jquery-ui.css' => $minOptions);

        $htmlRep = new jResponseHtml();
        $minifyHTMLResponsePluginTester = new testMinifyHTMLResponsePlugin( $htmlRep );

        $minifyList = $minifyHTMLResponsePluginTester->testGenerateMinifyList( $inputUrls, 'excludeCSS' );
        $this->assertEquals($inputUrls, $minifyList);
    }


    function testRelativeJs () {
        $minOptions = array( 'type' => 'text/javascript' );
        ksort($minOptions);
        $inputUrls = array('testminify/js/s1.js' => $minOptions,
                           'testminify/js/s2.js' => $minOptions);

        $htmlRep = new jResponseHtml();
        $minifyHTMLResponsePluginTester = new testMinifyHTMLResponsePlugin( $htmlRep );

        $minifyList = $minifyHTMLResponsePluginTester->testGenerateMinifyList( $inputUrls, 'excludeJS' );
        $this->assertEquals(array( '/minify.php?f=testminify/js/s1.js,testminify/js/s2.js' => $minOptions ), $minifyList);
    }

    function testRelativeCss () {
        $minOptions = array( 'media' => 'screen' , 'type' => 'text/css' );
        ksort($minOptions);
        $inputUrls = array('testminify/css/style1.css' => $minOptions,
                           'testminify/css/style2.css' => $minOptions);

        $htmlRep = new jResponseHtml();
        $minifyHTMLResponsePluginTester = new testMinifyHTMLResponsePlugin( $htmlRep );

        $minifyList = $minifyHTMLResponsePluginTester->testGenerateMinifyList( $inputUrls, 'excludeCSS' );
        $this->assertEquals(array( '/minify.php?f=testminify/css/style1.css,testminify/css/style2.css' => $minOptions ), $minifyList);
    }




    function testRelativeJsDifferentOptions () {
        $minOptions1 = array( 'type' => 'text/javascript', 'charset' => 'UTF-8' );
        ksort($minOptions1);
        $minOptions2 = array( 'type' => 'text/javascript', 'charset' => 'ISO-8859-1' );
        ksort($minOptions2);
        $inputUrls = array('testminify/js/s1.js' => $minOptions1,
                           'testminify/js/s2.js' => $minOptions2);

        $htmlRep = new jResponseHtml();
        $minifyHTMLResponsePluginTester = new testMinifyHTMLResponsePlugin( $htmlRep );

        $minifyList = $minifyHTMLResponsePluginTester->testGenerateMinifyList( $inputUrls, 'excludeJS' );
        $this->assertEquals(array( '/minify.php?f=testminify/js/s1.js' => $minOptions1,
                                   '/minify.php?f=testminify/js/s2.js' => $minOptions2 ), $minifyList);
    }

    function testRelativeCssDifferentOptions () {
        $minOptions1 = array( 'media' => 'screen' , 'type' => 'text/css' );
        ksort($minOptions1);
        $minOptions2 = array( 'media' => 'print' , 'type' => 'text/css' );
        ksort($minOptions2);
        $inputUrls = array('testminify/css/style1.css' => $minOptions1,
                           'testminify/css/style2.css' => $minOptions2);

        $htmlRep = new jResponseHtml();
        $minifyHTMLResponsePluginTester = new testMinifyHTMLResponsePlugin( $htmlRep );

        $minifyList = $minifyHTMLResponsePluginTester->testGenerateMinifyList( $inputUrls, 'excludeCSS' );
        $this->assertEquals(array( '/minify.php?f=testminify/css/style1.css' => $minOptions1,
                                   '/minify.php?f=testminify/css/style2.css' => $minOptions2 ), $minifyList);
    }



    function testExcludeJs () {
        $minOptions = array( 'type' => 'text/javascript' );
        ksort($minOptions);
        $inputUrls = array('testminify/js/s1.js' => $minOptions,
                           'testminify/js/s2.js' => $minOptions);

        $htmlRep = new jResponseHtml();
        $minifyHTMLResponsePluginTester = new testMinifyHTMLResponsePlugin( $htmlRep );
        $minifyHTMLResponsePluginTester->setExcludeList( array('testminify/js/s1.js'), 'excludeJS' );

        $minifyList = $minifyHTMLResponsePluginTester->testGenerateMinifyList( $inputUrls, 'excludeJS' );
        $this->assertEquals(array( '/minify.php?f=testminify/js/s2.js' => $minOptions, 'testminify/js/s1.js' => $minOptions  ), $minifyList);
    }

    function testExcludeCss () {
        $minOptions = array( 'media' => 'screen' , 'type' => 'text/css' );
        ksort($minOptions);
        $inputUrls = array('testminify/css/style1.css' => $minOptions,
                           'testminify/css/style2.css' => $minOptions);

        $htmlRep = new jResponseHtml();
        $minifyHTMLResponsePluginTester = new testMinifyHTMLResponsePlugin( $htmlRep );
        $minifyHTMLResponsePluginTester->setExcludeList( array('testminify/css/style1.css'), 'excludeCSS' );

        $minifyList = $minifyHTMLResponsePluginTester->testGenerateMinifyList( $inputUrls, 'excludeCSS' );
        $this->assertEquals(array( '/minify.php?f=testminify/css/style2.css' => $minOptions, 'testminify/css/style1.css' => $minOptions ), $minifyList);
    }



}
