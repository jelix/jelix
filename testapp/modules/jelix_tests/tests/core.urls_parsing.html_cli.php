<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor Thibault PIRONT < nuKs >
* @copyright   2005-2009 Laurent Jouanneau
* @copyright   2007 Thibault PIRONT
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTParseUrls extends UnitTestCase {
    protected $oldUrlScriptPath;
    protected $oldParams;
    protected $oldRequestType;
    protected $oldUrlengineConf;
    protected $simple_urlengine_entrypoints;
    protected $oldActionSelector;

    function setUp() {
      global $gJCoord, $gJConfig;

      $this->oldUrlScriptPath = $gJCoord->request->urlScriptPath;
      $this->oldParams = $gJCoord->request->params;
      $this->oldRequestType = $gJCoord->request->type;
      $this->oldUrlengineConf = $gJConfig->urlengine;
      $this->simple_urlengine_entrypoints = $gJConfig->simple_urlengine_entrypoints;
      $this->oldActionSelector = $gJConfig->enableOldActionSelector;
      $gJConfig->enableOldActionSelector = false;
    }

    function tearDown() {
      global $gJCoord, $gJConfig;

      $gJCoord->request->urlScriptPath=$this->oldUrlScriptPath;
      $gJCoord->request->params=$this->oldParams;
      $gJCoord->request->type=$this->oldRequestType;
      $gJConfig->urlengine = $this->oldUrlengineConf;
      $gJConfig->simple_urlengine_entrypoints = $this->simple_urlengine_entrypoints;
      $gJConfig->enableOldActionSelector = $this->oldActionSelector;
      jUrl::getEngine(true);
    }

    function testSignificantEngine() {
       global $gJConfig, $gJCoord;

       $gJCoord->request->urlScriptPath='/';
       $gJCoord->request->params=array();
       //$gJCoord->request->type=;
       $gJConfig->urlengine = array(
         'engine'=>'significant',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
         'entrypointExtension'=>'.php',
         'notfoundAct'=>'jelix~notfound',
         'significantFile'=>'urls.xml',
       );

      jUrl::getEngine(true); // on recharge le nouveau moteur d'url


      $resultList=array();
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url1', 'mois'=>'10',  'annee'=>'2005', 'id'=>'35');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url8', 'mois'=>'10',  'annee'=>'2005', 'id'=>'35');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url2', 'mois'=>'05',  'annee'=>'2004', "mystatic"=>"valeur statique");
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url3', 'rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c est la fete au village');
      $resultList[]= array('module'=>'jelix_tests', 'action'=>'urlsig:url4', 'first'=>'premier',  'second'=>'deuxieme');
      // celle ci n'a pas de définition dans urls.xml *exprés*
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
       );

      //$this->sendMessage("significant, multiview = false");
      foreach($request as $k=>$urldata){
         $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
         $p = $url->params;
         ksort($p);
         ksort($resultList[$k]);

         $this->assertTrue( ($p == $resultList[$k]), 'test '.$k.' created:'.var_export($p,true).' expected:'.var_export($resultList[$k],true));
      }


      //$this->sendMessage("significant, multiview = true");
      $gJConfig->urlengine['multiview']=true;
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

       );
      foreach($request as $k=>$urldata){
         $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
         $p = $url->params;
         ksort($p);
         ksort($resultList[$k]);

         $this->assertTrue( ($p == $resultList[$k]), 'test '.$k.' created:'.var_export($p,true).' expected:'.var_export($resultList[$k],true));
      }

    }

    function testBasicSignificantEngine() {
       global $gJConfig, $gJCoord;

       $gJCoord->request->urlScriptPath='/';
       $gJCoord->request->params=array();
       //$gJCoord->request->type=;
       $gJConfig->urlengine = array(
         'engine'=>'basic_significant',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
         'entrypointExtension'=>'.php',
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

      $gJConfig->urlengine['multiview']=true;
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

}

?>