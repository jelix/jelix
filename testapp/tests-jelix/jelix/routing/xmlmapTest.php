<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class xmlmapTest extends jUnitTestCase {


    public function setUp() {
        //self::initClassicRequest(TESTAPP_URL.'index.php');
        copy(__DIR__.'/urls/urls.xml', jApp::tempPath('urls.xml'));
        copy(__DIR__.'/urls/res_urls_addurl.xml', jApp::tempPath('urls2.xml'));
        parent::setUp();
    }
    function tearDown() {
    }

    function getEpTestsList() {
        return array(
            array('res_urls_addep.xml', 'foo', 'classic', array("https"=>true)),
            array('res_urls_addexistep.xml', 'bar', 'classic', array("https"=>true)),
            array('res_urls_addexistepdefault.xml', 'bar', 'classic', array("default"=>true)),
        );
    }
    
    /**
     * @dataProvider getEpTestsList
     */
    function testAddEntryPoint($expected, $name, $type, $options) {
        $modifier = new \Jelix\Routing\UrlMapping\XmlMapModifier(jApp::tempPath('urls.xml'));
        $modifier->addEntryPoint($name, $type, $options);
        $modifier->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/'.$expected),
                            file_get_contents(jApp::tempPath('urls.xml')));
    }

    function testGetEntryPoint() {
        $modifier = new \Jelix\Routing\UrlMapping\XmlMapModifier(jApp::tempPath('urls.xml'));
        $ep = $modifier->getEntryPoint('mysoap', 'soap');

        $this->assertEquals("mysoap", $ep->getName());
        $this->assertEquals("soap", $ep->getType());
    }


    function testAddUrl() {
        $modifier = new \Jelix\Routing\UrlMapping\XmlMapModifier(jApp::tempPath('urls.xml'));
        $ep = $modifier->getEntryPoint('index');
        $ep->addUrlAction("/first", "firstmodule", "foo:bar");
        $ep->addUrlAction("/first/what", "firstmodule", "foo2:bar", null, null, array("noentrypoint"=>true));
        $ep->addUrlAction("/withparam/:hello/:world", "firstmodule", "foo3:bar",
                          array(
                            "hello"=>array(),
                            "world"=>array("type"=>"year"),
                            "bonjour"=>array("type"=>"string", "regexp"=>"/^foo/"),
                        ));
        $ep->addUrlAction("/withparamstatic/:hello/:world", "firstmodule", "foo4:bar",
                          array(
                            "hello"=>array(),
                            "world"=>array("type"=>"year"),
                            ),
                          array("static1"=>array('value'=>'statval'))
                        );
        $ep->addUrlModule('/news', 'news');
        $ep->addUrlHandler('/articles', 'cms', 'superhandler');
        $ep->addUrlController("/dynamic/method", "firstmodule", "myctrl");

        $modifier->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/res_urls_addurl.xml'),
                            file_get_contents(jApp::tempPath('urls.xml')));
    }

    function testAddExistingUrl() {
        $modifier = new \Jelix\Routing\UrlMapping\XmlMapModifier(jApp::tempPath('urls2.xml'));
        $ep = $modifier->getEntryPoint('index');
        $ep->addUrlAction("/first", "othermodule", "foo:bar");
        $ep->addUrlAction("/first/what", "firstmodule", "foo2:bar", null, null, array("noentrypoint"=>false));
        $ep->addUrlAction("/withparam/:hello/:world", "firstmodule", "foo3:bar",
            array(
                "hello"=>array(),
                "bonjour"=>array("type"=>"string", "regexp"=>"/^aaaa/"),
            ));
        $ep->addUrlModule('/news', 'news2');
        $ep->addUrlHandler('/articles', 'cms', 'superhandler2');
        $ep->addUrlController("/dynamic/method", "firstmodule", "myctrl2");
        $modifier->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/res_urls_addurl2.xml'),
            file_get_contents(jApp::tempPath('urls2.xml')));

    }

    
}