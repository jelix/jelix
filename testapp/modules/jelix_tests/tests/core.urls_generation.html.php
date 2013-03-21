<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2009 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTCreateUrls extends UnitTestCase {
    protected $oldUrlScriptPath;
    protected $oldParams;
    protected $oldRequestType;
    protected $oldserver;
    protected $oldReq;

    function setUp() {
      $req = jApp::coord()->request;
      $this->oldReq = $req = jApp::coord()->request;
      $this->oldUrlScriptPath = $req->urlScriptPath;
      $this->oldParams = $req->params;
      $this->oldRequestType = $req->type;
      $this->oldserver = $_SERVER;
      jApp::saveContext();
    }

    function tearDown() {
      $req = jApp::coord()->request = $this->oldReq;
      $req->urlScriptPath = $this->oldUrlScriptPath;
      $req->params = $this->oldParams;
      $req->type = $this->oldRequestType;
      jApp::restoreContext();
      $_SERVER = $this->oldserver;
      jUrl::getEngine(true);
    }

    protected function _doCompareUrl($title, $urlList, $trueResult ){
        //$this->sendMessage($title);
        foreach($urlList as $k=>$urldata){
            try{
                $url = jUrl::get($urldata[0], $urldata[1]);
                $this->assertEqual($url, $trueResult[$k], 'expected url '.$k.' ='.str_replace('%','%%',$trueResult[$k]).'   created url='.str_replace('%','%%',$url).' %s' );
            }catch(jExceptionSelector $e){
                $this->assertTrue(false,'jExceptionSelector: '.$e->getMessage().' ('.$e->getLocaleKey().') %s');
            }catch(jException $e){
                $this->assertTrue(false,'jException: '.$e->getMessage().' ('.$e->getLocaleKey().') %s');
            }catch(Exception $e){
                $msgerr = '<br>generated exception, code='.$e->getCode().' msg='.$e->getMessage().' %s';
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
                $msgerr = '<br>generated exception, jExceptionSelector code='.$e->getCode().' localkey='.$e->getLocaleKey().' (%s)';
                $this->assertTrue( ($res[0]==2) ,$msg2.$msgerr);
            }catch(jException $e){
                $msgerr = '<br>generated exception, jException code='.$e->getCode().' localkey='.$e->getLocaleKey().' (%s)';
                $this->assertTrue( ($res[0]==1) ,$msg2.$msgerr);
            }catch(Exception $e){
                $msgerr = '<br>generated exception, Exception code='.$e->getCode().' (%s)';
                $this->assertTrue( ($res[0]==0) ,$msg.$msgerr);

            }
        }
    }

    function testSimpleEngine() {

       $req = jApp::coord()->request;
       $req->urlScriptPath = '/';
       $req->params = array();
       $conf = jApp::config();
       $conf->domainName = 'testapp.local';
       $conf->forceHTTPPort = true;
       $conf->forceHTTPSPort = true;
       $conf->urlengine = array(
         'engine'=>'simple',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
         'notfoundAct'=>'jelix~error:notfound',
         'simple_urlengine_https'=>'jelix_tests~urlsig:url8@classic @xmlrpc',
         'significantFile'=>'urls.xml',
       );

      jUrl::getEngine(true); // on recharge le nouveau moteur d'url

      $urlList=array();
      $urlList[]= array('urlsig:url1', array('mois'=>'10',  'annee'=>'2005', 'id'=>'35', 'p'=>null));
      $urlList[]= array('urlsig:url2', array('mois'=>'05',  'annee'=>'2004'));
      $urlList[]= array('jelix_tests~urlsig:url3', array('rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fête au village'));
      $urlList[]= array('jelix_tests~urlsig:url4', array('first'=>'premier',  'second'=>'deuxieme'));
      // celle ci n'a pas de définition dans urls.xml *exprés*
      $urlList[]= array('urlsig:url5', array('foo'=>'oof',  'bar'=>'rab'));
      $urlList[]= array('jelix~bar@xmlrpc', array('aaa'=>'bbb'));
      $urlList[]= array('jelix_tests~urlsig:url8', array('rubrique'=>'vetements',  'id_article'=>'98'));
      $urlList[]= array('jelix_tests~actu:foo', array('aaa'=>'bbb'));
      $urlList[]= array('jelix_tests~actu:bar', array('aaa'=>'@%bbb')); // with special char

      $trueResult=array(
          "/index.php?mois=10&annee=2005&id=35&module=jelix_tests&action=urlsig:url1",
          "/testnews.php?mois=05&annee=2004&module=jelix_tests&action=urlsig:url2",
          "/testnews.php?rubrique=actualite&id_art=65&article=c%27est+la+f%C3%AAte+au+village&module=jelix_tests&action=urlsig:url3",
          "/foo/bar.php?first=premier&second=deuxieme&module=jelix_tests&action=urlsig:url4",
          "/index.php?foo=oof&bar=rab&module=jelix_tests&action=urlsig:url5",
          "/xmlrpc.php",
          "/index.php?rubrique=vetements&id_article=98&module=jelix_tests&action=urlsig:url8",
          "/actu.php?aaa=bbb&module=jelix_tests&action=actu:foo",
          "/actu.php?aaa=%40%25bbb&module=jelix_tests&action=actu:bar",
       );


      $trueResult[5]='https://testapp.local'.$trueResult[5];
      $trueResult[6]='https://testapp.local'.$trueResult[6];
      $this->_doCompareUrl("simple, multiview = false", $urlList,$trueResult);

      $conf->urlengine['multiview']=true;
      jUrl::getEngine(true); // on recharge le nouveau moteur d'url
      $trueResult=array(
          "/index?mois=10&annee=2005&id=35&module=jelix_tests&action=urlsig:url1",
          "/testnews?mois=05&annee=2004&module=jelix_tests&action=urlsig:url2",
          "/testnews?rubrique=actualite&id_art=65&article=c%27est+la+f%C3%AAte+au+village&module=jelix_tests&action=urlsig:url3",
          "/foo/bar?first=premier&second=deuxieme&module=jelix_tests&action=urlsig:url4",
          "/index?foo=oof&bar=rab&module=jelix_tests&action=urlsig:url5",
          "/xmlrpc",
          "/index?rubrique=vetements&id_article=98&module=jelix_tests&action=urlsig:url8",
          "/actu?aaa=bbb&module=jelix_tests&action=actu:foo",
          "/actu?aaa=%40%25bbb&module=jelix_tests&action=actu:bar",
       );
      $trueResult[5]='https://testapp.local'.$trueResult[5];
      $trueResult[6]='https://testapp.local'.$trueResult[6];
      $this->_doCompareUrl("simple, multiview = true", $urlList,$trueResult);
    }



    function testSimpleEngineError(){

       $req = jApp::coord()->request;
       $req->urlScriptPath = '/';
       $req->params = array();
       $conf = jApp::config();
       $conf->urlengine = array(
         'engine'=>'simple',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
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

      $conf->urlengine['multiview']=true;
      $trueResult=array(
          array(2,11,'jelix~errors.selector.invalid.target'),
       );
      $this->_doCompareError("simple, errors multiview = true", $urlList,$trueResult);
    }

    function testSignificantEngine() {

       $req = jApp::coord()->request;
       $req->urlScriptPath = '/';
       $req->params = array();

       $conf = jApp::config();
       $conf->domainName = 'testapp.local';
       $conf->forceHTTPPort = true;
       $conf->forceHTTPSPort = true;
       $conf->urlengine = array(
         'engine'=>'significant',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
         'notfoundAct'=>'jelix~notfound',
         'significantFile'=>'urls.xml',
         'checkHttpsOnParsing'=>true
       );

      $conf->_modulesPathList['news']='/';
      $conf->_modulesPathList['articles']='/';

      jUrl::getEngine(true); // on recharge le nouveau moteur d'url

      $urlList=array();
      $urlList[]= array('urlsig:url1', array('mois'=>'10',  'annee'=>'2005', 'id'=>'01', 'p'=>null));
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
      $urlList[]= array('jelix_tests~urlsig:url13', array('rubrique'=>'@%alimentation',  'id_article'=>'26')); // with special char
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
      $urlList[]= array('jelix_tests~default:wikishow', array('page'=>''));
      $urlList[]= array('jelix_tests~default:wikishow', array('page'=>'foo'));
      $urlList[]= array('testapp~login:in', array('login'=>'foo', 'password'=>'pass'));
      $urlList[]= array('testapp~login:out', array());
      $urlList[]= array('testapp~login:form', array());
      $urlList[]= array('testapp~user:index', array('user'=>'laurent'));
      $urlList[]= array('testapp~main:suburlsfoo', array());
      $urlList[]= array('testapp~main:suburls', array());
      $urlList[]= array('testapp~main:indexghost', array());
      $urlList[]= array('jelix_tests~urlsig:wiki', array('path'=>''));
      $urlList[]= array('jelix_tests~urlsig:wiki', array('path'=>'/'));
      $urlList[]= array('jelix_tests~urlsig:wiki', array('path'=>'foo'));
      $urlList[]= array('jelix_tests~urlsig:wiki', array('path'=>'foo/bar/'));


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
          "/index.php/supershop/%40%25alimentation?id_article=26",
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
          "/index.php/super/wiki/",
          "/index.php/super/wiki/foo",
          "/index.php/auth/dologin?login=foo&password=pass",
          "/index.php/auth/dologout",
          "/index.php/auth/login",
          "/index.php/auth/user/laurent",
          "/index.php/suburl/foo",
          "/index.php/suburl",
          "/index.php/",
          "/index.php/wiki/",
          "/index.php/wiki//",
          "/index.php/wiki/foo",
          "/index.php/wiki/foo/bar/",
       );

      $trueResult[11]='https://testapp.local'.$trueResult[11];
      $this->_doCompareUrl("significant, multiview = false", $urlList,$trueResult);


      $conf->urlengine['multiview']=true;
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
          "/index/supershop/%40%25alimentation?id_article=26",
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
          "/index/super/wiki/",
          "/index/super/wiki/foo",
          "/index/auth/dologin?login=foo&password=pass",
          "/index/auth/dologout",
          "/index/auth/login",
          "/index/auth/user/laurent",
          "/index/suburl/foo",
          "/index/suburl",
          "/index/",
          "/index/wiki/",
          "/index/wiki//",
          "/index/wiki/foo",
          "/index/wiki/foo/bar/",
       );
      $trueResult[11]='https://testapp.local'.$trueResult[11];
      $this->_doCompareUrl("significant, multiview = true", $urlList,$trueResult);

    }

    function testSignificantEngineWithLang() {

        $req = jApp::coord()->request;
        $req->urlScriptPath = '/';
        $req->params = array();

        $conf = jApp::config();
        $conf->domainName = 'testapp.local';
        $conf->forceHTTPPort = true;
        $conf->forceHTTPSPort = true;
        $conf->urlengine = array(
            'engine'=>'significant',
            'enableParser'=>true,
            'multiview'=>false,
            'basePath'=>'/',
            'defaultEntrypoint'=>'index',
            'notfoundAct'=>'jelix~notfound',
            'significantFile'=>'urls.xml',
            'checkHttpsOnParsing'=>true
        );

        jUrl::getEngine(true); // on recharge le nouveau moteur d'url

        $urlList = array();
        $urlList[] = array('fr_FR', 'jelix_tests~urlsig:lang1', array('p1'=>'foo',  'lang'=>'fr'));
        $urlList[] = array('fr_FR', 'jelix_tests~urlsig:lang1', array('p1'=>'foo',  'lang'=>'en'));
        $urlList[] = array('fr_FR', 'jelix_tests~urlsig:lang1', array('p1'=>'foo',  'lang'=>'en_US'));
        $urlList[] = array('en_US', 'jelix_tests~urlsig:lang1', array('p1'=>'foo'));

        $urlList[] = array('fr_FR', 'jelix_tests~urlsig:lang1bis', array('p1'=>'foo',  'lang'=>'fr_FR'));
        $urlList[] = array('fr_FR', 'jelix_tests~urlsig:lang1bis', array('p1'=>'foo',  'lang'=>'en_US'));
        $urlList[] = array('fr_FR', 'jelix_tests~urlsig:lang1bis', array('p1'=>'foo',  'lang'=>'en'));
        $urlList[] = array('en_US', 'jelix_tests~urlsig:lang1bis', array('p1'=>'foo'));

        $urlList[] = array('en_US', 'jelix_tests~urlsig:lang2', array('p1'=>'foo'));
        $urlList[] = array('fr_FR', 'jelix_tests~urlsig:lang2', array('p1'=>'foo'));
        $urlList[] = array('en_US', 'jelix_tests~urlsig:lang2', array('p1'=>'foo', 'lang'=>'en'));
        $urlList[] = array('en_US', 'jelix_tests~urlsig:lang2', array('p1'=>'foo', 'lang'=>'fr'));
        $urlList[] = array('en_US', 'jelix_tests~urlsig:lang2', array('p1'=>'foo', 'lang'=>'en_US'));
        $urlList[] = array('en_US', 'jelix_tests~urlsig:lang2', array('p1'=>'foo', 'lang'=>'fr_FR'));

        $urlList[] = array('en_US', 'jelix_tests~urlsig:lang3', array('p1'=>'foo'));
        $urlList[] = array('fr_FR', 'jelix_tests~urlsig:lang3', array('p1'=>'foo'));
        $urlList[] = array('en_US', 'jelix_tests~urlsig:lang3', array('p1'=>'foo', 'lang'=>'en'));
        $urlList[] = array('en_US', 'jelix_tests~urlsig:lang3', array('p1'=>'foo', 'lang'=>'fr'));
        $urlList[] = array('en_US', 'jelix_tests~urlsig:lang3', array('p1'=>'foo', 'lang'=>'en_US'));
        $urlList[] = array('en_US', 'jelix_tests~urlsig:lang3', array('p1'=>'foo', 'lang'=>'fr_FR'));

        $trueResult = array(
            "/index.php/url-with-lang/test1/fr/foo",
            "/index.php/url-with-lang/test1/en/foo",
            "/index.php/url-with-lang/test1/en/foo",
            "/index.php/url-with-lang/test1/en/foo",

            "/index.php/url-with-lang/test1bis/fr_FR/foo",
            "/index.php/url-with-lang/test1bis/en_US/foo",
            "/index.php/url-with-lang/test1bis/en_EN/foo", // FIXME
            "/index.php/url-with-lang/test1bis/en_US/foo",

            "/index.php/url-with-lang/test2/en/foo",
            "/index.php/url-with-lang/test2/fr/foo",
            "/index.php/url-with-lang/test2/en/foo",
            "/index.php/url-with-lang/test2/fr/foo",
            "/index.php/url-with-lang/test2/en/foo",
            "/index.php/url-with-lang/test2/fr/foo",

            "/index.php/url-with-lang/test3/en/foo",
            "/index.php/url-with-lang/test3/fr/foo",
            "/index.php/url-with-lang/test3/en/foo",
            "/index.php/url-with-lang/test3/fr/foo",
            "/index.php/url-with-lang/test3/en/foo",
            "/index.php/url-with-lang/test3/fr/foo",

         );


        $this->_doCompareUrlLang("significant, multiview = false", $urlList, $trueResult);

        $conf->urlengine['multiview']=true;
        $trueResult=array(
            "/index/url-with-lang/test1/fr/foo",
            "/index/url-with-lang/test1/en/foo",
            "/index/url-with-lang/test1/en/foo",
            "/index/url-with-lang/test1/en/foo",

            "/index/url-with-lang/test1bis/fr_FR/foo",
            "/index/url-with-lang/test1bis/en_US/foo",
            "/index/url-with-lang/test1bis/en_EN/foo", // FIXME
            "/index/url-with-lang/test1bis/en_US/foo",

            "/index/url-with-lang/test2/en/foo",
            "/index/url-with-lang/test2/fr/foo",
            "/index/url-with-lang/test2/en/foo",
            "/index/url-with-lang/test2/fr/foo",
            "/index/url-with-lang/test2/en/foo",
            "/index/url-with-lang/test2/fr/foo",

            "/index/url-with-lang/test3/en/foo",
            "/index/url-with-lang/test3/fr/foo",
            "/index/url-with-lang/test3/en/foo",
            "/index/url-with-lang/test3/fr/foo",
            "/index/url-with-lang/test3/en/foo",
            "/index/url-with-lang/test3/fr/foo",

        );

        $this->_doCompareUrlLang("significant, multiview = true", $urlList,$trueResult);
    }

    protected function _doCompareUrlLang($title, $urlList, $trueResult ){
        foreach($urlList as $k=>$urldata){
            try{
                jApp::config()->locale = $urldata[0];
                $url = jUrl::get($urldata[1], $urldata[2]);
                $this->assertEqual($url, $trueResult[$k], 'url '.$k.' - %s');
            }catch(jExceptionSelector $e){
                $this->assertTrue(false,'jExceptionSelector: '.$e->getMessage().' ('.$e->getLocaleKey().') %s');
            }catch(jException $e){
                $this->assertTrue(false,'jException: '.$e->getMessage().' ('.$e->getLocaleKey().') %s');
            }catch(Exception $e){
                $msgerr = '<br>generated exception, code='.$e->getCode().' msg='.$e->getMessage().' %s';
                $this->sendMessage($msgerr);
                throw $e;
            }
        }
    }

    function testSignificantEngineError(){

       $req = jApp::coord()->request;
       $req->urlScriptPath = '/';
       $req->params = array();

       $conf = jApp::config();
       $conf->urlengine = array(
         'engine'=>'significant',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
         'notfoundAct'=>'jelix~notfound',
         'significantFile'=>'urls.xml',
         'checkHttpsOnParsing'=>true
       );

      $conf->_modulesPathList['news']='/';

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

      $conf->urlengine['multiview']=true;
      $trueResult=array(
          array(2,11,'jelix~errors.selector.invalid.target'),
       );
      $this->_doCompareError("significant, errors multiview = true", $urlList,$trueResult);


    }

    function testBasicSignificantEngine() {

       $req = jApp::coord()->request;
       $req->urlScriptPath = '/';
       $req->params = array();

       $conf = jApp::config();
       $conf->domainName = 'testapp.local';
       $conf->forceHTTPPort = true;
       $conf->forceHTTPSPort = true;
       $conf->urlengine = array(
         'engine'=>'basic_significant',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
         'notfoundAct'=>'jelix~error:notfound',
         'simple_urlengine_https'=>'jelix_tests~urlsig:url8@classic @xmlrpc',
         'significantFile'=>'urls.xml',
       );

      jUrl::getEngine(true); // on recharge le nouveau moteur d'url

      $urlList=array();
      $urlList[]= array('urlsig:url1', array('mois'=>'10',  'annee'=>'2005', 'id'=>'35', 'p'=>null));
      $urlList[]= array('urlsig:url2', array('mois'=>'05',  'annee'=>'2004'));
      $urlList[]= array('jelix_tests~urlsig:url3', array('rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fête au village'));
      $urlList[]= array('jelix_tests~urlsig:url4', array('first'=>'premier',  'second'=>'%@deuxieme')); // with special char
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
          "/foo/bar.php/jelix_tests/urlsig/url4?first=premier&second=%25%40deuxieme",
          "/index.php/jelix_tests/urlsig/url5?foo=oof&bar=rab",
          "/xmlrpc.php",
          "/index.php/jelix_tests/urlsig/url8?rubrique=vetements&id_article=98",
          "/index.php/jelix_tests/?rubrique=vetements&id_article=98",
          "/index.php/jelix_tests/urlsig/?rubrique=vetements&id_article=98",
       );


      $trueResult[5]='https://testapp.local'.$trueResult[5];
      $trueResult[6]='https://testapp.local'.$trueResult[6];
      $this->_doCompareUrl("simple, multiview = false", $urlList,$trueResult);

      $conf->urlengine['multiview']=true;
      jUrl::getEngine(true); // on recharge le nouveau moteur d'url
      $trueResult=array(
          "/index/jelix_tests/urlsig/url1?mois=10&annee=2005&id=35",
          "/jelix_tests/urlsig/url2?mois=05&annee=2004",
          "/jelix_tests/urlsig/url3?rubrique=actualite&id_art=65&article=c%27est+la+f%C3%AAte+au+village",
          "/foo/bar/jelix_tests/urlsig/url4?first=premier&second=%25%40deuxieme",
          "/index/jelix_tests/urlsig/url5?foo=oof&bar=rab",
          "/xmlrpc",
          "/index/jelix_tests/urlsig/url8?rubrique=vetements&id_article=98",
          "/index/jelix_tests/?rubrique=vetements&id_article=98",
          "/index/jelix_tests/urlsig/?rubrique=vetements&id_article=98",
       );
      $trueResult[5]='https://testapp.local'.$trueResult[5];
      $trueResult[6]='https://testapp.local'.$trueResult[6];
      $this->_doCompareUrl("simple, multiview = true", $urlList,$trueResult);
    }



    function testBasicSignificantEngineError(){

       $req = jApp::coord()->request;
       $req->urlScriptPath = '/';
       $req->params = array();

       $conf = jApp::config();
       $conf->urlengine = array(
         'engine'=>'basic_significant',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
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

      $conf->urlengine['multiview']=true;
      $trueResult=array(
          array(2,11,'jelix~errors.selector.invalid.target'),
       );
      $this->_doCompareError("simple, errors multiview = true", $urlList,$trueResult);
    }


    function testGetFullUrl() {

        $req = jApp::coord()->request;
        $req->urlScriptPath = '/';
        $req->params = array();

        $conf = jApp::config();
        $conf->urlengine = array(
          'engine'=>'basic_significant',
          'enableParser'=>true,
          'multiview'=>false,
          'basePath'=>'/',
          'defaultEntrypoint'=>'index',
          'notfoundAct'=>'jelix~error:notfound',
          'simple_urlengine_https'=>'jelix_tests~urlsig:url8@classic @xmlrpc',
          'significantFile'=>'urls.xml',
        );

        /*
         parameters
            $_SERVER['HTTPS'] or not
            $_SERVER['SERVER_NAME'] ot $conf->domainName
            given domainName or not
            jelix_tests~urlsig:url3 (http) or jelix_tests~urlsig:url8 (https)
        */

        $_SERVER['SERVER_NAME'] = 'testapp.local';
        $_SERVER['SERVER_PORT'] = '80';

        // ================= HTTP URL
        unset($_SERVER['HTTPS']);

        // without given domain name, without domain name in config, without https
        $conf->domainName = '';
        jUrl::getEngine(true);
        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,null);
        $this->assertEqual('http://testapp.local/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,null);
        $this->assertEqual('https://testapp.local/index.php/jelix_tests/urlsig/url8', $url);


        // with given domain name, without domain name in config, without https
        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,'football.local');
        $this->assertEqual('http://football.local/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,'football.local');
        $this->assertEqual('https://football.local/index.php/jelix_tests/urlsig/url8', $url);

        // without given domain name, with domain name in config, without https
        $conf->domainName = 'configdomain.local';
        jUrl::getEngine(true);

        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,null);
        $this->assertEqual('http://configdomain.local/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,null);
        $this->assertEqual('https://configdomain.local/index.php/jelix_tests/urlsig/url8', $url);


        // with given domain name, with domain name in config, without https
        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,'football.local');
        $this->assertEqual('http://football.local/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,'football.local');
        $this->assertEqual('https://football.local/index.php/jelix_tests/urlsig/url8', $url);


        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '443';
        // without given domain name, without domain name in config, with https
        $conf->domainName = '';
        jUrl::getEngine(true);

        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,null);
        $this->assertEqual('https://testapp.local/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,null);
        $this->assertEqual('https://testapp.local/index.php/jelix_tests/urlsig/url8', $url);

        // with given domain name, without domain name in config, with https
        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,'football.local');
        $this->assertEqual('https://football.local/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,'football.local');
        $this->assertEqual('https://football.local/index.php/jelix_tests/urlsig/url8', $url);

        // without given domain name, with domain name in config, with https
        $conf->domainName = 'configdomain.local';
        jUrl::getEngine(true);

        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,null);
        $this->assertEqual('https://configdomain.local/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,null);
        $this->assertEqual('https://configdomain.local/index.php/jelix_tests/urlsig/url8', $url);

        // with given domain name, with domain name in config, with https
        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,'football.local');
        $this->assertEqual('https://football.local/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,'football.local');
        $this->assertEqual('https://football.local/index.php/jelix_tests/urlsig/url8', $url);
    }

    function testGetCurrentUrl() {
        $url = jUrl::getCurrentUrl(false, true);
        $this->assertEqual('http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'], $url);

        $_SERVER['PATH_INFO'] = '/zip/yo/';
        $_SERVER['SERVER_NAME'] = 'testapp.local';
        $_SERVER['SERVER_PORT'] = '80';
        $conf = jApp::config();
        $conf->domainName = 'testapp.local';
        $conf->urlengine = array(
          'engine'=>'basic_significant',
          'enableParser'=>true,
          'multiview'=>false,
          'basePath'=>'/',
          'backendBasePath'=>'/',
          'scriptNameServerVariable'=>'SCRIPT_NAME',
          'defaultEntrypoint'=>'index',
          'entrypointExtension'=>'.php',
          'notfoundAct'=>'jelix~error:notfound',
          'pathInfoInQueryParameter'=>'',
          'simple_urlengine_https'=>'jelix_tests~urlsig:url8@classic @xmlrpc',
          'significantFile'=>'urls.xml',
          'urlScript'=>'/noep.php',
          'urlScriptPath'=>'/',
          'urlScriptName'=>'noep.php',
          'urlScriptId'=>'noep',
          'urlScriptIdenc'=>'noep',
          'documentRoot'=>$conf->urlengine['documentRoot'],
          'checkHttpsOnParsing'=>true,
          'jelixWWWPath' =>$conf->urlengine['jelixWWWPath'],
          'jqueryPath' =>$conf->urlengine['jqueryPath'],
        );

        jUrl::getEngine(true);

        $req = jApp::coord()->request = new jClassicRequest();
        $req->init();
        $req->params = array('module'=>'jelix_tests', 'action'=>'urlsig:bug1488', 'var'=>'yo');
        $req->getModuleAction();

        $url = jUrl::getCurrentUrl(false, false);
        $this->assertEqual('/noep.php/jelix_tests/urlsig/bug1488?var=yo', $url);

        $url = jUrl::getCurrentUrl(false, true);
        $this->assertEqual('http://testapp.local/noep.php/jelix_tests/urlsig/bug1488?var=yo', $url);

        $conf = jApp::config();
        $conf->domainName = 'testapp.local';
        $conf->urlengine = array(
          'engine'=>'significant',
          'enableParser'=>true,
          'multiview'=>true,
          'basePath'=>'/',
          'backendBasePath'=>'/',
          'scriptNameServerVariable'=>'SCRIPT_NAME',
          'defaultEntrypoint'=>'index',
          'entrypointExtension'=>'.php',
          'notfoundAct'=>'jelix~error:notfound',
          'pathInfoInQueryParameter'=>'',
          'simple_urlengine_https'=>'jelix_tests~urlsig:url8@classic @xmlrpc',
          'significantFile'=>'urls.xml',
          'urlScript'=>'/noep.php',
          'urlScriptPath'=>'/',
          'urlScriptName'=>'noep.php',
          'urlScriptId'=>'noep',
          'urlScriptIdenc'=>'noep',
          'documentRoot'=>$conf->urlengine['documentRoot'],
          'checkHttpsOnParsing'=>true,
          'jelixWWWPath' =>$conf->urlengine['jelixWWWPath'],
          'jqueryPath' =>$conf->urlengine['jqueryPath'],
        );
        jUrl::getEngine(true);

        $req = jApp::coord()->request = new jClassicRequest();
        $req->init();
        $req->params = array('module'=>'jelix_tests', 'action'=>'urlsig:bug1488', 'var'=>'yo', 'foo'=>'bar');
        $req->getModuleAction();

        $url = jUrl::getCurrentUrl(false, false);
        $this->assertEqual('/zip/yo/?foo=bar', $url);

        $url = jUrl::getCurrentUrl(false, true);
        $this->assertEqual('http://testapp.local/zip/yo/?foo=bar', $url);

    }
}
