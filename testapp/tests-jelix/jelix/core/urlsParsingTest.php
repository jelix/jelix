<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor Thibault Piront (nuKs)
* @copyright   2005-2016 Laurent Jouanneau
* @copyright   2007 Thibault Piront
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class UTParseUrlsIncluder extends \Jelix\Core\Includer\Includer {

    static function resetUrlCache() {
        self::$_includedFiles = array();
        \Jelix\FileUtilities\Directory::remove(\jApp::tempPath('compiled/urlsig/urlsfiles/'),false);
    }
}


class UTParseUrls extends \Jelix\UnitTests\UnitTestCase {

    function setUp()  : void {
        jApp::saveContext();
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        parent::setUp();
    }

    function tearDown()  : void {
        jApp::popCurrentModule();
        jApp::restoreContext();
    }

    function testSignificantEngine() {

       $req = jApp::coord()->request;
       $req->urlScriptPath = '/';
       $req->params = array();
       $config = jApp::config();
       $config->urlengine = array(
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'notFoundAct'=>'jelix~error:notfound',
         'significantFile'=>'urlsfiles/url_maintests.xml',
         'localSignificantFile'=> '',
         'checkHttpsOnParsing'=>false,
         'urlScriptIdenc'=>'index'
       );
        $config->compilation['force'] = true;
        UTParseUrlsIncluder::resetUrlCache();
        jUrl::getEngine(true);


      $resultList=array();
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url1', 'mois'=>'10',  'annee'=>'2005', 'id'=>'35');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url8', 'mois'=>'10',  'annee'=>'2005', 'id'=>'35');
      $resultList[]= array('module'=>'testapp',       'action'=>'main:indexghost', "annee"=>"2016", "mois"=>"12", "id"=>"3");
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url2', 'mois'=>'05',  'annee'=>'2004', "mystatic"=>"valeur statique");
      $resultList[]= array('module'=>'jelix',       'action'=>'error:notfound');
      $resultList[]= array('module'=>'jelix',       'action'=>'error:notfound');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url3', 'rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c est la fete au village');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url4', 'first'=>'premier',  'second'=>'deuxieme');
      $resultList[]= array('module'=>'testapp',     'action'=>'main:indexghost', 'foo'=>'oof',  'bar'=>'rab');
      $resultList[]= array('module'=>'jelix',       'action'=>'error:notfound');
      //10
      $resultList[]= array();
      $resultList[]= array('module'=>'news',        'action'=>'main:bar',     'aaa'=>'bbb');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url11', 'rubrique'=>'vetements',  'id_article'=>'65');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url12', 'rubrique'=>'bricolage',  'id_article'=>'34');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url13', 'rubrique'=>'alimentation');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url13', 'rubrique'=>'chaussures');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url20', 'mois'=>'08',  'annee'=>'2007','lang'=>'en_US');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url20', 'mois'=>'08',  'annee'=>'2007','lang'=>'fr_FR');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url30');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'default:hello2');
      //20
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'default:hello3');
      $resultList[]= array('module'=>'testurls', 'action'=>'urlsig:urla');
      $resultList[]= array('module'=>'testurls', 'action'=>'urlsig:urla', 'first'=>'premier');
      $resultList[]= array('module'=>'testurls', 'action'=>'urlsig:urlb');
      $resultList[]= array('module'=>'testurls', 'action'=>'urlsig:urlc');
      $resultList[]= array('module'=>'news',        'action'=>'default:index', 'aaa'=>'bbb');
      $resultList[]= array('module'=>'news',        'action'=>'default:index', 'e'=>'g');
      $resultList[]= array('module'=>'news',        'action'=>'default:index', 't'=>'r');
      $resultList[]= array('module'=>'articles',    'action'=>'default:index',   't'=>'r');
      $resultList[]= array('module'=>'articles',    'action'=>'default:index',   't'=>'r');
      //30
      $resultList[]= array('module'=>'articles',    'action'=>'default:zap',   't'=>'r');
      $resultList[]= array('module'=>'jelix_tests',    'action'=>'default:wikishow',   'page'=>'/');
      $resultList[]= array('module'=>'jelix_tests',    'action'=>'default:wikishow',   'page'=>'/foo/bar');
      $resultList[]= array('module'=>'jelix_tests',    'action'=>'default:wikiedit',   'page'=>'/foo/bar');
      $resultList[]= array('module'=>'testapp',   'action'=>'login:in',   'login'=>'laurent');
      $resultList[]= array('module'=>'testapp',   'action'=>'login:out');
      $resultList[]= array('module'=>'testapp',   'action'=>'login:form');
      $resultList[]= array('module'=>'testapp',   'action'=>'user:index', 'user'=>'laurent');
      $resultList[]= array('module'=>'testapp',   'action'=>'main:suburlsfoo');
      $resultList[]= array('module'=>'testapp',   'action'=>'main:suburls');
      //40
      $resultList[]= array('module'=>'testapp',   'action'=>'main:indexghost');
      $resultList[]= array('module'=>'jelix', 'action'=>'error:notfound');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:wiki', 'path'=>'');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:wiki', 'path'=>'foo');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:wiki', 'path'=>'foo/bar/');
      $resultList[]= array('module'=>'jelix', 'action'=>'error:notfound');
      $resultList[]= array('module'=>'jfeeds', 'action'=>'myctrl:index');
      $resultList[]= array('module'=>'jfeeds', 'action'=>'myctrl:index');
      $resultList[]= array('module'=>'jfeeds', 'action'=>'myctrl:foo');

      $request=array(
          array("index.php","/test/news/2005/10/35",array()),
          array("index.php","/test/news/2005/10/35",array("action"=>"urlsig:url8")),
          array("index.php","",array("action"=>"urlsig:url1", "module"=>"jelix_tests", "annee"=>"2016", "mois"=>"12", "id"=>"3")),
          array("testnews.php","/2004/05",array()),
          array("testnews","/fooo/05",array()),
          array("testnews","/jelix_tests/urlsig/url2",array("annee"=>"2015", "mois"=>"12")),
          array("index.php","/test/cms/actualite/65-c-est-la-fete-au-village",array()),
          array("foo/bar.php","/withhandler/premier/deuxieme",array()),
          array("index.php",'',array('module'=>'jelix_tests', 'action'=>'urlsig:url5', 'foo'=>'oof',  'bar'=>'rab')),
          // this url has no definition in urls.xml, it is intentional
          array("index.php",'/jelix_tests/urlsig/url5',array( 'foo'=>'oof',  'bar'=>'rab')),
          //10
          array("xmlrpc.php","",array()),
          array("news.php","/news/main/bar",array('aaa'=>'bbb')),
          array("index.php","/shop/vetements/65",array()),
          array("index.php","/shop/bricolage/34/",array()),
          array("index.php","/supershop/alimentation",array()),
          array("index.php","/supershop/chaussures",array()),
          array("index.php","/articles/en/2007/08",array()),
          array("index.php","/articles/fr/2007/08",array()),
          array("index.php","/hello",array()),
          array("index.php","/hello2",array()),
          //20
          array("index.php","/hello3",array()),
          array("handlermodule.php","/myhand/urlsig/urla",array()),
          array("handlermodule.php","/myhand/urlsig/urla",array('first'=>'premier')),
          array("handlermodule.php","/myhand/urlsig/urlb",array()),
          array("handlermodule.php","/myhand/urlsig/urlc",array()),
          array("news.php","",array('aaa'=>'bbb','action'=>'main:bar')),
          array("news.php","",array('module'=>'news', 'e'=>'g','action'=>'main:chou')),
          array("news.php","",array('module'=>'articles', 't'=>'r','action'=>'default:zap')),
          array("news.php","/articles",array('t'=>'r','action'=>'default:zap')),
          array("news.php","/articles/default",array('t'=>'r')),
          //30
          array("news.php","/articles/default/zap",array('t'=>'r')),
          array("index.php","/super/wiki/",array()),
          array("index.php","/super/wiki/foo/bar",array()),
          array("index.php","/super/wiki/foo/bar",array('action'=>"default:wikiedit")),
          array("index.php","/auth/dologin", array('login'=>'laurent')),
          array("index.php","/auth/dologout", array()),
          array("index.php","/auth/login/", array()),
          array("index.php","/auth/user/laurent", array()),
          array("index.php","/suburl/foo", array()),
          array("index.php","/suburl/", array()),
          //40
          array("index.php","/", array()),
          array('index.php', "/wiki", array()),
          array('index.php', "/wiki/", array()),
          array('index.php', "/wiki/foo", array()),
          array('index.php', "/wiki/foo/bar/", array()),
          array('testnews.php', "/", array()),
          array('index.php', "/dynamic/method", array()),
          array('index.php', "/dynamic/method/", array()),
          array('index.php', "/dynamic/method/foo", array()),
       );

      foreach($request as $k=>$urldata){
         $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
         $p = $url->params;
         ksort($p);
         ksort($resultList[$k]);

         $this->assertEquals($resultList[$k], $p, 'test '.$k);
      }

      $config->urlengine['checkHttpsOnParsing'] = true;
      UTParseUrlsIncluder::resetUrlCache();
      jUrl::getEngine(true);

      $expected = array ( 'action' => 'error:notfound', 'module' => 'jelix');
      $url = jUrl::parse ("index.php","/shop/vetements/65",array());
      $p = $url->params;
      ksort($p);

      $this->assertEquals($expected, $p);

      $config->urlengine['checkHttpsOnParsing'] = false;
      UTParseUrlsIncluder::resetUrlCache();
      jUrl::getEngine(true);

      // the dot should be escaped in the regular expression
      $url = jUrl::parse ("index.php", "/hello.html",array());
      $this->assertEquals($url->params['module'], 'jelix_tests');
      $this->assertEquals($url->params['action'], 'urlsig:url31');
      $url = jUrl::parse ("index.php", "/helloUhtml",array());
      $this->assertEquals($url->params['module'], 'jelix');
      $this->assertEquals($url->params['action'], 'error:notfound');

      $config->urlengine['multiview']=true;
      $request=array(
          array("index","/test/news/2005/10/35",array()),
          array("index","/test/news/2005/10/35",array("action"=>"urlsig:url8")),
          array("index","",array("action"=>"urlsig:url1", "module"=>"jelix_tests", "annee"=>"2016", "mois"=>"12", "id"=>"3")),
          array("testnews","/2004/05",array()),
          array("testnews","/fooo/05",array()),
          array("testnews","/jelix_tests/urlsig/url2",array("annee"=>"2015", "mois"=>"12")),
          array("index","/test/cms/actualite/65-c-est-la-fete-au-village",array()),
          array("foo/bar","/withhandler/premier/deuxieme",array()),
          array("index",'',array('module'=>'jelix_tests', 'action'=>'urlsig:url5', 'foo'=>'oof',  'bar'=>'rab')),
          array("index",'/jelix_tests/urlsig/url5',array( 'foo'=>'oof',  'bar'=>'rab')),
          array("xmlrpc","",array()),
          array("news","/news/main/bar",array('aaa'=>'bbb',)),
          array("index","/shop/vetements/65",array()),
          array("index","/shop/bricolage/34/",array()),
          array("index","/supershop/alimentation",array()),
          array("index","/supershop/chaussures",array()),
          array("index","/articles/en/2007/08",array()),
          array("index","/articles/fr/2007/08",array()),
          array("index","/hello",array()),
          array("index","/hello2",array()),
          array("index","/hello3",array()),
          array("handlermodule","/myhand/urlsig/urla",array()),
          array("handlermodule","/myhand/urlsig/urla",array('first'=>'premier')),
          array("handlermodule","/myhand/urlsig/urlb",array()),
          array("handlermodule","/myhand/urlsig/urlc",array()),
          array("news","",array('aaa'=>'bbb','action'=>'main:bar')),
          array("news","",array('module'=>'news', 'e'=>'g','action'=>'main:chou')),
          array("news","",array('module'=>'articles', 't'=>'r','action'=>'default:zap')),
          array("news","/articles",array('t'=>'r','action'=>'default:zap')),
          array("news","/articles/default",array('t'=>'r')),
          array("news","/articles/default/zap",array('t'=>'r')),
          array("index.php","/super/wiki/",array()),
          array("index.php","/super/wiki/foo/bar",array()),
          array("index.php","/super/wiki/foo/bar",array('action'=>"default:wikiedit")),
          array("index","/auth/dologin", array('login'=>'laurent')),
          array("index","/auth/dologout", array()),
          array("index","/auth/login/", array()),
          array("index","/auth/user/laurent", array()),
          array("index","/suburl/foo", array()),
          array("index","/suburl/", array()),
          array("index","/", array()),
          array('index', "/wiki", array()),
          array('index', "/wiki/", array()),
          array('index', "/wiki/foo", array()),
          array('index', "/wiki/foo/bar/", array()),
          array('testnews', "", array()),
          array('index', "/dynamic/method", array()),
          array('index', "/dynamic/method/", array()),
          array('index', "/dynamic/method/foo", array()),
       );
      foreach($request as $k=>$urldata){
         $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
         $p = $url->params;
         ksort($p);
         ksort($resultList[$k]);

         $this->assertEquals( $resultList[$k], $p, 'test '.$k);
      }

    }

    function testSignificantEngineWithLang() {

        $req = jApp::coord()->request;
        $req->urlScriptPath = '/';
        $req->params = array();
        $config = jApp::config();
        $config->urlengine = array(
            'enableParser'=>true,
            'multiview'=>false,
            'basePath'=>'/',
            'notFoundAct'=>'jelix~error:notfound',
            'significantFile'=>'urlsfiles/url_maintests.xml',
            'localSignificantFile'=> '',
            'checkHttpsOnParsing'=>false,
            'urlScriptIdenc'=>'index'
        );
        $config->compilation['force'] = true;
        UTParseUrlsIncluder::resetUrlCache();
        jUrl::getEngine(true);

        $resultList=array();
        $resultList[]= array('fr_FR', array('module'=>'jelix_tests', 'action'=>'urlsig:lang1', 'p1'=>'foo', 'lang'=>'fr'));
        $resultList[]= array('en_US', array('module'=>'jelix_tests', 'action'=>'urlsig:lang1', 'p1'=>'foo', 'lang'=>'en', 'bar'=>'baz')); // FIXME should be en_US
        $resultList[]= array('fr_FR', array('module'=>'jelix_tests', 'action'=>'urlsig:lang1bis', 'p1'=>'foo', 'lang'=>'fr_FR'));
        $resultList[]= array('fr_FR', array('module'=>'jelix_tests', 'action'=>'urlsig:lang1bis', 'p1'=>'foo', 'lang'=>'fr_FR'));
        $resultList[]= array('en_US', array('module'=>'jelix_tests', 'action'=>'urlsig:lang1bis', 'p1'=>'foo', 'lang'=>'en_US')); // FIXME should be en_US
        $resultList[]= array('en_US', array('module'=>'jelix_tests', 'action'=>'urlsig:lang2', 'p1'=>'foo', 'lang'=>'en'));// FIXME should be en_US
        $resultList[]= array('fr_FR', array('module'=>'jelix_tests', 'action'=>'urlsig:lang2', 'p1'=>'foo', 'lang'=>'fr'));
        $resultList[]= array('en_US', array('module'=>'jelix_tests', 'action'=>'urlsig:lang3', 'p1'=>'foo', 'lang'=>'en_US'));
        $resultList[]= array('fr_FR', array('module'=>'jelix_tests', 'action'=>'urlsig:lang3', 'p1'=>'foo', 'lang'=>'fr_FR'));

        $request=array(
            array("index.php","/url-with-lang/test1/fr/foo", array()),
            array("index.php","/url-with-lang/test1/en/foo", array('bar'=>'baz')),
            array("index.php","/url-with-lang/test1bis/fr_FR/foo", array()),
            array("index.php","/url-with-lang/test1bis/fr/foo", array()),
            array("index.php","/url-with-lang/test1bis/en/foo", array()),
            array("index.php","/url-with-lang/test2/en/foo", array()),
            array("index.php","/url-with-lang/test2/fr/foo", array()),
            array("index.php","/url-with-lang/test3/en/foo", array()),
            array("index.php","/url-with-lang/test3/fr/foo", array()),
        );

        foreach($request as $k=>$urldata){
            jApp::config()->locale = 'xx_YY';
            $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
            $p = $url->params;
            ksort($p);
            ksort($resultList[$k][1]);
            $this->assertEquals($p, $resultList[$k][1], 'test '.$k);
            $this->assertEquals(jApp::config()->locale, $resultList[$k][0], 'test '.$k);
        }

        $config->urlengine['checkHttpsOnParsing'] = true;
        UTParseUrlsIncluder::resetUrlCache();
        jUrl::getEngine(true);
        $config->urlengine['multiview']=true;

        $request=array(
            array("index","/url-with-lang/test1/fr/foo", array()),
            array("index","/url-with-lang/test1/en/foo", array('bar'=>'baz')),
            array("index","/url-with-lang/test1bis/fr_FR/foo", array()),
            array("index","/url-with-lang/test1bis/fr/foo", array()),
            array("index","/url-with-lang/test1bis/en/foo", array()),
            array("index","/url-with-lang/test2/en/foo", array()),
            array("index","/url-with-lang/test2/fr/foo", array()),
            array("index","/url-with-lang/test3/en/foo", array()),
            array("index","/url-with-lang/test3/fr/foo", array()),
        );

        foreach($request as $k=>$urldata){
            jApp::config()->locale = 'xx_YY';
            $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
            $p = $url->params;
            ksort($p);
            ksort($resultList[$k][1]);
            $this->assertEquals($p, $resultList[$k][1], 'test '.$k);
            $this->assertEquals(jApp::config()->locale, $resultList[$k][0], 'test '.$k);
        }
    }


    function testDedicatedModule() {

        $req = jApp::coord()->request;
        $req->urlScriptPath = '/';
        $req->params = array();
        $config = jApp::config();
        $config->urlengine = array(
          'enableParser'=>true,
          'multiview'=>false,
          'basePath'=>'/',
          'notFoundAct'=>'jelix~error:notfound',
          'significantFile'=>'urlsfiles/url_dedicatedmodule.xml',
          'localSignificantFile'=> '',
          'checkHttpsOnParsing'=>false,
          'urlScriptIdenc'=>'index'
        );
        $config->compilation['force'] = true;
        UTParseUrlsIncluder::resetUrlCache();
        jUrl::getEngine(true);

        $resultList=array();
        $resultList[]= array('module'=>'testapp', 'action'=>'default:index');
        $resultList[]= array('module'=>'testapp', 'action'=>'default:index');
        $resultList[]= array('module'=>'testapp', 'action'=>'default:index');
        $resultList[]= array('module'=>'testapp', 'action'=>'default:index');
        $resultList[]= array('module'=>'testapp', 'action'=>'foo:index');
        $resultList[]= array('module'=>'testapp', 'action'=>'foo:index');
        $resultList[]= array('module'=>'testapp', 'action'=>'foo:bar');
        $resultList[]= array('module'=>'testapp', 'action'=>'foo:bar');

        $resultList[]= array('module'=>'news', 'action'=>'default:index');
        $resultList[]= array('module'=>'news', 'action'=>'default:index');
        $resultList[]= array('module'=>'news', 'action'=>'foo:index');
        $resultList[]= array('module'=>'news', 'action'=>'foo:bar');
        $resultList[]= array('module'=>'articles', 'action'=>'default:index');
        $resultList[]= array('module'=>'articles', 'action'=>'foo:index');
        $resultList[]= array('module'=>'articles', 'action'=>'foo:bar');

        $request=array(
            array("index.php","", array()),
            array("index.php","/", array()),
            array("index.php","/testapp", array()),
            array("index.php","/testapp/", array()),
            array("index.php","/testapp/foo", array()),
            array("index.php","/testapp/foo/", array()),
            array("index.php","/testapp/foo/bar", array()),
            array("index.php","/testapp/foo/bar/", array()),

            array("news.php","/", array()),
            array("news.php","/news/", array()),
            array("news.php","/news/foo", array()),
            array("news.php","/news/foo/bar", array()),
            array("news.php","/mynews/", array()),
            array("news.php","/mynews/foo", array()),
            array("news.php","/mynews/foo/bar", array()),
        );

        foreach($request as $k=>$urldata) {
            $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
            $p = $url->params;
            ksort($p);
            ksort($resultList[$k]);
            $this->assertEquals($resultList[$k], $p, 'test '.$k);
        }
    }

    function testDefaultAction() {

        $req = jApp::coord()->request;
        $req->urlScriptPath = '/';
        $req->params = array();
        $config = jApp::config();
        $config->urlengine = array(
          'enableParser'=>true,
          'multiview'=>false,
          'basePath'=>'/',
          'notFoundAct'=>'jelix~error:notfound',
          'significantFile'=>'urlsfiles/url_defaultaction.xml',
          'localSignificantFile'=> '',
          'checkHttpsOnParsing'=>false,
          'urlScriptIdenc'=>'index'
        );
        $config->compilation['force'] = true;
        UTParseUrlsIncluder::resetUrlCache();
        jUrl::getEngine(true);

        $resultList=array();
        $resultList[]= array('module'=>'testapp', 'action'=>'main:index');
        $resultList[]= array('module'=>'testapp', 'action'=>'main:index');
        $resultList[]= array('module'=>'jelix', 'action'=>'error:notfound');
        $resultList[]= array('module'=>'testapp', 'action'=>'default:test1', 'annee'=>'2010', 'mois'=>'12', 'bla'=>'cequejeveux');

        $resultList[]= array('module'=>'jelix_tests', 'action'=>'main:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:bug1488', 'var'=>'something');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'default:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'default:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'foo:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'foo:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'foo:bar');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'foo:bar');

        $resultList[]= array('module'=>'testapp', 'action'=>'main:index2');
        $resultList[]= array('module'=>'jelix', 'action'=>'error:notfound');

        $resultList[]= array('module'=>'news', 'action'=>'default:index');
        $resultList[]= array('module'=>'news', 'action'=>'default:index');
        $resultList[]= array('module'=>'news', 'action'=>'foo:index');
        $resultList[]= array('module'=>'news', 'action'=>'foo:bar');
        $resultList[]= array('module'=>'articles', 'action'=>'default:index');
        $resultList[]= array('module'=>'articles', 'action'=>'foo:index');
        $resultList[]= array('module'=>'articles', 'action'=>'foo:bar');

        $request=array(
            array("index.php","", array()),
            array("index.php","/", array()),
            array("index.php","/testapp", array()),
            array("index.php","/test/2010/12", array()),

            array("noep.php","/", array()),
            array("noep.php","/zip/something/", array()),
            array("noep.php","/jelix_tests", array()),
            array("noep.php","/jelix_tests/", array()),
            array("noep.php","/jelix_tests/foo", array()),
            array("noep.php","/jelix_tests/foo/", array()),
            array("noep.php","/jelix_tests/foo/bar", array()),
            array("noep.php","/jelix_tests/foo/bar/", array()),

            array("testnews.php","/", array()),
            array("testnews.php","/testapp", array()),

            array("news.php","/", array()),
            array("news.php","/news/", array()),
            array("news.php","/news/foo", array()),
            array("news.php","/news/foo/bar", array()),
            array("news.php","/articles/", array()),
            array("news.php","/articles/foo", array()),
            array("news.php","/articles/foo/bar", array()),
        );

        foreach($request as $k=>$urldata) {
            $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
            $p = $url->params;
            ksort($p);
            ksort($resultList[$k]);
            $this->assertEquals($resultList[$k], $p, 'test '.$k);
        }
    }

    function testEmptyRedefinedUrls() {

        $req = jApp::coord()->request;
        $req->urlScriptPath = '/';
        $req->params = array();
        $config = jApp::config();
        $config->urlengine = array(
            'enableParser'=>true,
            'multiview'=>false,
            'basePath'=>'/',
            'notFoundAct'=>'jelix~error:notfound',
            'significantFile'=>'urlsfiles/url_mainredefined.xml',
            'localSignificantFile'=> 'urlsfiles/url_empty.xml',
            'checkHttpsOnParsing'=>false,
            'urlScriptIdenc'=>'index'
        );
        $config->compilation['force'] = true;
        UTParseUrlsIncluder::resetUrlCache();
        jUrl::getEngine(true);

        $resultList=array();
        $resultList[]= array('module'=>'testapp', 'action'=>'main:index');
        $resultList[]= array('module'=>'testapp', 'action'=>'main:index');
        $resultList[]= array('module'=>'jelix', 'action'=>'error:notfound');
        $resultList[]= array('module'=>'testapp', 'action'=>'default:test1', 'annee'=>'2010', 'mois'=>'12', 'bla'=>'cequejeveux');

        $resultList[]= array('module'=>'jelix_tests', 'action'=>'main:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:bug1488', 'var'=>'something');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'default:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'default:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'foo:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'foo:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'foo:bar');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'foo:bar');

        $resultList[]= array('module'=>'testapp', 'action'=>'main:index2');
        $resultList[]= array('module'=>'jelix', 'action'=>'error:notfound');

        $resultList[]= array('module'=>'news', 'action'=>'default:index');
        $resultList[]= array('module'=>'news', 'action'=>'default:index');
        $resultList[]= array('module'=>'news', 'action'=>'foo:index');
        $resultList[]= array('module'=>'news', 'action'=>'foo:bar');
        $resultList[]= array('module'=>'articles', 'action'=>'default:index');
        $resultList[]= array('module'=>'articles', 'action'=>'foo:index');
        $resultList[]= array('module'=>'articles', 'action'=>'foo:bar');

        $request=array(
            array("index.php","", array()),
            array("index.php","/", array()),
            array("index.php","/testapp", array()),
            array("index.php","/test/2010/12", array()),

            array("noep.php","/", array()),
            array("noep.php","/zip/something/", array()),
            array("noep.php","/jelix_tests", array()),
            array("noep.php","/jelix_tests/", array()),
            array("noep.php","/jelix_tests/foo", array()),
            array("noep.php","/jelix_tests/foo/", array()),
            array("noep.php","/jelix_tests/foo/bar", array()),
            array("noep.php","/jelix_tests/foo/bar/", array()),

            array("testnews.php","/", array()),
            array("testnews.php","/testapp", array()),

            array("news.php","/", array()),
            array("news.php","/news/", array()),
            array("news.php","/news/foo", array()),
            array("news.php","/news/foo/bar", array()),
            array("news.php","/articles/", array()),
            array("news.php","/articles/foo", array()),
            array("news.php","/articles/foo/bar", array()),
        );

        foreach($request as $k=>$urldata) {
            $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
            $p = $url->params;
            ksort($p);
            ksort($resultList[$k]);
            $this->assertEquals($resultList[$k], $p, 'test '.$k);
        }
    }

    function testRedefinedUrls() {
        $req = jApp::coord()->request;
        $req->urlScriptPath = '/';
        $req->params = array();
        $config = jApp::config();
        $config->urlengine = array(
            'enableParser'=>true,
            'multiview'=>false,
            'basePath'=>'/',
            'notFoundAct'=>'jelix~error:notfound',
            'significantFile'=>'urlsfiles/url_mainredefined.xml',
            'localSignificantFile'=> 'urlsfiles/url_redefined.xml',
            'checkHttpsOnParsing'=>false,
            'urlScriptIdenc'=>'index'
        );
        $config->compilation['force'] = true;
        UTParseUrlsIncluder::resetUrlCache();
        jUrl::getEngine(true);

        $resultList=array();
        $resultList[]= array('module'=>'testapp', 'action'=>'main:index');
        $resultList[]= array('module'=>'testapp', 'action'=>'main:index');
        $resultList[]= array('module'=>'jelix', 'action'=>'error:notfound');
        $resultList[]= array('module'=>'testapp', 'action'=>'default:test1', 'annee'=>'2010', 'mois'=>'12', 'bla'=>'cequejeveux');

        $resultList[]= array('module'=>'jelix_tests', 'action'=>'main:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:bug1488', 'var'=>'something');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'default:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'default:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'foo:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'foo:index');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'foo:bar');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'foo:bar');

        $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url4', 'first'=>'premier',  'second'=>'deuxieme');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:display', 'var'=>'chimlou');
        $resultList[]= array();

        $resultList[]= array('module'=>'testapp', 'action'=>'main:index2');
        $resultList[]= array('module'=>'jelix', 'action'=>'error:notfound');

        $resultList[]= array('module'=>'news', 'action'=>'default:index');
        $resultList[]= array('module'=>'news', 'action'=>'default:index');
        $resultList[]= array('module'=>'news', 'action'=>'foo:index');
        $resultList[]= array('module'=>'news', 'action'=>'foo:bar');
        $resultList[]= array('module'=>'articles', 'action'=>'default:index');
        $resultList[]= array('module'=>'articles', 'action'=>'foo:index');
        $resultList[]= array('module'=>'articles', 'action'=>'foo:bar');

        $request=array(
            array("index.php","", array()),
            array("index.php","/", array()),
            array("index.php","/testapp", array()),
            array("index.php","/test/2010/12", array()),

            array("noep.php","/", array()),
            array("noep.php","/zip/something/", array()),
            array("noep.php","/jelix_tests", array()),
            array("noep.php","/jelix_tests/", array()),
            array("noep.php","/jelix_tests/foo", array()),
            array("noep.php","/jelix_tests/foo/", array()),
            array("noep.php","/jelix_tests/foo/bar", array()),
            array("noep.php","/jelix_tests/foo/bar/", array()),

            array("foo/bar.php","/withhandler/premier/deuxieme",array()),
            array("foo/bar.php","/sopar/chimlou",array()),
            array("xmlrpc.php","",array()),

            array("testnews.php","/", array()),
            array("testnews.php","/testapp", array()),

            array("news.php","/", array()),
            array("news.php","/news/", array()),
            array("news.php","/news/foo", array()),
            array("news.php","/news/foo/bar", array()),
            array("news.php","/articles/", array()),
            array("news.php","/articles/foo", array()),
            array("news.php","/articles/foo/bar", array()),
        );

        foreach($request as $k=>$urldata) {
            $url = jUrl::parse($urldata[0], $urldata[1], $urldata[2]);
            $p = $url->params;
            ksort($p);
            ksort($resultList[$k]);
            $this->assertEquals($resultList[$k], $p, 'test '.$k);
        }
    }



    public function getErrors() {
        return array(
            array('urlsfiles/url_twodefaultep.xml', 'urlsfiles/url_twodefaultep.xml: Only one default entry point for the type classic is allowed (<entrypoint name="testnews" default="true">)'),
            array('urlsfiles/url_nodefaultep.xml', 'There are several entrypoint of the same type classic, but no one as default'),
            //array('urlsfiles/url_empty.xml', 'Default url is missing for the entry point index')
        );
    }

    /**
     * @dataProvider getErrors
     */
    function testErrorSignificantEngine($file, $error) {

       $req = jApp::coord()->request;
       $req->urlScriptPath = '/';
       $req->params = array();
       $config = jApp::config();
       $config->urlengine = array(
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'notFoundAct'=>'jelix~error:notfound',
         'significantFile'=>$file,
         'localSignificantFile'=> '',
         'checkHttpsOnParsing'=>false,
         'urlScriptIdenc'=>'index'
       );
        $config->compilation['force'] = true;
        UTParseUrlsIncluder::resetUrlCache();
        try {
            jUrl::getEngine(true);
            $this->assertFalse(true, 'No expected error');
        }
        catch(\Jelix\Routing\UrlMapping\MapParserException $e) {
            $this->assertEquals($error, $e->getMessage());
        }
        catch(Exception $e) {
            $this->assertFalse(true, 'Not the expected error: '.$e->getMessage());
        }
    }
}
