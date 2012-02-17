<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_CORE_PATH.'jConfigCompiler.class.php');


class configCompileTest extends jConfigCompiler {

    static public function prepareConfig2($config){
        self::prepareConfig($config, false, false, '');
    }
}

class requestTest extends jRequest {

 protected function _initParams() {}


}

class UTjrequest extends jUnitTestCase {
    protected $currentServer;
    protected $currentConfig;

    function setUp() {
        $this->currentServer = $_SERVER;
        jApp::saveContext();
        $conf = jApp::config();
        $conf->urlengine['basePath'] = '/';
        $conf->responses=array();
        $conf->_coreResponses=array();
    }

    function tearDown() {
        $_SERVER = $this->currentServer;
        jApp::restoreContext();
    }

    // /foo/index.php, CGI,  cgi.fix_pathinfo=0
    function testSimpleUrl_CGI_0_REDIRECT_URL() {

        jApp::config()->urlengine['scriptNameServerVariable'] = 'REDIRECT_URL';
        //don't change $_SERVER values : it correspond to a real case
        $_SERVER = array(
        'PATH_INFO' => '/foo/index.php',
        'PATH_TRANSLATED' => '/opt/tests/foo/index.php',
        'PHP_SELF' => '/foo/index.php',
        'QUERY_STRING' => '',
        'REDIRECT_URL' => '/foo/index.php',
        'REQUEST_URI' => '/foo/index.php',
        'SCRIPT_FILENAME' => '/usr/lib/cgi-bin/php5',
        'SCRIPT_NAME' => '/cgi-bin/php5',
        );
        $req = new requestTest();
        configCompileTest::prepareConfig2(jApp::config());
        $req->init();

        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    //  /foo/index.php, CGI cgi.fix_pathinfo=1
    function testSimpleUrl_CGI_1_SCRIPT_NAME() {
        jApp::config()->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';// 'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
        //don't change $_SERVER values : it correspond to a real case
        $_SERVER = array(
        'ORIG_PATH_INFO' => '/foo/index.php',
        'ORIG_PATH_TRANSLATED' => '/opt/tests/foo/index.php',
        'ORIG_SCRIPT_FILENAME' => '/usr/lib/cgi-bin/php5',
        'ORIG_SCRIPT_NAME' => '/cgi-bin/php5',
        'PHP_SELF' => '/foo/index.php',
        'QUERY_STRING' => '',
        'REDIRECT_URL' => '/foo/index.php',
        'REQUEST_URI' => '/foo/index.php',
        'SCRIPT_FILENAME' => '/opt/tests/foo/index.php',
        'SCRIPT_NAME' => '/foo/index.php',
        );
        $req = new requestTest();
        configCompileTest::prepareConfig2(jApp::config());
        $req->init();

        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    // /foo/index.php CGI+SUPHP cgi.fix_pathinfo=0
    function testSimpleUrl_SUPHP_0_SCRIPT_NAME() {
        jApp::config()->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME'; //'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
        //don't change $_SERVER values : it correspond to a real case
        $_SERVER = array(
        'PHPRC' => '/etc/php5/cgi/',
        'PHP_SELF' => '/foo/index.php',
        'QUERY_STRING' => '',
        'REQUEST_URI' => '/foo/index.php',
        'SCRIPT_FILENAME' => '/opt/tests/foo/index.php',
        'SCRIPT_NAME' => '/foo/index.php',
        );
        $req = new requestTest();
        configCompileTest::prepareConfig2(jApp::config());
        $req->init();

        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    // /foo/index.php , CGI+SUPHP,  cgi.fix_pathinfo=1
    function testSimpleUrl_SUPHP_1_SCRIPT_NAME() {

        jApp::config()->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME'; //'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
        //don't change $_SERVER values : it correspond to a real case
        $_SERVER = array(
        'PHPRC' => '/etc/php5/cgi/',
        'PHP_SELF' => '/foo/index.php',
        'QUERY_STRING' => '',
        'REQUEST_URI' => '/foo/index.php',
        'SCRIPT_FILENAME' => '/opt/tests/foo/index.php',
        'SCRIPT_NAME' => '/foo/index.php',
        );
        $req = new requestTest();
        configCompileTest::prepareConfig2(jApp::config());
        $req->init();

        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    // /foo/index.php mod apache
    function testSimpleUrl_MODPHP5_SCRIPT_NAME() {
        jApp::config()->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
        //don't change $_SERVER values : it correspond to a real case
        $_SERVER = array (
            'PHP_SELF' => '/foo/index.php',
            'QUERY_STRING' => '',
            'REQUEST_URI' => '/foo/index.php',
            'SCRIPT_FILENAME' => '/opt/tests/foo/index.php',
            'SCRIPT_NAME' => '/foo/index.php',
        );
        $req = new requestTest();
        configCompileTest::prepareConfig2(jApp::config());
        $req->init();

        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    // /foo/index.php/bla CGI cgi.fix_pathinfo=1
    function testPathInfo_CGI_1_SCRIPT_NAME() {
        jApp::config()->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
        //don't change $_SERVER values : it correspond to a real case
        $_SERVER = array (
            'ORIG_PATH_INFO' => '/foo/index.php/bar',
            'ORIG_PATH_TRANSLATED' => '/opt/tests/foo/index.php/bar',
            'ORIG_SCRIPT_FILENAME' => '/usr/lib/cgi-bin/php5',
            'ORIG_SCRIPT_NAME' => '/cgi-bin/php5',
            'PATH_INFO' => '/bar',
            'PATH_TRANSLATED' => '/opt/tests/bar',
            'PHP_SELF' => '/foo/index.php',
            'QUERY_STRING' => '',
            'REDIRECT_URL' => '/foo/index.php/bar',
            'REQUEST_URI' => '/foo/index.php/bar',
            'SCRIPT_FILENAME' => '/opt/tests/foo/index.php',
            'SCRIPT_NAME' => '/foo/index.php',
        );
        $req = new requestTest();
        configCompileTest::prepareConfig2(jApp::config());
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }
    // /foo/index.php/bla CGI+SUPHP cgi.fix_pathinfo=0
    function testPathInfo_SUPHP_0_SCRIPT_NAME() {
        jApp::config()->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
        //don't change $_SERVER values : it correspond to a real case
        $_SERVER = array (
            'PATH_INFO' => '/bar',
            'PATH_TRANSLATED' => '/opt/tests/foo/index.php',
            'PHPRC' => '/etc/php5/cgi/',
            'PHP_SELF' => '/bar',
            'QUERY_STRING' => '',
            'REQUEST_URI' => '/foo/index.php/bar',
            'SCRIPT_FILENAME' => '/opt/tests/foo/index.php',
            'SCRIPT_NAME' => '/foo/index.php',
        );
        $req = new requestTest();
        configCompileTest::prepareConfig2(jApp::config());
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }
    // /foo/index.php/bla CGI+SUPHP cgi.fix_pathinfo=1
    function testPathInfo_SUPHP_1_SCRIPT_NAME() {
        jApp::config()->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
        //don't change $_SERVER values : it correspond to a real case
        $_SERVER = array (
            'ORIG_PATH_INFO' => '/bar',
            'ORIG_PATH_TRANSLATED' => '/opt/tests/foo/index.php',
            'PHPRC' => '/etc/php5/cgi/',
            'PHP_SELF' => '/foo/index.php',
            'QUERY_STRING' => '',
            'REQUEST_URI' => '/foo/index.php/bar',
            'SCRIPT_FILENAME' => '/opt/tests/foo/index.php',
            'SCRIPT_NAME' => '/foo/index.php',
        );
        $req = new requestTest();
        configCompileTest::prepareConfig2(jApp::config());
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }
    // /foo/index.php/bla MOD_PHP5
    function testPathInfo_MODPHP5_SCRIPT_NAME() {
        jApp::config()->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
        //don't change $_SERVER values : it correspond to a real case
        $_SERVER = array (
            'PATH_INFO' => '/bar',
            'PATH_TRANSLATED' => '/opt/tests/bar',
            'PHP_SELF' => '/foo/index.php/bar',
            'QUERY_STRING' => '',
            'REQUEST_URI' => '/foo/index.php/bar',
            'SCRIPT_FILENAME' => '/opt/tests/foo/index.php',
            'SCRIPT_NAME' => '/foo/index.php',
        );
        $req = new requestTest();
        configCompileTest::prepareConfig2(jApp::config());
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    //  /foo/bla where index.php is in foo/, CGI cgi.fix_pathinfo=1
    function testRewrite_CGI_1_SCRIPT_NAME() {
        jApp::config()->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
        //don't change $_SERVER values : it correspond to a real case
        $_SERVER = array (
            'ORIG_PATH_INFO' => '/foo/index.php/bar',
            'ORIG_PATH_TRANSLATED' => '/opt/tests/foo/index.php/bar',
            'ORIG_SCRIPT_FILENAME' => '/usr/lib/cgi-bin/php5',
            'ORIG_SCRIPT_NAME' => '/cgi-bin/php5',
            'PATH_INFO' => '/bar',
            'PATH_TRANSLATED' => '/opt/tests/bar',
            'PHP_SELF' => '/foo/index.php',
            'QUERY_STRING' => '',
            'REDIRECT_URL' => '/foo/index.php/bar',
            'REQUEST_URI' => '/foo/bar',
            'SCRIPT_FILENAME' => '/opt/tests/foo/index.php',
            'SCRIPT_NAME' => '/foo/index.php',
        );
        $req = new requestTest();
        configCompileTest::prepareConfig2(jApp::config());
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    //  /foo/bla where index.php is in foo/, CGI+SUPHP cgi.fix_pathinfo=0
    function testRewrite_SUPHP_0_SCRIPT_NAME() {
        jApp::config()->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
        //don't change $_SERVER values : it correspond to a real case
        $_SERVER = array (
             'PATH_INFO' => '/bar',
            'PATH_TRANSLATED' => '/opt/tests/foo/index.php',
            'PHPRC' => '/etc/php5/cgi/',
            'PHP_SELF' => '/bar',
            'QUERY_STRING' => '',
            'REDIRECT_URL' => '/foo/bar',
            'REQUEST_URI' => '/foo/bar',
            'SCRIPT_FILENAME' => '/opt/tests/foo/index.php',
            'SCRIPT_NAME' => '/foo/index.php',
       );
        $req = new requestTest();
        configCompileTest::prepareConfig2(jApp::config());
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    //  /foo/bla where index.php is in foo/, CGI+SUPHP cgi.fix_pathinfo=1
    function testRewrite_SUPHP_1_ORIG_SCRIPT_NAME() {
        jApp::config()->urlengine['scriptNameServerVariable'] = 'ORIG_SCRIPT_NAME';
        //don't change $_SERVER values : it correspond to a real case
        $_SERVER = array (
             'ORIG_PATH_INFO' => '/bar',
            'ORIG_PATH_TRANSLATED' => '/opt/tests/foo/index.php',
            'ORIG_SCRIPT_FILENAME' => '/opt/tests/foo/index.php',
            'ORIG_SCRIPT_NAME' => '/foo/index.php',
            'PHPRC' => '/etc/php5/cgi/',
            'PHP_SELF' => '/foo/bar',
            'QUERY_STRING' => '',
            'REDIRECT_URL' => '/foo/bar',
            'REQUEST_URI' => '/foo/bar',
            'SCRIPT_FILENAME' => '/opt/tests/foo/index.php',
            'SCRIPT_NAME' => '/foo/bar',
       );
        $req = new requestTest();
        configCompileTest::prepareConfig2(jApp::config());
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    //  /foo/bla where index.php is in foo/, MOD_PHP5
    function testRewrite_MODPHP5_SCRIPT_NAME() {
        jApp::config()->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
        //don't change $_SERVER values : it correspond to a real case
        $_SERVER = array (
            'PATH_INFO' => '/bar',
            'PATH_TRANSLATED' => '/opt/tests/bar',
            'PHP_SELF' => '/foo/index.php/bar',
            'QUERY_STRING' => '',
            'REDIRECT_URL' => '/foo/bar',
            'REQUEST_URI' => '/foo/bar',
            'SCRIPT_FILENAME' => '/opt/tests/foo/index.php',
            'SCRIPT_NAME' => '/foo/index.php',
        );
        $req = new requestTest();
        configCompileTest::prepareConfig2(jApp::config());
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }


    function testGetServerURI_HTTP_80() {
        $request = jApp::coord()->request;
        $config = jApp::config();
        $config->domainName = 'foo.local';
        unset($_SERVER['HTTPS']);
        $_SERVER['SERVER_PORT'] = '80';


        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '';

        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '';
        $this->assertEqual('http://foo.local:8080', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('http://foo.local:8080', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('http://foo.local:8080', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '443';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = true;
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = true;
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = true;
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = true;
        $this->assertEqual('http://foo.local:8080', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));
    }

    function testGetServerURI_HTTP_8082() {

        $request = jApp::coord()->request;
        $config = jApp::config();
        $config->domainName = 'foo.local';
        unset($_SERVER['HTTPS']);
        $_SERVER['SERVER_PORT'] = '8082';

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '';

        $this->assertEqual('http://foo.local:8082', $request->getServerURI());
        $this->assertEqual('http://foo.local:8082', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '';
        $this->assertEqual('http://foo.local:8080', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('http://foo.local:8080', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('http://foo.local:8082', $request->getServerURI());
        $this->assertEqual('http://foo.local:8082', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('http://foo.local:8080', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('http://foo.local:8082', $request->getServerURI());
        $this->assertEqual('http://foo.local:8082', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '443';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = true;
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = true;
        $this->assertEqual('http://foo.local:8082', $request->getServerURI());
        $this->assertEqual('http://foo.local:8082', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = true;
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = true;
        $this->assertEqual('http://foo.local:8080', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));
    }
    
    function testGetServerURI_HTTPS_443() {

        $request = jApp::coord()->request;
        $config = jApp::config();
        $config->domainName = 'foo.local';

        // ----
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '443';
        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '';

        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('https://foo.local:4433', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('https://foo.local:4433', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('https://foo.local:4433', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '443';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('https://foo.local:4433', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = true;
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = true;
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = true;
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = true;
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));
    }//bad
    
    function testGetServerURI_HTTPS_4435() {

        $config = jApp::config();
        $request = jApp::coord()->request;
        $config->domainName = 'foo.local';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '4435';

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '';

        $this->assertEqual('https://foo.local:4435', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4435', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '';
        $this->assertEqual('https://foo.local:4435', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4435', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '';
        $this->assertEqual('https://foo.local:4435', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4435', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('https://foo.local:4433', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('https://foo.local:4433', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('https://foo.local:4433', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '';
        $this->assertEqual('https://foo.local:4435', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4435', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '443';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('https://foo.local:4433', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = true;
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = true;
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = true;
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = true;
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));
    }
    
    function testGetServerURI_HTTPS_80() {

        $config = jApp::config();
        $request = jApp::coord()->request;
        $config->domainName = 'foo.local';

        // special ugly case
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '80';

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '';

        $this->assertEqual('https://foo.local:80', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:80', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '';
        $this->assertEqual('https://foo.local:80', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:80', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '';
        $this->assertEqual('https://foo.local:80', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:80', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('https://foo.local:4433', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('https://foo.local:4433', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('https://foo.local:4433', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '';
        $this->assertEqual('https://foo.local:80', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:80', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '443';
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('https://foo.local:4433', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = true;
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = true;
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = true;
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = true;
        $this->assertEqual('https://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));
    }
    
    function testGetServerURI_HTTP_443() {

        $config = jApp::config();
        $request = jApp::coord()->request;
        $config->domainName = 'foo.local';
        // special ugly case
        unset($_SERVER['HTTPS']);
        $_SERVER['SERVER_PORT'] = '443';

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '';

        $this->assertEqual('http://foo.local:443', $request->getServerURI());
        $this->assertEqual('http://foo.local:443', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '';
        $this->assertEqual('http://foo.local:8080', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('http://foo.local:8080', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('http://foo.local:443', $request->getServerURI());
        $this->assertEqual('http://foo.local:443', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '443';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('http://foo.local:8080', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('http://foo.local:443', $request->getServerURI());
        $this->assertEqual('http://foo.local:443', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '443';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '4433';
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = true;
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = true;
        $this->assertEqual('http://foo.local:443', $request->getServerURI());
        $this->assertEqual('http://foo.local:443', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = true;
        $this->assertEqual('http://foo.local', $request->getServerURI());
        $this->assertEqual('http://foo.local', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = true;
        $this->assertEqual('http://foo.local:8080', $request->getServerURI());
        $this->assertEqual('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEqual('https://foo.local', $request->getServerURI(true));
    }
}

