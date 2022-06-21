<?php
/**
 * @package     testapp
 * @subpackage  jelix_tests module
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2022 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class XmlMapParserForTest extends \Jelix\Routing\UrlMapping\XmlMapParser
{
    function __construct()
    {
        //parent::__construct();

        $this->modulesPath = array(
            'testapp' => '/jelixapp/testapp/modules/testapp/'
        );
    }

    function callParseXml(\SimpleXMLElement $xml) {
        $this->parseXml($xml);
    }

    function getEntryPointsInfos()
    {
        return $this->entrypoints;
    }
}


class xmlMapParserTest extends \Jelix\UnitTests\UnitTestCase
{


    public function setUp(): void
    {
        parent::setUp();
    }

    function tearDown(): void
    {
    }

    function testParseModuleUrl()
    {
        $xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?>
<urls xmlns="http://jelix.org/ns/urls/1.0">
    <entrypoint name="index" default="true">
        <url module="testapp"/>
    </entrypoint>
</urls>');

        $parser = new XmlMapParserForTest();
        $parser->callParseXml($xml);
        $this->assertEquals(array('index'=>array(
            new \Jelix\Routing\UrlMapping\UrlMapData('classic', 'index', false),
            array(
                array(
                    'isDefault' => true,
                    'startModule' => '',
                    'startAction' => '',
                    'requestType' => 'classic',
                    'dedicatedModules' => array (
                        'testapp' => false
                    )
                )
            ),
            array(
                'testapp~*@classic' => array( 3,  'index', false, true, '')
            ),
            false
        )), $parser->getEntryPointsInfos());
    }

    function testParseModuleUrlWithPathInfo()
    {
        $xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?>
<urls xmlns="http://jelix.org/ns/urls/1.0">
    <entrypoint name="index" default="true">
        <url module="testapp" pathinfo="/test/"/>
    </entrypoint>
</urls>');

        $parser = new XmlMapParserForTest();
        $parser->callParseXml($xml);
        $this->assertEquals(array('index'=>array(
            new \Jelix\Routing\UrlMapping\UrlMapData('classic', 'index', false),
            array(
                array(
                    'isDefault' => true,
                    'startModule' => '',
                    'startAction' => '',
                    'requestType' => 'classic',
                    'dedicatedModules' => array ()
                ),
                array(
                    'testapp',
                    '',
                    '!^/test(/.*)?$!',
                    Array (), Array (), Array (), false, false
                )
            ),
            array(
                'testapp~*@classic' => array( 3,  'index', false, true, '/test')
            ),
            false
        )), $parser->getEntryPointsInfos());
    }

    function testParseInclude()
    {
        $xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?>
<urls xmlns="http://jelix.org/ns/urls/1.0">
    <entrypoint name="index" default="true">
        <url module="testapp" pathinfo="/test/" include="urls.xml"/>
    </entrypoint>
</urls>');

        $parser = new XmlMapParserForTest();
        $parser->callParseXml($xml);
        $this->assertEquals(array('index'=>array(
            new \Jelix\Routing\UrlMapping\UrlMapData('classic', 'index', false),
            array(
                array(
                    'isDefault' => true,
                    'startModule' => '',
                    'startAction' => '',
                    'requestType' => 'classic',
                    'dedicatedModules' => array ()
                ),
                array(
                    'testapp',
                    'login:in',
                    '!^/test/dologin$!',
                    Array (), Array (), Array (), false, false
                ),
                array(
                    'testapp',
                    'login:out',
                    '!^/test/dologout$!',
                    Array (), Array (), Array (), false, false
                ),
                array(
                    'testapp',
                    'login:form',
                    '!^/test/login\/?$!',
                    Array (), Array (), Array (), false, false
                ),
                array(
                    'testapp',
                    'user:index',
                    '!^/test/user/([^\/]+)$!',
                    Array ('user'), Array (0), Array (), false, false
                )
            ),
            array(
            ),
            false
        )), $parser->getEntryPointsInfos());
    }

    function testParseIncludeEmptyFile()
    {
        $xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?>
<urls xmlns="http://jelix.org/ns/urls/1.0">
    <entrypoint name="index" default="true">
        <url module="testapp" pathinfo="/test/" include="urls_empty_for_tests.xml"/>
    </entrypoint>
</urls>');

        $parser = new XmlMapParserForTest();
        $parser->callParseXml($xml);
        $this->assertEquals(array('index'=>array(
            new \Jelix\Routing\UrlMapping\UrlMapData('classic', 'index', false),
            array(
                array(
                    'isDefault' => true,
                    'startModule' => '',
                    'startAction' => '',
                    'requestType' => 'classic',
                    'dedicatedModules' => array ()
                ),
                array(
                    'testapp',
                    '',
                    '!^/test(/.*)?$!',
                    Array (), Array (), Array (), false, false
                )
            ),
            array(
                'testapp~*@classic' => array( 3,  'index', false, true, '/test')
            ),
            false
        )), $parser->getEntryPointsInfos());
    }
}