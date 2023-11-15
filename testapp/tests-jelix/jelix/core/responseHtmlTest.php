<?php

require_once(JELIX_LIB_PATH.'core/response/jResponseHtml.class.php');


class htmlRespTest extends jResponseHtml {

    function __construct (){
        $this->_locale = 'en_US';
        $this->_lang = 'en';
        $this->body = new jTpl();
        $this->webAssetsSelection = new \Jelix\WebAssets\WebAssetsSelection();
    }

    protected function sendHttpHeaders(){ $this->_httpHeadersSent=true; }
}


class responseHtmlTest extends \Jelix\UnitTests\UnitTestCase
{
    protected $oldserver;

    function setUp() : void  {
        $this->oldserver = $_SERVER;
        jApp::saveContext();
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        parent::setUp();
    }

    function tearDown() : void  {
        jApp::popCurrentModule();
        jApp::restoreContext();
        $_SERVER = $this->oldserver;
    }

    function testAddJSCSS()
    {
        $conf = jApp::config();
        $conf->domainName = 'testapp.local';
        $conf->forceHTTPPort = true;
        $conf->forceHTTPSPort = true;
        $conf->urlengine['assetsRevision'] = '';
        $conf->urlengine['assetsRevQueryUrl'] = "";

        $resp = new htmlRespTest();

        $resp->addJSLink('/assets/foo.js');
        $resp->addJSLink('/assets/bar.js', array('defer'=>true));
        $resp->addJSLink('https://example.com/var.js');
        $resp->addJSLinkModule('testapp', 'test.js');
        $resp->addCSSLink('/assets/foo.css');
        $resp->addCSSLink('https://example.com/oo.css');
        $resp->addCSSLinkModule('testapp', 'hello.css');

        $this->assertEquals([
            '/assets/foo.js'=> array(),
            'https://example.com/var.js'=> array(),
            '/assets/bar.js' => array ('defer'=>true),
            '/index.php/jelix/res/testapp/test.js' => array ()
        ] , $resp->getJSLinks());
        $this->assertEquals([
            '/assets/foo.css' => array (),
            'https://example.com/oo.css' => array (),
            '/index.php/jelix/res/testapp/hello.css' => array (),
        ] , $resp->getCSSLinks());
    }

    function testAddJSCSSWithRevision()
    {
        $conf = jApp::config();
        $conf->domainName = 'testapp.local';
        $conf->forceHTTPPort = true;
        $conf->forceHTTPSPort = true;
        $conf->urlengine['assetsRevision'] = '123';
        $conf->urlengine['assetsRevQueryUrl'] = "_r=123";
        $conf->urlengine['assetsRevisionParameter'] = "_r";

        $resp = new htmlRespTest();
        $resp->addJSLink('/assets/foo.js');
        $resp->addJSLink('/assets/bar.js', array('defer'=>true));
        $resp->addJSLink('https://example.com/var.js');
        $resp->addJSLinkModule('testapp', 'test.js');
        $resp->addCSSLink('/assets/foo.css');
        $resp->addCSSLink('https://example.com/oo.css');
        $resp->addCSSLinkModule('testapp', 'hello.css');

        $this->assertEquals([
            '/assets/foo.js?_r=123'=> array(),
            'https://example.com/var.js'=> array(),
            '/assets/bar.js?_r=123' => array ('defer'=>true),
            '/index.php/jelix/res/testapp/test.js?_r=123' => array ()
        ] , $resp->getJSLinks());
        $this->assertEquals([
            '/assets/foo.css?_r=123' => array (),
            'https://example.com/oo.css' => array (),
            '/index.php/jelix/res/testapp/hello.css?_r=123' => array (),
        ] , $resp->getCSSLinks());
    }


}