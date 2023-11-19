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

class xmlmapTest extends \Jelix\UnitTests\UnitTestCase {


    public function setUp() : void {
        //self::initClassicRequest(TESTAPP_URL.'index.php');
        copy(__DIR__.'/urls/urls.xml', jApp::tempPath('urls.xml'));
        copy(__DIR__.'/urls/res_urls_addurl.xml', jApp::tempPath('urls2.xml'));
        parent::setUp();
    }
    function tearDown() : void {
    }

    static function getEpTestsList() {
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

    function testEntryPointUrlModifier()
    {

        $modifier = new \Jelix\Routing\UrlMapping\XmlMapModifier(jApp::tempPath('urls.xml'));
        $epUrlMod = new \Jelix\Routing\UrlMapping\EntryPointUrlModifier($modifier, 'foo');

        $epUrlMod->havingName('index',
            array(
                new \Jelix\Routing\UrlMapping\MapEntry\MapInclude('urls-my-include.xml', '/my-include'),
                new \Jelix\Routing\UrlMapping\MapEntry\ModuleUrl('/my-foo-module'),
            )
        );
        $epUrlMod->havingName('news',
            array(
                // its pathinfo should be renamed by /mynews2 during tests
                new \Jelix\Routing\UrlMapping\MapEntry\MapInclude('urls-my-news.xml', '/mynews/'),
            )
        );
        $epUrlMod->havingType('soap',
            array(
                new \Jelix\Routing\UrlMapping\MapEntry\MapInclude('urls-soap.xml', '/my-soap-include'),
                new \Jelix\Routing\UrlMapping\MapEntry\ModuleUrl('/my-soap-module'),
            )
        );
        $modifier->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/res_urls_entrypointurlmodifier.xml'),
                            file_get_contents(jApp::tempPath('urls.xml')));
    }

    function testRemoveAllUrlOfAModule()
    {
        copy(__DIR__.'/urls/res_urls_entrypointurlmodifier.xml', jApp::tempPath('urls3.xml'));
        $modifier = new \Jelix\Routing\UrlMapping\XmlMapModifier(jApp::tempPath('urls3.xml'));
        $modifier->removeAllUrlOfModule('foo');
        $modifier->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/urls_without_foo.xml'),
            file_get_contents(jApp::tempPath('urls3.xml')));
    }

    function testRemoveHandler()
    {
        copy(__DIR__.'/urls/urls_many.xml', jApp::tempPath('urls3.xml'));
        $modifier = new \Jelix\Routing\UrlMapping\XmlMapModifier(jApp::tempPath('urls3.xml'));
        $index = $modifier->getEntryPoint('index');

        $index->removeUrlHandler('cms', 'superhandler');
        $modifier->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/urls_remove_handler.xml'),
            file_get_contents(jApp::tempPath('urls3.xml')));


    }

    function testRemoveController()
    {
        copy(__DIR__.'/urls/urls_many.xml', jApp::tempPath('urls3.xml'));
        $modifier = new \Jelix\Routing\UrlMapping\XmlMapModifier(jApp::tempPath('urls3.xml'));
        $index = $modifier->getEntryPoint('index');

        $index->removeUrlController('firstmodule', 'myctrl');
        $modifier->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/urls_remove_controller.xml'),
            file_get_contents(jApp::tempPath('urls3.xml')));

    }

    function testRemoveAction()
    {
        copy(__DIR__.'/urls/urls_many.xml', jApp::tempPath('urls3.xml'));
        $modifier = new \Jelix\Routing\UrlMapping\XmlMapModifier(jApp::tempPath('urls3.xml'));
        $index = $modifier->getEntryPoint('index');

        $index->removeUrlAction('firstmodule','foo3:bar');
        $modifier->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/urls_remove_action.xml'),
            file_get_contents(jApp::tempPath('urls3.xml')));
    }

    function testRemoveInclude()
    {
        copy(__DIR__.'/urls/urls_many.xml', jApp::tempPath('urls3.xml'));
        $modifier = new \Jelix\Routing\UrlMapping\XmlMapModifier(jApp::tempPath('urls3.xml'));
        $index = $modifier->getEntryPoint('index');

        $index->removeUrlInclude('foo', 'urls-my-include.xml');
        $modifier->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/urls_remove_include.xml'),
            file_get_contents(jApp::tempPath('urls3.xml')));

    }

    function testRemoveUrlModule()
    {
        copy(__DIR__.'/urls/urls_many.xml', jApp::tempPath('urls3.xml'));
        $modifier = new \Jelix\Routing\UrlMapping\XmlMapModifier(jApp::tempPath('urls3.xml'));
        $ep = $modifier->getEntryPoint('news');

        $ep->removeUrlModule('articles');
        $modifier->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/urls_remove_module_url.xml'),
            file_get_contents(jApp::tempPath('urls3.xml')));

    }
}