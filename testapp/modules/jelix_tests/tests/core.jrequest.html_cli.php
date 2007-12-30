<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/



class requestTest extends jRequest {

 protected function _initParams() {}


}

class UTjrequest extends jUnitTestCase {
    protected $currentServer;

    function setUp() {
        $this->currentServer = $_SERVER;
        $this->currentConfigScriptName = $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'];
    }

    function tearDown() {
        $_SERVER = $this->currentServer;
        $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'] = $this->currentConfigScriptName;
    }

    // /foo/index.php, CGI,  cgi.fix_pathinfo=0
    function testSimpleUrl_CGI_0_REDIRECT_URL() {

        $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'] = 'REDIRECT_URL';
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
        $req->init();

        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    //  /foo/index.php, CGI cgi.fix_pathinfo=1
    function testSimpleUrl_CGI_1_SCRIPT_NAME() {
        $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';// 'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
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
        $req->init();

        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    // /foo/index.php CGI+SUPHP cgi.fix_pathinfo=0
    function testSimpleUrl_SUPHP_0_SCRIPT_NAME() {
        $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME'; //'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
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
        $req->init();

        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    // /foo/index.php , CGI+SUPHP,  cgi.fix_pathinfo=1
    function testSimpleUrl_SUPHP_1_SCRIPT_NAME() {

        $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME'; //'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
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
        $req->init();

        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    // /foo/index.php mod apache
    function testSimpleUrl_MODPHP5_SCRIPT_NAME() {
        $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
        //don't change $_SERVER values : it correspond to a real case
        $_SERVER = array (
            'PHP_SELF' => '/foo/index.php',
            'QUERY_STRING' => '',
            'REQUEST_URI' => '/foo/index.php',
            'SCRIPT_FILENAME' => '/opt/tests/foo/index.php',
            'SCRIPT_NAME' => '/foo/index.php',
        );
        $req = new requestTest();
        $req->init();

        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    // /foo/index.php/bla CGI cgi.fix_pathinfo=1
    function testPathInfo_CGI_1_SCRIPT_NAME() {
        $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
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
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }
    // /foo/index.php/bla CGI+SUPHP cgi.fix_pathinfo=0
    function testPathInfo_SUPHP_0_SCRIPT_NAME() {
        $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
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
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }
    // /foo/index.php/bla CGI+SUPHP cgi.fix_pathinfo=1
    function testPathInfo_SUPHP_1_SCRIPT_NAME() {
        $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
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
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }
    // /foo/index.php/bla MOD_PHP5
    function testPathInfo_MODPHP5_SCRIPT_NAME() {
        $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
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
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    //  /foo/bla where index.php is in foo/, CGI cgi.fix_pathinfo=1
    function testRewrite_CGI_1_SCRIPT_NAME() {
        $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
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
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    //  /foo/bla where index.php is in foo/, CGI+SUPHP cgi.fix_pathinfo=0
    function testRewrite_SUPHP_0_SCRIPT_NAME() {
        $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
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
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    //  /foo/bla where index.php is in foo/, CGI+SUPHP cgi.fix_pathinfo=1
    function testRewrite_SUPHP_1_ORIG_SCRIPT_NAME() {
        $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'] = 'ORIG_SCRIPT_NAME';
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
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }

    //  /foo/bla where index.php is in foo/, MOD_PHP5
    function testRewrite_MODPHP5_SCRIPT_NAME() {
        $GLOBALS['gJConfig']->urlengine['scriptNameServerVariable'] = 'SCRIPT_NAME';//'REDIRECT_URL'; 'ORIG_SCRIPT_NAME';
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
        $req->init();
        $this->assertEqual('/foo/', $req->urlScriptPath);
        $this->assertEqual('index.php', $req->urlScriptName);
        $this->assertEqual('/bar', $req->urlPathInfo);
        $this->assertEqual('/foo/index.php', $req->urlScript);
    }
}

?>