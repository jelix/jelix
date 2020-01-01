<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2010-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class restcontrollerTest extends \Jelix\UnitTests\UnitTestCase {

    function setUp() : void  {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        parent::setUp();
    }

    function tearDown() : void  {
        jApp::popCurrentModule();
    }

    function testRestGET() {
        $client = new \GuzzleHttp\Client();
        $res = $client->get($this->getServerUri().jApp::urlBasePath().'rest.php/test/rest', array());
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('this is a GET response. resturl='.jApp::urlBasePath().'rest.php/test/rest', (string)$res->getBody());
    }

    function testRestPUT() {
        $client = new \GuzzleHttp\Client();
        $res = $client->put($this->getServerUri().jApp::urlBasePath().'rest.php/test/rest',
            array('headers'=>array('Content-type'=>'application/x-www-form-urlencoded'),
                 'query'=>array('machin'=>'bidule','foo'=>'bar')));
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('this is a PUT response. machin=bidule foo=bar module=jelix_tests action=myrest:', (string)$res->getBody());
    }

    function testRestPUTJSON() {
        $client = new \GuzzleHttp\Client();
        $res = $client->put($this->getServerUri().jApp::urlBasePath().'rest.php/test/rest?machin=bidule',
            array('headers'=>array('Content-type'=>"application/json"),
                'body'=>'["foo", "bar"]'));
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('this is a PUT response. machin=bidule module=jelix_tests action=myrest: 0=foo 1=bar', (string)$res->getBody());
    }


    function testRestPOST() {
        $client = new \GuzzleHttp\Client();
        $res = $client->post($this->getServerUri().jApp::urlBasePath().'rest.php/test/rest', array('headers'=>array('Content-type'=>'application/x-www-form-urlencoded'),
                                                                                                  'query'=>array('foo'=>'bar')));
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('this is a POST response. foo=bar module=jelix_tests action=myrest:', (string)$res->getBody());
    }

    function testRestDELETE() {
        $client = new \GuzzleHttp\Client();
        $res = $client->delete($this->getServerUri().jApp::urlBasePath().'rest.php/test/rest');
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('this is a DELETE response', (string)$res->getBody());
    }

    protected function getServerUri() {
        $serverUri = jUrl::getRootUrlRessourceValue('localapp');
        if ($serverUri === null) {
            $serverUri = 'http://'.$_SERVER['HTTP_HOST'];
        }
        return $serverUri;
    }
}

