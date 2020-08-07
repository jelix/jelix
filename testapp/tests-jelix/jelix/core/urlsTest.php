<?php
/**
 * @package     testapp
 * @subpackage  jelix_tests module
 * @author      Laurent Jouanneau
 * @copyright   2020 Laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class UTUrls extends jUnitTestCase
{

    function setUp()
    {
        self::initClassicRequest(TESTAPP_URL . 'index.php');
        jApp::pushCurrentModule('jelix_tests');
        parent::setUp();
    }

    function tearDown()
    {
        jApp::popCurrentModule();
        jUrl::getEngine(true);
    }

    function getIsUrlFromAppData()
    {
        return array(
            array(TESTAPP_URL.'', true, '/', TESTAPP_URL_HOST, array()),
            array(TESTAPP_URL.'ab/cd', true, '/', TESTAPP_URL_HOST, array()),
            array('/', true, '/', TESTAPP_URL_HOST, array()),
            array(TESTAPP_URL, false, '/abc/', TESTAPP_URL_HOST, array()),
            array(TESTAPP_URL.'ab', false, '/abc/', TESTAPP_URL_HOST, array()),
            array(TESTAPP_URL.'abc', true, '/abc/', TESTAPP_URL_HOST, array()),
            array(TESTAPP_URL.'987/az', false, '/abc/', TESTAPP_URL_HOST, array()),
            array('http://example.com/', false, '/', TESTAPP_URL_HOST, array()),
            array('http://example.com/abc', false, '/', TESTAPP_URL_HOST, array()),
            array('http://example.com/abc', false, '/abc', TESTAPP_URL_HOST, array()),
            array('http://example.com/', true, '/', TESTAPP_URL_HOST, array('example.com')),
            array('http://example.com/abc', true, '/', TESTAPP_URL_HOST, array('example.com')),
            array('http://example.com/abc', true, '/abc', TESTAPP_URL_HOST, array('example.com')),

        );
    }

    /**
     * @dataProvider getIsUrlFromAppData
     */
    function testIsUrlFromApp($url, $expected, $configBasePath, $configDomain, $domains)
    {
        $config = jApp::config();

        $config->domainName = $configDomain?$configDomain:TESTAPP_URL_HOST;
        $config->urlengine['basePath'] = $configBasePath;
        $this->assertEquals($expected, jUrl::isUrlFromApp($url, $domains));
    }

}
