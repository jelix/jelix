<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor Thibault Piront (nuKs)
* @copyright   2005-2009 Laurent Jouanneau
* @copyright   2007 Thibault Piront
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'plugins/urls/significant/significant.urls.php');

class UTParseUrlsIncluder extends jIncluder {

    static function resetUrlCache() {
        $sel = new jSelectorUrlCfgSig(jApp::config()->urlengine['significantFile']);
        $file = $sel->getCompiledFilePath();

        unset(self::$_includedFiles[$file]);
    }
}


class UTParseUrls extends jUnitTestCase {

    function setUp() {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        parent::setUp();
    }
    
    function tearDown() {
        jApp::popCurrentModule();
        jUrl::getEngine(true);
    }

    function testSignificantEngine() {

       $req = jApp::coord()->request;
       $req->urlScriptPath = '/';
       $req->params = array();
       $config = jApp::config();
       $config->urlengine = array(
         'engine'=>'significant',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
         'notfoundAct'=>'jelix~notfound',
         'significantFile'=>'urls.xml',
         'checkHttpsOnParsing'=>false
       );
        $config->compilation['force'] = true;
        UTParseUrlsIncluder::resetUrlCache();
        jUrl::getEngine(true);


      $resultList=array();
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url1', 'mois'=>'10',  'annee'=>'2005', 'id'=>'35');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url8', 'mois'=>'10',  'annee'=>'2005', 'id'=>'35');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url2', 'mois'=>'05',  'annee'=>'2004', "mystatic"=>"valeur statique");
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url3', 'rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c est la fete au village');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url4', 'first'=>'premier',  'second'=>'deuxieme');
      // this result has no definition in urls.xml, it si normal
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url5', 'foo'=>'oof',  'bar'=>'rab');
      $resultList[]= array();
      $resultList[]= array('module'=>'news',        'action'=>'main:bar',     'aaa'=>'bbb');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url11', 'rubrique'=>'vetements',  'id_article'=>'65');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url12', 'rubrique'=>'bricolage',  'id_article'=>'34');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url13', 'rubrique'=>'alimentation');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url13', 'rubrique'=>'chaussures');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url20', 'mois'=>'08',  'annee'=>'2007','lang'=>'en_EN');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url20', 'mois'=>'08',  'annee'=>'2007','lang'=>'fr_FR');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url30');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'default:hello2');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'default:hello3');
      $resultList[]= array('module'=>'testurls', 'action'=>'urlsig:urla');
      $resultList[]= array('module'=>'testurls', 'action'=>'urlsig:urla', 'first'=>'premier');
      $resultList[]= array('module'=>'testurls', 'action'=>'urlsig:urlb');
      $resultList[]= array('module'=>'testurls', 'action'=>'urlsig:urlc');
      $resultList[]= array('module'=>'news',        'action'=>'main:chou',     'e'=>'g');
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
      $resultList[]= array('module'=>'testapp',   'action'=>'main:indexghost');
      $resultList[]= array('module'=>'jelix', 'action'=>'default:notfound');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:wiki', 'path'=>'');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:wiki', 'path'=>'foo');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:wiki', 'path'=>'foo/bar/');

      $request=array(
          array("index.php","/test/news/2005/10/35",array()),
          array("index.php","/test/news/2005/10/35",array("action"=>"urlsig:url8")),
          array("testnews.php","/2004/05",array()),
          array("index.php","/test/cms/actualite/65-c-est-la-fete-au-village",array()),
          array("foo/bar.php","/withhandler/premier/deuxieme",array()),
          array("index.php",'',array('module'=>'jelix_tests', 'action'=>'urlsig:url5', 'foo'=>'oof',  'bar'=>'rab')),
          array("xmlrpc.php","",array()),
          array("news.php","",array('aaa'=>'bbb','action'=>'main:bar')),
          array("index.php","/shop/vetements/65",array()),
          array("index.php","/shop/bricolage/34/",array()),
          array("index.php","/supershop/alimentation",array()),
          array("index.php","/supershop/chaussures",array()),
          array("index.php","/articles/en/2007/08",array()),
          array("index.php","/articles/fr/2007/08",array()),
          array("index.php","/hello",array()),
          array("index.php","/hello2",array()),
          array("index.php","/hello3",array()),
          array("handlermodule.php","/myhand/urlsig/urla",array()),
          array("handlermodule.php","/myhand/urlsig/urla",array('first'=>'premier')),
          array("handlermodule.php","/myhand/urlsig/urlb",array()),
          array("handlermodule.php","/myhand/urlsig/urlc",array()),
          array("news.php","",array('module'=>'news', 'e'=>'g','action'=>'main:chou')),
          array("news.php","",array('module'=>'articles', 't'=>'r','action'=>'default:zap')),
          array("index.php","/super/wiki/",array()),
          array("index.php","/super/wiki/foo/bar",array()),
          array("index.php","/super/wiki/foo/bar",array('action'=>"default:wikiedit")),
          array("index.php","/auth/dologin", array('login'=>'laurent')),
          array("index.php","/auth/dologout", array()),
          array("index.php","/auth/login/", array()),
          array("index.php","/auth/user/laurent", array()),
          array("index.php","/suburl/foo", array()),
          array("index.php","/suburl/", array()),
          array("index.php","/", array()),
          array('index.php', "/wiki", array()),
          array('index.php', "/wiki/", array()),
          array('index.php', "/wiki/foo", array()),
          array('index.php', "/wiki/foo/bar/", array()),
       );

      //$this->sendMessage("significant, multiview = false");
      foreach($request as $k=>$urldata){
         $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
         $p = $url->params;
         ksort($p);
         ksort($resultList[$k]);

         $this->assertTrue( ($p == $resultList[$k]), 'test '.$k.' created:'.var_export($p,true).' expected:'.var_export($resultList[$k],true));
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
      $this->assertEquals($url->params['action'], 'default:notfound');

      //$this->sendMessage("significant, multiview = true");
      $config->urlengine['multiview']=true;
      $request=array(
          array("index","/test/news/2005/10/35",array()),
          array("index","/test/news/2005/10/35",array("action"=>"urlsig:url8")),
          array("testnews","/2004/05",array()),
          array("index","/test/cms/actualite/65-c-est-la-fete-au-village",array()),
          array("foo/bar","/withhandler/premier/deuxieme",array()),
          array("index",'',array('module'=>'jelix_tests', 'action'=>'urlsig:url5', 'foo'=>'oof',  'bar'=>'rab')),
          array("xmlrpc","",array()),
          array("news","",array('aaa'=>'bbb','action'=>'main:bar')),
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
          array("news","",array('module'=>'news', 'e'=>'g','action'=>'main:chou')),
          array("news","",array('module'=>'articles', 't'=>'r','action'=>'default:zap')),
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

       );
      foreach($request as $k=>$urldata){
         $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
         $p = $url->params;
         ksort($p);
         ksort($resultList[$k]);

         $this->assertTrue( ($p == $resultList[$k]), 'test '.$k.' created:'.var_export($p,true).' expected:'.var_export($resultList[$k],true));
      }

    }

    function testSignificantEngineWithLang() {

        $req = jApp::coord()->request;
        $req->urlScriptPath = '/';
        $req->params = array();
        $config = jApp::config();
        $config->urlengine = array(
            'engine'=>'significant',
            'enableParser'=>true,
            'multiview'=>false,
            'basePath'=>'/',
            'defaultEntrypoint'=>'index',
            'notfoundAct'=>'jelix~notfound',
            'significantFile'=>'urls.xml',
            'checkHttpsOnParsing'=>false
        );
        $config->compilation['force'] = true;
        UTParseUrlsIncluder::resetUrlCache();
        jUrl::getEngine(true);

        $resultList=array();
        $resultList[]= array('fr_FR', array('module'=>'jelix_tests', 'action'=>'urlsig:lang1', 'p1'=>'foo', 'lang'=>'fr'));
        $resultList[]= array('en_EN', array('module'=>'jelix_tests', 'action'=>'urlsig:lang1', 'p1'=>'foo', 'lang'=>'en', 'bar'=>'baz')); // FIXME should be en_US
        $resultList[]= array('fr_FR', array('module'=>'jelix_tests', 'action'=>'urlsig:lang1bis', 'p1'=>'foo', 'lang'=>'fr_FR'));
        $resultList[]= array('fr_FR', array('module'=>'jelix_tests', 'action'=>'urlsig:lang1bis', 'p1'=>'foo', 'lang'=>'fr_FR'));
        $resultList[]= array('en_EN', array('module'=>'jelix_tests', 'action'=>'urlsig:lang1bis', 'p1'=>'foo', 'lang'=>'en_EN')); // FIXME should be en_US
        $resultList[]= array('en_EN', array('module'=>'jelix_tests', 'action'=>'urlsig:lang2', 'p1'=>'foo', 'lang'=>'en'));// FIXME should be en_US
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
            $this->assertEquals($p, $resultList[$k][1], 'test '.$k. ' - %s');
            $this->assertEquals(jApp::config()->locale, $resultList[$k][0], 'test '.$k. ' - %s');
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
            $this->assertEquals($p, $resultList[$k][1], 'test '.$k. ' - %s');
            $this->assertEquals(jApp::config()->locale, $resultList[$k][0], 'test '.$k. ' - %s');
        }
    }

    function testBasicSignificantEngine() {
       $req = jApp::coord()->request;
       $req->urlScriptPath = '/';
       $req->params = array();

       $config = jApp::config();
       $config->urlengine = array(
         'engine'=>'basic_significant',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
         'notfoundAct'=>'jelix~notfound',
         'significantFile'=>'urls.xml',
       );

      jUrl::getEngine(true); // on recharge le nouveau moteur d'url

      $resultList=array();
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url1', 'mois'=>'10',  'annee'=>'2005', 'id'=>'35');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url2', 'mois'=>'05',  'annee'=>'2004');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url3', 'rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fete au village');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url4', 'first'=>'premier',  'second'=>'deuxieme');
      $resultList[]= array();
      $resultList[]= array('module'=>'news',        'action'=>'main:bar',     'aaa'=>'bbb');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url5', 'foo'=>'oof',  'bar'=>'rab');

      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url8', 'rubrique'=>'vetements',  'id_article'=>'98');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'default:index', 'rubrique'=>'vetements',  'id_article'=>'98');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:index', 'rubrique'=>'vetements',  'id_article'=>'98');
      $resultList[]= array('rubrique'=>'vetements',  'id_article'=>'98');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'actu:foo',     'aaa'=>'bbb');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'actu:bar',     'aaa'=>'bbb');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:bar',     'aaa'=>'bbb');

      $request=array(
          array("index.php","/jelix_tests/urlsig/url1",array('mois'=>'10',  'annee'=>'2005', 'id'=>'35')),
          array("testnews.php","/jelix_tests/urlsig/url2",array('mois'=>'05',  'annee'=>'2004')),
          array("testnews.php","/jelix_tests/urlsig/url3",array('rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fete au village')),
          array("foo/bar.php","/jelix_tests/urlsig/url4",array('first'=>'premier',  'second'=>'deuxieme')),
          array("xmlrpc.php","",array()),
          array("news.php","/news/main/bar",array('aaa'=>'bbb')),
          array("index.php","/jelix_tests/urlsig/url5",array('foo'=>'oof',  'bar'=>'rab')),
          array("index.php","/jelix_tests/urlsig/url8",array('rubrique'=>'vetements',  'id_article'=>'98')),
          array("index.php","/jelix_tests/",array('rubrique'=>'vetements',  'id_article'=>'98')),
          array("index.php","/jelix_tests/urlsig/",array('rubrique'=>'vetements',  'id_article'=>'98')),
          array("index.php","",array('rubrique'=>'vetements',  'id_article'=>'98')),
          array("actu.php","/jelix_tests/actu/foo",array('aaa'=>'bbb')),
          array("actu.php","/jelix_tests/actu/bar",array('aaa'=>'bbb')),
          array("actu.php","/jelix_tests/urlsig/bar",array('aaa'=>'bbb')),
       );

      //$this->sendMessage("significant, multiview = false");
      foreach($request as $k=>$urldata){
         $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
         $p = $url->params;
         ksort($p);
         ksort($resultList[$k]);

         $this->assertTrue( ($p == $resultList[$k]), 'created:'.var_export($p,true).' expected:'.var_export($resultList[$k],true));
      }

      $config->urlengine['multiview']=true;
      $request=array(
          array("index","/jelix_tests/urlsig/url1",array('mois'=>'10',  'annee'=>'2005', 'id'=>'35')),
          array("testnews","/jelix_tests/urlsig/url2",array('mois'=>'05',  'annee'=>'2004')),
          array("testnews","/jelix_tests/urlsig/url3",array('rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fete au village')),
          array("foo/bar","/jelix_tests/urlsig/url4",array('first'=>'premier',  'second'=>'deuxieme')),
          array("xmlrpc","",array()),
          array("news","/news/main/bar",array('aaa'=>'bbb')),
          array("index","/jelix_tests/urlsig/url5",array('foo'=>'oof',  'bar'=>'rab')),
          array("index","/jelix_tests/urlsig/url8",array('rubrique'=>'vetements',  'id_article'=>'98')),
          array("index","/jelix_tests/",array('rubrique'=>'vetements',  'id_article'=>'98')),
          array("index","/jelix_tests/urlsig/",array('rubrique'=>'vetements',  'id_article'=>'98')),
          array("index","",array('rubrique'=>'vetements',  'id_article'=>'98')),
          array("actu.php","/jelix_tests/actu/foo",array('aaa'=>'bbb')),
          array("actu.php","/jelix_tests/actu/bar",array('aaa'=>'bbb')),
          array("actu.php","/jelix_tests/urlsig/bar",array('aaa'=>'bbb')),
       );
      foreach($request as $k=>$urldata){
         $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
         $p = $url->params;
         ksort($p);
         ksort($resultList[$k]);

         $this->assertTrue( ($p == $resultList[$k]), 'created:'.var_export($p,true).' expected:'.var_export($resultList[$k],true));
      }
    }

    function testBasicSignificantEngineWithAliases() {
        $req = jApp::coord()->request;
        $req->urlScriptPath = '/';
        $req->params = array();

        $config = jApp::config();
        $config->urlengine = array(
            'engine'=>'basic_significant',
            'enableParser'=>true,
            'multiview'=>false,
            'basePath'=>'/',
            'defaultEntrypoint'=>'index',
            'notfoundAct'=>'jelix~notfound',
            'significantFile'=>'urls.xml',
        );
        $config->basic_significant_urlengine_aliases = array(
            'supernews' => 'news',
            'supertests' => 'jelix_tests'
        );
        jUrl::getEngine(true); // on recharge le nouveau moteur d'url

        $resultList=array();
        $resultList[]= array('module'=>'testapp', 'action'=>'main:hello', 'person'=>'Bob');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url1', 'mois'=>'10',  'annee'=>'2005', 'id'=>'35');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url2', 'mois'=>'05',  'annee'=>'2004');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url3', 'rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fete au village');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url4', 'first'=>'premier',  'second'=>'deuxieme');
        $resultList[]= array();
        $resultList[]= array('module'=>'news',        'action'=>'main:bar',     'aaa'=>'bbb');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url5', 'foo'=>'oof',  'bar'=>'rab');

        $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url8', 'rubrique'=>'vetements',  'id_article'=>'98');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'default:index', 'rubrique'=>'vetements',  'id_article'=>'98');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:index', 'rubrique'=>'vetements',  'id_article'=>'98');
        $resultList[]= array('rubrique'=>'vetements',  'id_article'=>'98');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'actu:foo',     'aaa'=>'bbb');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'actu:bar',     'aaa'=>'bbb');
        $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:bar',     'aaa'=>'bbb');

        $request=array(
            array("index.php","/testapp/main/hello",array('person'=>'Bob')),
            array("index.php","/supertests/urlsig/url1",array('mois'=>'10',  'annee'=>'2005', 'id'=>'35')),
            array("testnews.php","/supertests/urlsig/url2",array('mois'=>'05',  'annee'=>'2004')),
            array("testnews.php","/supertests/urlsig/url3",array('rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fete au village')),
            array("foo/bar.php","/supertests/urlsig/url4",array('first'=>'premier',  'second'=>'deuxieme')),
            array("xmlrpc.php","",array()),
            array("news.php","/supernews/main/bar",array('aaa'=>'bbb')),
            array("index.php","/supertests/urlsig/url5",array('foo'=>'oof',  'bar'=>'rab')),
            array("index.php","/supertests/urlsig/url8",array('rubrique'=>'vetements',  'id_article'=>'98')),
            array("index.php","/supertests/",array('rubrique'=>'vetements',  'id_article'=>'98')),
            array("index.php","/supertests/urlsig/",array('rubrique'=>'vetements',  'id_article'=>'98')),
            array("index.php","",array('rubrique'=>'vetements',  'id_article'=>'98')),
            array("actu.php","/supertests/actu/foo",array('aaa'=>'bbb')),
            array("actu.php","/supertests/actu/bar",array('aaa'=>'bbb')),
            array("actu.php","/supertests/urlsig/bar",array('aaa'=>'bbb')),
        );

        //$this->sendMessage("significant, multiview = false");
        foreach($request as $k=>$urldata){
            $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
            $p = $url->params;
            ksort($p);
            ksort($resultList[$k]);

            $this->assertTrue( ($p == $resultList[$k]), 'created:'.var_export($p,true).' expected:'.var_export($resultList[$k],true));
        }

        $config->urlengine['multiview']=true;
        $request=array(
            array("index","/testapp/main/hello",array('person'=>'Bob')),
            array("index","/supertests/urlsig/url1",array('mois'=>'10',  'annee'=>'2005', 'id'=>'35')),
            array("testnews","/supertests/urlsig/url2",array('mois'=>'05',  'annee'=>'2004')),
            array("testnews","/supertests/urlsig/url3",array('rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fete au village')),
            array("foo/bar","/supertests/urlsig/url4",array('first'=>'premier',  'second'=>'deuxieme')),
            array("xmlrpc","",array()),
            array("news","/supernews/main/bar",array('aaa'=>'bbb')),
            array("index","/supertests/urlsig/url5",array('foo'=>'oof',  'bar'=>'rab')),
            array("index","/supertests/urlsig/url8",array('rubrique'=>'vetements',  'id_article'=>'98')),
            array("index","/supertests/",array('rubrique'=>'vetements',  'id_article'=>'98')),
            array("index","/supertests/urlsig/",array('rubrique'=>'vetements',  'id_article'=>'98')),
            array("index","",array('rubrique'=>'vetements',  'id_article'=>'98')),
            array("actu.php","/supertests/actu/foo",array('aaa'=>'bbb')),
            array("actu.php","/supertests/actu/bar",array('aaa'=>'bbb')),
            array("actu.php","/supertests/urlsig/bar",array('aaa'=>'bbb')),
        );
        foreach($request as $k=>$urldata){
            $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
            $p = $url->params;
            ksort($p);
            ksort($resultList[$k]);

            $this->assertTrue( ($p == $resultList[$k]), 'created:'.var_export($p,true).' expected:'.var_export($resultList[$k],true));
        }

    }

}

?>
