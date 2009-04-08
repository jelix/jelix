<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006-2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTCreateUrls extends UnitTestCase {
    protected $oldUrlScriptPath;
    protected $oldParams;
    protected $oldRequestType;
    protected $oldUrlengineConf;
    protected $oldModule;
    protected $simple_urlengine_entrypoints;
    protected $oldActionSelector;

    function setUp() {
      global $gJCoord, $gJConfig;

      $this->oldUrlScriptPath = $gJCoord->request->urlScriptPath;
      $this->oldParams = $gJCoord->request->params;
      $this->oldRequestType = $gJCoord->request->type;
      $this->oldUrlengineConf = $gJConfig->urlengine;
      $this->simple_urlengine_entrypoints = $gJConfig->simple_urlengine_entrypoints;
      $this->oldModule = $gJConfig->_modulesPathList;
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
      $gJConfig->_modulesPathList=$this->oldModule ;
      $gJConfig->enableOldActionSelector = $this->oldActionSelector;
      jUrl::getEngine(true);
    }


    protected function _doCompareUrl($title, $urlList,$trueResult ){
        //$this->sendMessage($title);
        foreach($urlList as $k=>$urldata){
            try{
                $url = jUrl::get($urldata[0], $urldata[1]);
                $this->assertTrue( ($url == $trueResult[$k]), 'expected url '.$k.' ='.$trueResult[$k].'   created url='.$url );
            }catch(jExceptionSelector $e){
                $this->assertTrue(false,'jExceptionSelector: '.$e->getMessage().' ('.$e->getLocaleKey().')');
            }catch(jException $e){
                $this->assertTrue(false,'jException: '.$e->getMessage().' ('.$e->getLocaleKey().')');
            }catch(Exception $e){
                $msgerr = '<br>generated exception, code='.$e->getCode().' msg='.$e->getMessage();
                $this->sendMessage($msgerr);
                throw $e;
            }
        }
    }
    protected function _doCompareError($title, $urlList,$trueResult ){
        //$this->sendMessage($title);

        $labels = array('Exception','jException','jExceptionSelector');

        foreach($urlList as $k=>$urldata){
            $res = $trueResult[$k];
            $msg = 'expected exception:'.$labels[$res[0]].' code='.$res[1];
            $msg2 = $msg.' localkey='.$res[2];

            try{
                $url = jUrl::get($urldata[0], $urldata[1]);
                $this->assertTrue( false, ($res[0]?$msg2:$msg).'<br>No thrown exception !!!');
            }catch(jExceptionSelector $e){
                $msgerr = '<br>generated exception, jExceptionSelector code='.$e->getCode().' localkey='.$e->getLocaleKey();
                $this->assertTrue( ($res[0]==2) ,$msg2.$msgerr);
            }catch(jException $e){
                $msgerr = '<br>generated exception, jException code='.$e->getCode().' localkey='.$e->getLocaleKey();
                $this->assertTrue( ($res[0]==1) ,$msg2.$msgerr);
            }catch(Exception $e){
                $msgerr = '<br>generated exception, Exception code='.$e->getCode();
                $this->assertTrue( ($res[0]==0) ,$msg.$msgerr);

            }
        }
    }

    function testSimpleEngine() {
       global $gJConfig, $gJCoord;

       $gJCoord->request->urlScriptPath='/';
       $gJCoord->request->params=array();
       //$gJCoord->request->type=;
       $gJConfig->urlengine = array(
         'engine'=>'simple',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
         'entrypointExtension'=>'.php',
         'notfoundAct'=>'jelix~error:notfound',
         'simple_urlengine_https'=>'jelix_tests~urlsig:url8@classic @xmlrpc',
         'significantFile'=>'urls.xml',
       );
       
      jUrl::getEngine(true); // on recharge le nouveau moteur d'url

      $urlList=array();
      $urlList[]= array('urlsig:url1', array('mois'=>'10',  'annee'=>'2005', 'id'=>'35'));
      $urlList[]= array('urlsig:url2', array('mois'=>'05',  'annee'=>'2004'));
      $urlList[]= array('jelix_tests~urlsig:url3', array('rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fête au village'));
      $urlList[]= array('jelix_tests~urlsig:url4', array('first'=>'premier',  'second'=>'deuxieme'));
      // celle ci n'a pas de définition dans urls.xml *exprés*
      $urlList[]= array('urlsig:url5', array('foo'=>'oof',  'bar'=>'rab'));
      $urlList[]= array('jelix~bar@xmlrpc', array('aaa'=>'bbb'));
      $urlList[]= array('jelix_tests~urlsig:url8', array('rubrique'=>'vetements',  'id_article'=>'98'));

      $trueResult=array(
          "/index.php?mois=10&annee=2005&id=35&module=jelix_tests&action=urlsig:url1",
          "/testnews.php?mois=05&annee=2004&module=jelix_tests&action=urlsig:url2",
          "/testnews.php?rubrique=actualite&id_art=65&article=c%27est+la+f%C3%AAte+au+village&module=jelix_tests&action=urlsig:url3",
          "/foo/bar.php?first=premier&second=deuxieme&module=jelix_tests&action=urlsig:url4",
          "/index.php?foo=oof&bar=rab&module=jelix_tests&action=urlsig:url5",
          "/xmlrpc.php",
          "/index.php?rubrique=vetements&id_article=98&module=jelix_tests&action=urlsig:url8",
       );


      $trueResult[5]='https://'.$_SERVER['HTTP_HOST'].$trueResult[5];
      $trueResult[6]='https://'.$_SERVER['HTTP_HOST'].$trueResult[6];
      $this->_doCompareUrl("simple, multiview = false", $urlList,$trueResult);

      $gJConfig->urlengine['multiview']=true;
      jUrl::getEngine(true); // on recharge le nouveau moteur d'url
      $trueResult=array(
          "/index?mois=10&annee=2005&id=35&module=jelix_tests&action=urlsig:url1",
          "/testnews?mois=05&annee=2004&module=jelix_tests&action=urlsig:url2",
          "/testnews?rubrique=actualite&id_art=65&article=c%27est+la+f%C3%AAte+au+village&module=jelix_tests&action=urlsig:url3",
          "/foo/bar?first=premier&second=deuxieme&module=jelix_tests&action=urlsig:url4",
          "/index?foo=oof&bar=rab&module=jelix_tests&action=urlsig:url5",
          "/xmlrpc",
          "/index?rubrique=vetements&id_article=98&module=jelix_tests&action=urlsig:url8",
       );
      $trueResult[5]='https://'.$_SERVER['HTTP_HOST'].$trueResult[5];
      $trueResult[6]='https://'.$_SERVER['HTTP_HOST'].$trueResult[6];
      $this->_doCompareUrl("simple, multiview = true", $urlList,$trueResult);
    }



    function testSimpleEngineError(){
       global $gJConfig, $gJCoord;

       $gJCoord->request->urlScriptPath='/';
       $gJCoord->request->params=array();
       //$gJCoord->request->type=;
       $gJConfig->urlengine = array(
         'engine'=>'simple',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
         'entrypointExtension'=>'.php',
         'notfoundAct'=>'jelix~notfound',
         'simple_urlengine_https'=>'jelix_tests~urlsig:url8@classic @xmlrpc',
         'significantFile'=>'urls.xml',
       );

      $urlList=array();
      $urlList[]= array('foo~bar@xmlrpc', array('aaa'=>'bbb'));

      $trueResult=array(
          // type exception : 0 Exception, 1 jException, 2 jExceptionSelector
          // code
          // local key
          array(2,11,'jelix~errors.selector.invalid.target'),
       );

      $this->_doCompareError("simple, errors, multiview = false", $urlList,$trueResult);

      $gJConfig->urlengine['multiview']=true;
      $trueResult=array(
          array(2,11,'jelix~errors.selector.invalid.target'),
       );
      $this->_doCompareError("simple, errors multiview = true", $urlList,$trueResult);
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

      $gJConfig->_modulesPathList['news']='/';
      $gJConfig->_modulesPathList['articles']='/';

      jUrl::getEngine(true); // on recharge le nouveau moteur d'url

      $urlList=array();
      $urlList[]= array('urlsig:url1', array('mois'=>'10',  'annee'=>'2005', 'id'=>'01'));
      $urlList[]= array('urlsig:url9', array('mois'=>'10',  'annee'=>'2005', 'id'=>'09'));
      $urlList[]= array('urlsig:url10', array('mois'=>'10',  'annee'=>'2005', 'id'=>'10'));
      $urlList[]= array('urlsig:url2', array('mois'=>'05',  'annee'=>'2004'));
      $urlList[]= array('jelix_tests~urlsig:url3', array('rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fête au village'));
      $urlList[]= array('jelix_tests~urlsig:url6', array('rubrique'=>'actualite',  'id_art'=>'65'));
      $urlList[]= array('jelix_tests~urlsig:url4', array('first'=>'premier',  'second'=>'deuxieme'));
      // celle ci n'a pas de définition dans urls.xml *exprés*
      $urlList[]= array('urlsig:url5', array('foo'=>'oof',  'bar'=>'rab'));
      $urlList[]= array('jelix~bar@xmlrpc', array('aaa'=>'bbb'));
      $urlList[]= array('news~bar', array('aaa'=>'bbb'));
      $urlList[]= array('jelix_tests~urlsig:url8', array('mois'=>'23',  'annee'=>'2007', 'id'=>'74'));
      $urlList[]= array('jelix_tests~urlsig:url11', array('rubrique'=>'vetements',  'id_article'=>'98'));
      $urlList[]= array('jelix_tests~urlsig:url12', array('rubrique'=>'bricolage',  'id_article'=>'53'));
      $urlList[]= array('jelix_tests~urlsig:url13', array('rubrique'=>'alimentation',  'id_article'=>'26'));
      $urlList[]= array('jelix_tests~urlsig:url20', array('mois'=>'08',  'annee'=>'2007','lang'=>'en_EN'));
      $urlList[]= array('jelix_tests~urlsig:url20', array('mois'=>'08',  'annee'=>'2007','lang'=>'fr_FR'));
      $urlList[]= array('jelix_tests~urlsig:url20', array('mois'=>'08',  'annee'=>'2007'));
      $urlList[]= array('jelix_tests~urlsig:url30', array());
      $urlList[]= array('jelix_tests~default:hello2', array());
      $urlList[]= array('jelix_tests~default:hello3', array());
      $urlList[]= array('jelix_tests~hello3', array());
      $urlList[]= array('jelix_tests~urlsig:bug599', array('first'=>'premier',  'second'=>'deuxieme'));
      $urlList[]= array('testurls~urlsig:urla', array());
      $urlList[]= array('testurls~urlsig:urla', array('first'=>'premier'));
      $urlList[]= array('testurls~urlsig:urlb', array());
      $urlList[]= array('articles~zap', array('f'=>'g'));

      $trueResult=array(
          "/index.php/test/news/2005/10/01",
          "/index.php/test/news/2005/10/09?action=urlsig:url9",
          "/index.php/test/news/2005/10/10?action=urlsig:url10",
          "/testnews.php/2004/05",
          "/index.php/test/cms/actualite/65-c-est-la-fete-au-village",
          "/test/cms2/actualite/65",
          "/foo/bar.php/withhandler/premier/deuxieme",
          "/index.php?foo=oof&bar=rab&module=jelix_tests&action=urlsig:url5",
          "/xmlrpc.php",
          "/news.php?aaa=bbb&module=news&action=default:bar",
          "/index.php/test/news/2007/23/74?action=urlsig:url8",
          "/index.php/shop/vetements/98",
          "/index.php/shop/bricolage/53/",
          "/index.php/supershop/alimentation?id_article=26",
          "/index.php/articles/en/2007/08",
          "/index.php/articles/fr/2007/08",
          "/index.php/articles/fr/2007/08",
          "/index.php/hello",
          "/index.php/hello2",
          "/index.php/hello3",
          "/index.php/hello3",
          "/withhandler/premier/deuxieme",
          "/myhand/urlsig/urla",
          "/myhand/urlsig/urla?first=premier",
          "/myhand/urlsig/urlb",
          "/news.php?f=g&module=articles&action=default:zap",
       );

      $trueResult[11]='https://'.$_SERVER['HTTP_HOST'].$trueResult[11];
      $this->_doCompareUrl("significant, multiview = false", $urlList,$trueResult);


      $gJConfig->urlengine['multiview']=true;
      $trueResult=array(
          "/index/test/news/2005/10/01",
          "/index/test/news/2005/10/09?action=urlsig:url9",
          "/index/test/news/2005/10/10?action=urlsig:url10",
          "/testnews/2004/05",
          "/index/test/cms/actualite/65-c-est-la-fete-au-village",
          "/test/cms2/actualite/65",
          "/foo/bar/withhandler/premier/deuxieme",
          "/index?foo=oof&bar=rab&module=jelix_tests&action=urlsig:url5",
          "/xmlrpc",
          "/news?aaa=bbb&module=news&action=default:bar",
          "/index/test/news/2007/23/74?action=urlsig:url8",
          "/index/shop/vetements/98",
          "/index/shop/bricolage/53/",
          "/index/supershop/alimentation?id_article=26",
          "/index/articles/en/2007/08",
          "/index/articles/fr/2007/08",
          "/index/articles/fr/2007/08",
          "/index/hello",
          "/index/hello2",
          "/index/hello3",
          "/index/hello3",
          "/withhandler/premier/deuxieme",
          "/myhand/urlsig/urla",
          "/myhand/urlsig/urla?first=premier",
          "/myhand/urlsig/urlb",
          "/news?f=g&module=articles&action=default:zap",
       );
      $trueResult[11]='https://'.$_SERVER['HTTP_HOST'].$trueResult[11];
      $this->_doCompareUrl("significant, multiview = true", $urlList,$trueResult);

    }


    function testSignificantEngineError(){
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

      $gJConfig->_modulesPathList['news']='/';

      jUrl::getEngine(true); // on recharge le nouveau moteur d'url


      $urlList=array();
      $urlList[]= array('foo~bar@xmlrpc', array('aaa'=>'bbb'));

      $trueResult=array(
          // type exception : 0 Exception, 1 jException, 2 jExceptionSelector
          // code
          // local key
          array(2,11,'jelix~errors.selector.invalid.target'),
       );

      $this->_doCompareError("significant, errors, multiview = false", $urlList,$trueResult);

      $gJConfig->urlengine['multiview']=true;
      $trueResult=array(
          array(2,11,'jelix~errors.selector.invalid.target'),
       );
      $this->_doCompareError("significant, errors multiview = true", $urlList,$trueResult);


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
         'notfoundAct'=>'jelix~error:notfound',
         'simple_urlengine_https'=>'jelix_tests~urlsig:url8@classic @xmlrpc',
         'significantFile'=>'urls.xml',
       );

      jUrl::getEngine(true); // on recharge le nouveau moteur d'url

      $urlList=array();
      $urlList[]= array('urlsig:url1', array('mois'=>'10',  'annee'=>'2005', 'id'=>'35'));
      $urlList[]= array('urlsig:url2', array('mois'=>'05',  'annee'=>'2004'));
      $urlList[]= array('jelix_tests~urlsig:url3', array('rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fête au village'));
      $urlList[]= array('jelix_tests~urlsig:url4', array('first'=>'premier',  'second'=>'deuxieme'));
      // celle ci n'a pas de définition dans urls.xml *exprés*
      $urlList[]= array('urlsig:url5', array('foo'=>'oof',  'bar'=>'rab'));
      $urlList[]= array('jelix~bar@xmlrpc', array('aaa'=>'bbb'));
      $urlList[]= array('jelix_tests~urlsig:url8', array('rubrique'=>'vetements',  'id_article'=>'98'));
      $urlList[]= array('jelix_tests~default:index', array('rubrique'=>'vetements',  'id_article'=>'98'));
      $urlList[]= array('jelix_tests~urlsig:index', array('rubrique'=>'vetements',  'id_article'=>'98'));

      $trueResult=array(
          "/index.php/jelix_tests/urlsig/url1?mois=10&annee=2005&id=35",
          "/jelix_tests/urlsig/url2?mois=05&annee=2004",
          "/jelix_tests/urlsig/url3?rubrique=actualite&id_art=65&article=c%27est+la+f%C3%AAte+au+village",
          "/foo/bar.php/jelix_tests/urlsig/url4?first=premier&second=deuxieme",
          "/index.php/jelix_tests/urlsig/url5?foo=oof&bar=rab",
          "/xmlrpc.php",
          "/index.php/jelix_tests/urlsig/url8?rubrique=vetements&id_article=98",
          "/index.php/jelix_tests/?rubrique=vetements&id_article=98",
          "/index.php/jelix_tests/urlsig/?rubrique=vetements&id_article=98",
       );


      $trueResult[5]='https://'.$_SERVER['HTTP_HOST'].$trueResult[5];
      $trueResult[6]='https://'.$_SERVER['HTTP_HOST'].$trueResult[6];
      $this->_doCompareUrl("simple, multiview = false", $urlList,$trueResult);

      $gJConfig->urlengine['multiview']=true;
      jUrl::getEngine(true); // on recharge le nouveau moteur d'url
      $trueResult=array(
          "/index/jelix_tests/urlsig/url1?mois=10&annee=2005&id=35",
          "/jelix_tests/urlsig/url2?mois=05&annee=2004",
          "/jelix_tests/urlsig/url3?rubrique=actualite&id_art=65&article=c%27est+la+f%C3%AAte+au+village",
          "/foo/bar/jelix_tests/urlsig/url4?first=premier&second=deuxieme",
          "/index/jelix_tests/urlsig/url5?foo=oof&bar=rab",
          "/xmlrpc",
          "/index/jelix_tests/urlsig/url8?rubrique=vetements&id_article=98",
          "/index/jelix_tests/?rubrique=vetements&id_article=98",
          "/index/jelix_tests/urlsig/?rubrique=vetements&id_article=98",
       );
      $trueResult[5]='https://'.$_SERVER['HTTP_HOST'].$trueResult[5];
      $trueResult[6]='https://'.$_SERVER['HTTP_HOST'].$trueResult[6];
      $this->_doCompareUrl("simple, multiview = true", $urlList,$trueResult);
    }



    function testBasciSignificantEngineError(){
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
         'simple_urlengine_https'=>'jelix_tests~urlsig:url8@classic @xmlrpc',
         'significantFile'=>'urls.xml',
       );

      $urlList=array();
      $urlList[]= array('foo~bar@xmlrpc', array('aaa'=>'bbb'));

      $trueResult=array(
          // type exception : 0 Exception, 1 jException, 2 jExceptionSelector
          // code
          // local key
          array(2,11,'jelix~errors.selector.invalid.target'),
       );

      $this->_doCompareError("simple, errors, multiview = false", $urlList,$trueResult);

      $gJConfig->urlengine['multiview']=true;
      $trueResult=array(
          array(2,11,'jelix~errors.selector.invalid.target'),
       );
      $this->_doCompareError("simple, errors multiview = true", $urlList,$trueResult);
    }




}
?>