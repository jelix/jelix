<?php

require_once(JELIX_LIB_PATH.'core/response/jResponseHtml.class.php');
require_once(JELIX_LIB_PATH.'plugins/htmlresponse/minify/minify.htmlresponse.php');

class testMinifyHTMLResponsePlugin extends minifyHTMLResponsePlugin {

    function testGenerateMinifyList($list, $exclude) {
        return $this->generateMinifyList($list, $exclude);
    }
}


class minifyHTMLResponsePluginTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        jelix_init_test_env();
    }

    function testStaticJs () {
        $minOptions = array( 'type' => 'text/javascript' );
        ksort($minOptions);
        $inputUrls = array('http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js' => $minOptions,
                            'http://ajax.googleapis.com/ajax/libs/dojo/1.6.0/dojo/dojo.xd.js' => $minOptions);

        $htmlRep = new jResponseHtml();
        $minifyHTMLResponsePluginTester = new testMinifyHTMLResponsePlugin( $htmlRep );

        $minifyList = $minifyHTMLResponsePluginTester->testGenerateMinifyList( $inputUrls, 'excludeJS' );
        $this->assertEquals($minifyList, $inputUrls);
    }

    function testStaticCss () {
        $minOptions = array( 'media' => 'screen' , 'type' => 'text/css' );
        ksort($minOptions);
        $inputUrls = array('http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.0/themes/base/jquery-ui.css' => $minOptions,
                            'http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.0/themes/black-tie/jquery-ui.css' => $minOptions);

        $htmlRep = new jResponseHtml();
        $minifyHTMLResponsePluginTester = new testMinifyHTMLResponsePlugin( $htmlRep );

        $minifyList = $minifyHTMLResponsePluginTester->testGenerateMinifyList( $inputUrls, 'excludeCSS' );
        $this->assertEquals($minifyList, $inputUrls);
    }


    function testRelativeJs () {
        $minOptions = array( 'type' => 'text/javascript' );
        ksort($minOptions);
        $inputUrls = array('www/testminify/js/s1.css' => $minOptions,
                           'www/testminify/js/s2.css' => $minOptions);

        $htmlRep = new jResponseHtml();
        $minifyHTMLResponsePluginTester = new testMinifyHTMLResponsePlugin( $htmlRep );

        $minifyList = $minifyHTMLResponsePluginTester->testGenerateMinifyList( $inputUrls, 'excludeJS' );
        $this->assertEquals($minifyList, array( 'minify.php?f=www/testminify/js/s1.css,www/testminify/js/s2.css' => $minOptions ));
    }

    function testRelativeCss () {
        $minOptions = array( 'media' => 'screen' , 'type' => 'text/css' );
        ksort($minOptions);
        $inputUrls = array('www/testminify/css/style1.css' => $minOptions,
                           'www/testminify/css/style2.css' => $minOptions);

        $htmlRep = new jResponseHtml();
        $minifyHTMLResponsePluginTester = new testMinifyHTMLResponsePlugin( $htmlRep );

        $minifyList = $minifyHTMLResponsePluginTester->testGenerateMinifyList( $inputUrls, 'excludeCSS' );
        $this->assertEquals($minifyList, array( 'minify.php?f=www/testminify/css/style1.css,www/testminify/css/style2.css' => $minOptions ));
    }




    function testRelativeJsDifferentOptions () {
        $minOptions1 = array( 'type' => 'text/javascript', 'charset' => 'UTF-8' );
        ksort($minOptions1);
        $minOptions2 = array( 'type' => 'text/javascript', 'charset' => 'ISO-8859-1' );
        ksort($minOptions2);
        $inputUrls = array('www/testminify/js/s1.css' => $minOptions1,
                           'www/testminify/js/s2.css' => $minOptions2);

        $htmlRep = new jResponseHtml();
        $minifyHTMLResponsePluginTester = new testMinifyHTMLResponsePlugin( $htmlRep );

        $minifyList = $minifyHTMLResponsePluginTester->testGenerateMinifyList( $inputUrls, 'excludeJS' );
        $this->assertEquals($minifyList, array( 'minify.php?f=www/testminify/js/s1.css' => $minOptions1,
                                                'minify.php?f=www/testminify/js/s2.css' => $minOptions2 ));
    }

    function testRelativeCssDifferentOptions () {
        $minOptions1 = array( 'media' => 'screen' , 'type' => 'text/css' );
        ksort($minOptions1);
        $minOptions2 = array( 'media' => 'print' , 'type' => 'text/css' );
        ksort($minOptions2);
        $inputUrls = array('www/testminify/css/style1.css' => $minOptions1,
                           'www/testminify/css/style2.css' => $minOptions2);

        $htmlRep = new jResponseHtml();
        $minifyHTMLResponsePluginTester = new testMinifyHTMLResponsePlugin( $htmlRep );

        $minifyList = $minifyHTMLResponsePluginTester->testGenerateMinifyList( $inputUrls, 'excludeCSS' );
        $this->assertEquals($minifyList, array( 'minify.php?f=www/testminify/css/style1.css' => $minOptions1,
                                                'minify.php?f=www/testminify/css/style2.css' => $minOptions2 ));
    }



}
