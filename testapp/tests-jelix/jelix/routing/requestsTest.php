<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007-2024 Laurent Jouanneau
* @link        https://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

use \Jelix\Core\App;
use Jelix\Installer\WarmUp\WarmUpLauncherInterface;

class requestsTest extends \Jelix\UnitTests\UnitTestCase {

    protected $server;
    protected $fServer;

    public function setUp() : void {
        $this->server = $_SERVER;
        App::saveContext();
        App::initPaths(__DIR__.'/app1/');
        $tempPath = __DIR__.'/../../../temp/routingapp1';
        if (!file_exists($tempPath)) {
            mkdir($tempPath);
        }
        App::setTempBasePath(realpath($tempPath).'/');
        App::clearModulesPluginsPath();
        App::declareModulesDir(__DIR__.'/app1/modules/');

        //self::initClassicRequest(TESTAPP_URL.'index.php');
        parent::setUp();
    }
    function tearDown() : void {
        unset($this->fServer);
        $_SERVER = $this->server;
        App::restoreContext();
    }

    protected function initRequest($url, $server, $scriptPath = '/foo/index.php', $scriptNameServerVariable = '')
    {
        $this->fServer = $server;
        $this->fServer->setHttpRequest($url);

        $config = \Jelix\Core\Config\AppConfig::loadWithoutCache('index/config.ini.php', $scriptPath);
        if ($scriptNameServerVariable) {
            $config->urlengine['scriptNameServerVariable'] = $scriptNameServerVariable;
        }

        $coord = new \Jelix\UnitTests\CoordinatorForTest($config, false);
        App::setRouter($coord);

        $warmUp = new Jelix\Installer\WarmUp\WarmUp(App::app());
        $warmUp->launch(App::getEnabledModulesPaths(), WarmUpLauncherInterface::STEP_ALL);

        $request = new jClassicRequest();
        $coord->testSetRequest($request);
        return $request;
    }


    function testSimpleUrl_MODPHP5_SCRIPT_NAME() {
        $serverconf = new \Jelix\FakeServerConf\ApacheMod(App::wwwPath(), '/foo/index.php');
        $req = $this->initRequest('http://testapp.local/foo/index.php/aaa',
                                  $serverconf, '/foo/index.php', 'SCRIPT_NAME');
        $this->assertEquals('/foo/', $req->urlScriptPath);
        $this->assertEquals('index.php', $req->urlScriptName);
        $this->assertEquals('/aaa', $req->urlPathInfo);
        $this->assertEquals('/foo/index.php', $req->urlScript);
    }

    //  /foo/index.php, CGI cgi.fix_pathinfo=1
    function testSimpleUrl_CGI_1_SCRIPT_NAME() {
        $serverconf = new \Jelix\FakeServerConf\ApacheCGI(App::wwwPath(),
                                                         '/foo/index.php',
                                                         '/usr/lib/cgi-bin/php5',
                                                         '/cgi-bin/php5');
        $req = $this->initRequest('http://testapp.local/foo/index.php/aaa',
                                  $serverconf, '/foo/index.php', 'SCRIPT_NAME');

        $this->assertEquals('/foo/', $req->urlScriptPath);
        $this->assertEquals('index.php', $req->urlScriptName);
        $this->assertEquals('/aaa', $req->urlPathInfo);
        $this->assertEquals('/foo/index.php', $req->urlScript);
    }

