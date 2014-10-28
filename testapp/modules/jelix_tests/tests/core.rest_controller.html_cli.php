<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class UTRestController extends jUnitTestCase {
    function setUp() {
    }

    function tearDown() {
    }

    protected function getServerUri() {
        $serverUri = jUrl::getRootUrlRessourceValue('localapp');
        if ($serverUri === null) {
            $serverUri = $_SERVER['HTTP_HOST'];
        }
        else {
            $serverUri = str_replace('http://', '', $serverUri);
        }
        return $serverUri;
    }

    function testRestGET() {
        $http = new jHttp($this->getServerUri());
        $http->get(jApp::config()->urlengine['basePath'].'rest.php/test/rest');
        $this->assertEqual($http->getStatus(), 200);

        $this->assertEqual($http->getContent(), 'this is a GET response. resturl='.jApp::config()->urlengine['basePath'].'rest.php/test/rest');
    }

    function testRestPUT() {
        $http = new jHttp($this->getServerUri());
        $http->put(jApp::config()->urlengine['basePath'].'rest.php/test/rest', array('foo'=>'bar'));
        $this->assertEqual($http->getStatus(), 200);

        $this->assertEqual($http->getContent(), 'this is a PUT response. module=jelix_tests action=myrest: foo=bar');
    }

    function testRestPOST() {
        $http = new jHttp($this->getServerUri());
        $http->post(jApp::config()->urlengine['basePath'].'rest.php/test/rest', array('foo'=>'bar'));
        $this->assertEqual($http->getStatus(), 200);

        $this->assertEqual($http->getContent(), 'this is a POST response. module=jelix_tests action=myrest: foo=bar');
    }

    function testRestDELETE() {
        $http = new jHttp($this->getServerUri());
        $http->delete(jApp::config()->urlengine['basePath'].'rest.php/test/rest');
        $this->assertEqual($http->getStatus(), 200);

        $this->assertEqual($http->getContent(), 'this is a DELETE response');
    }


}