    function testGetServerURI_HTTP_80() {
        $serverconf = new \Jelix\FakeServerConf\ApacheMod(App::wwwPath(), '/index.php');
        $request = $this->initRequest('http://foo.local/index.php',
                                  $serverconf,
                                  '/index.php');
        $config = App::config();
        $config->domainName = 'foo.local';
        unset($_SERVER['HTTPS']);
        $_SERVER['SERVER_PORT'] = '80';

        // reset domain cache
        $this->assertEquals(array('foo.local', '80'), jServer::getDomainPortFromServer(false));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '';

        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '';
        $this->assertEquals('http://foo.local:8080', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('http://foo.local:8080', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('http://foo.local:8080', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '443';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = true;
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = true;
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = true;
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = true;
        $this->assertEquals('http://foo.local:8080', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));
    }

    function testGetServerURI_HTTP_8082() {

        $serverconf = new \Jelix\FakeServerConf\ApacheMod(App::wwwPath(), '/index.php');
        $request = $this->initRequest('http://foo.local/index.php',
                                  $serverconf,
                                  '/index.php');
        $config = App::config();
        $config->domainName = 'foo.local';
        unset($_SERVER['HTTPS']);
        $_SERVER['SERVER_PORT'] = '8082';

        // reset domain cache
        $this->assertEquals(array('foo.local', '8082'), jServer::getDomainPortFromServer(false));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '';

        $this->assertEquals('http://foo.local:8082', $request->getServerURI());
        $this->assertEquals('http://foo.local:8082', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '';
        $this->assertEquals('http://foo.local:8080', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('http://foo.local:8080', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('http://foo.local:8082', $request->getServerURI());
        $this->assertEquals('http://foo.local:8082', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('http://foo.local:8080', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('http://foo.local:8082', $request->getServerURI());
        $this->assertEquals('http://foo.local:8082', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '443';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = true;
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = true;
        $this->assertEquals('http://foo.local:8082', $request->getServerURI());
        $this->assertEquals('http://foo.local:8082', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = true;
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = true;
        $this->assertEquals('http://foo.local:8080', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));
    }
    
    function testGetServerURI_HTTPS_443() {

        $serverconf = new \Jelix\FakeServerConf\ApacheMod(App::wwwPath(), '/index.php');
        $request = $this->initRequest('http://foo.local/index.php',
                                  $serverconf,
                                  '/index.php');
        $config = App::config();
        $config->domainName = 'foo.local';

        // ----
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '443';
        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '';
        // reset domain cache
        $this->assertEquals(array('foo.local', '443'), jServer::getDomainPortFromServer(false));


        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('https://foo.local:4433', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('https://foo.local:4433', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('https://foo.local:4433', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '443';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('https://foo.local:4433', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = true;
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = true;
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = true;
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = true;
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));
    }//bad
    
    function testGetServerURI_HTTPS_4435() {

        $serverconf = new \Jelix\FakeServerConf\ApacheMod(App::wwwPath(), '/index.php');
        $request = $this->initRequest('http://foo.local/index.php',
                                  $serverconf,
                                  '/index.php');
        $request = App::router()->request;
        $config = App::config();
        $config->domainName = 'foo.local';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '4435';

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '';

        // reset domain cache
        $this->assertEquals(array('foo.local', '4435'), jServer::getDomainPortFromServer(false));

        $this->assertEquals('https://foo.local:4435', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4435', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '';
        $this->assertEquals('https://foo.local:4435', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4435', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '';
        $this->assertEquals('https://foo.local:4435', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4435', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('https://foo.local:4433', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('https://foo.local:4433', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('https://foo.local:4433', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '';
        $this->assertEquals('https://foo.local:4435', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4435', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '443';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('https://foo.local:4433', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = true;
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = true;
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = true;
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = true;
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));
    }
    
    function testGetServerURI_HTTPS_80() {

        $serverconf = new \Jelix\FakeServerConf\ApacheMod(App::wwwPath(), '/index.php');
        $request = $this->initRequest('http://foo.local/index.php',
                                  $serverconf,
                                  '/index.php');
        $request = App::router()->request;
        $config = App::config();
        $config->domainName = 'foo.local';

        // special ugly case
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '80';

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '';

        // reset domain cache
        $this->assertEquals(array('foo.local', '80'), jServer::getDomainPortFromServer(false));

        $this->assertEquals('https://foo.local:80', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:80', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '';
        $this->assertEquals('https://foo.local:80', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:80', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '';
        $this->assertEquals('https://foo.local:80', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:80', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('https://foo.local:4433', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('https://foo.local:4433', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('https://foo.local:4433', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '';
        $this->assertEquals('https://foo.local:80', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:80', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '443';
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('https://foo.local:4433', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = true;
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = true;
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = true;
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = true;
        $this->assertEquals('https://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));
    }
    
    function testGetServerURI_HTTP_443() {

        $serverconf = new \Jelix\FakeServerConf\ApacheMod(App::wwwPath(), '/index.php');
        $request = $this->initRequest('http://foo.local/index.php',
                                  $serverconf,
                                  '/index.php');
        $request = App::router()->request;
        $config = App::config();
        $config->domainName = 'foo.local';
        // special ugly case
        unset($_SERVER['HTTPS']);
        $_SERVER['SERVER_PORT'] = '443';

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '';

        // reset domain cache
        $this->assertEquals(array('foo.local', '443'), jServer::getDomainPortFromServer(false));

        $this->assertEquals('http://foo.local:443', $request->getServerURI());
        $this->assertEquals('http://foo.local:443', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '';
        $this->assertEquals('http://foo.local:8080', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('http://foo.local:8080', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('http://foo.local:443', $request->getServerURI());
        $this->assertEquals('http://foo.local:443', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '443';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('http://foo.local:8080', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('http://foo.local:443', $request->getServerURI());
        $this->assertEquals('http://foo.local:443', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '443';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = '4433';
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local:4433', $request->getServerURI(true));

        $config->forceHTTPPort = true;
        $config->forceHTTPSPort = true;
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '';
        $config->forceHTTPSPort = true;
        $this->assertEquals('http://foo.local:443', $request->getServerURI());
        $this->assertEquals('http://foo.local:443', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '80';
        $config->forceHTTPSPort = true;
        $this->assertEquals('http://foo.local', $request->getServerURI());
        $this->assertEquals('http://foo.local', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));

        $config->forceHTTPPort = '8080';
        $config->forceHTTPSPort = true;
        $this->assertEquals('http://foo.local:8080', $request->getServerURI());
        $this->assertEquals('http://foo.local:8080', $request->getServerURI(false));
        $this->assertEquals('https://foo.local', $request->getServerURI(true));
    }


}