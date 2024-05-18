<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class urlsGenerationTest extends \Jelix\UnitTests\UnitTestCase {
    protected $oldserver;

    function setUp() : void  {
        $this->oldserver = $_SERVER;
        jApp::saveContext();
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        jIncluder::clear();
        parent::setUp();
    }
    
    function tearDown() : void  {
        jApp::popCurrentModule();
        jApp::restoreContext();
        $_SERVER = $this->oldserver;
    }

    protected function _doCompareUrl($title, $urlList, $trueResult ){
        //$this->sendMessage($title);
        foreach($urlList as $k=>$urldata){
            try{
                $url = jUrl::get($urldata[0], $urldata[1]);
                $this->assertEquals($trueResult[$k], $url, 'expected url '.$k.' ='.str_replace('%','%%',$trueResult[$k]).'   created url='.str_replace('%','%%',$url) );
            }catch(\Jelix\Core\Selector\Exception $e){
                $this->assertTrue(false,'jExceptionSelector: '.$e->getMessage().' ('.$e->getLocaleKey().') %s');
            }catch(jException $e){
                $this->assertTrue(false,'jException: '.$e->getMessage().' ('.$e->getLocaleKey().') %s');
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
                $this->assertTrue( false, ($res[0]?$msg2:$msg).' - No thrown exception !!!');
            }catch(\Jelix\Core\Selector\Exception $e){
                $msgerr = 'generated exception, jExceptionSelector code='.$e->getCode().' localkey='.$e->getLocaleKey().' (%s)';
                $this->assertTrue( ($res[0]==2) ,$msg2.$msgerr);
            }catch(jException $e){
                $msgerr = 'generated exception, jException code='.$e->getCode().' localkey='.$e->getLocaleKey().' (%s)';
                $this->assertTrue( ($res[0]==1) ,$msg2.$msgerr);
            }catch(Exception $e){
                $msgerr = 'generated exception, Exception code='.$e->getCode().' (%s)';
                $this->assertTrue( ($res[0]==0) ,$msg.$msgerr);

            }
        }
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
         'multiview'=>false,
         'basePath'=>'/',
         'notFoundAct'=>'jelix~error:notfound',
         'significantFile'=>'urlsfiles/url_maintests.xml',
         'checkHttpsOnParsing'=>true,
         'urlScriptIdenc'=>'index',
         'forceProxyProtocol' =>''
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
      // For this one, no definition in urls.xml, this is intentional
      $urlList[]= array('urlsig:url5', array('foo'=>'oof',  'bar'=>'rab'));
      $urlList[]= array('jelix~bar@xmlrpc', array('aaa'=>'bbb'));
      $urlList[]= array('news~bar', array('aaa'=>'bbb'));
      //10
      $urlList[]= array('jelix_tests~urlsig:url8', array('mois'=>'23',  'annee'=>'2007', 'id'=>'74'));
      $urlList[]= array('jelix_tests~urlsig:url11', array('rubrique'=>'vetements',  'id_article'=>'98'));
      $urlList[]= array('jelix_tests~urlsig:url12', array('rubrique'=>'bricolage',  'id_article'=>'53'));
      $urlList[]= array('jelix_tests~urlsig:url13', array('rubrique'=>'@%alimentation',  'id_article'=>'26')); // with special char
      $urlList[]= array('jelix_tests~urlsig:url20', array('mois'=>'08',  'annee'=>'2007','lang'=>'en_US'));
      $urlList[]= array('jelix_tests~urlsig:url20', array('mois'=>'08',  'annee'=>'2007','lang'=>'fr_FR'));
      $urlList[]= array('jelix_tests~urlsig:url20', array('mois'=>'08',  'annee'=>'2007'));
      $urlList[]= array('jelix_tests~urlsig:url30', array());
      $urlList[]= array('jelix_tests~default:hello2', array());
      $urlList[]= array('jelix_tests~default:hello3', array());
      //20
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
      //30
      $urlList[]= array('testapp~login:form', array());
      $urlList[]= array('testapp~user:index', array('user'=>'laurent'));
      $urlList[]= array('testapp~main:suburlsfoo', array());
      $urlList[]= array('testapp~main:suburls', array());
      $urlList[]= array('testapp~main:indexghost', array());
      $urlList[]= array('jelix_tests~urlsig:wiki', array('path'=>''));
      $urlList[]= array('jelix_tests~urlsig:wiki', array('path'=>'/'));
      $urlList[]= array('jelix_tests~urlsig:wiki', array('path'=>'foo'));
      $urlList[]= array('jelix_tests~urlsig:wiki', array('path'=>'foo/bar/'));
      $urlList[]= array('jfeeds~myctrl:index', array());
      $urlList[]= array('jfeeds~myctrl:foo', array());

      $trueResult=array(
          "/index.php/test/news/2005/10/01",
          "/index.php/test/news/2005/10/09?action=urlsig:url9",
          "/index.php/test/news/2005/10/10?action=urlsig:url10",
          "/testnews.php/2004/05",
          "/index.php/test/cms/actualite/65-c-est-la-fete-au-village",
          "/test/cms2/actualite/65",
          "/foo/bar.php/withhandler/premier/deuxieme",
          "/index.php/jelix_tests/urlsig/url5?foo=oof&bar=rab",
          "/xmlrpc.php",
          "/news.php/news/default/bar?aaa=bbb",
          // 10
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
          //20
          "/index.php/hello3",
          "/withhandler/premier/deuxieme",
          "/myhand/urlsig/urla",
          "/myhand/urlsig/urla?first=premier",
          "/myhand/urlsig/urlb",
          "/news.php/articles/default/zap?f=g",
          "/index.php/super/wiki/",
          "/index.php/super/wiki/foo",
          "/index.php/auth/dologin?login=foo&password=pass",
          "/index.php/auth/dologout",
          //30
          "/index.php/auth/login",
          "/index.php/auth/user/laurent",
          "/index.php/suburl/foo",
          "/index.php/suburl",
          "/index.php",
          "/index.php/wiki/",
          "/index.php/wiki//",
          "/index.php/wiki/foo",
          "/index.php/wiki/foo/bar/",
          "/index.php/dynamic/method",
          "/index.php/dynamic/method/foo"
       );

      $trueResult[11]='https://testapp.local'.$trueResult[11];
      $this->_doCompareUrl("multiview = false", $urlList,$trueResult);


      $conf->urlengine['multiview']=true;
      jUrl::getEngine(true);

      $trueResult=array(
          "/index/test/news/2005/10/01",
          "/index/test/news/2005/10/09?action=urlsig:url9",
          "/index/test/news/2005/10/10?action=urlsig:url10",
          "/testnews/2004/05",
          "/index/test/cms/actualite/65-c-est-la-fete-au-village",
          "/test/cms2/actualite/65",
          "/foo/bar/withhandler/premier/deuxieme",
          "/index/jelix_tests/urlsig/url5?foo=oof&bar=rab",
          "/xmlrpc",
          "/news/news/default/bar?aaa=bbb",
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
          "/news/articles/default/zap?f=g",
          "/index/super/wiki/",
          "/index/super/wiki/foo",
          "/index/auth/dologin?login=foo&password=pass",
          "/index/auth/dologout",
          "/index/auth/login",
          "/index/auth/user/laurent",
          "/index/suburl/foo",
          "/index/suburl",
          "/index",
          "/index/wiki/",
          "/index/wiki//",
          "/index/wiki/foo",
          "/index/wiki/foo/bar/",
          "/index/dynamic/method",
          "/index/dynamic/method/foo"
       );
      $trueResult[11]='https://testapp.local'.$trueResult[11];
      $this->_doCompareUrl("multiview = true", $urlList,$trueResult);

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
            'multiview'=>false,
            'basePath'=>'/',
            'notFoundAct'=>'jelix~error:notfound',
            'significantFile'=>'urlsfiles/url_maintests.xml',
            'checkHttpsOnParsing'=>true,
            'urlScriptIdenc'=>'index',
            'forceProxyProtocol' =>''
        );
        $conf->_modulesPathList['news']='/';
        $conf->_modulesPathList['articles']='/';
        //$conf->availableLocales = array('fr_FR', 'en_US');
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
            "/index.php/url-with-lang/test1bis/en_US/foo",
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


        $this->_doCompareUrlLang("multiview = false", $urlList, $trueResult);

        $conf->urlengine['multiview']=true;
        jUrl::getEngine(true);

        $trueResult=array(
            "/index/url-with-lang/test1/fr/foo",
            "/index/url-with-lang/test1/en/foo",
            "/index/url-with-lang/test1/en/foo",
            "/index/url-with-lang/test1/en/foo",

            "/index/url-with-lang/test1bis/fr_FR/foo",
            "/index/url-with-lang/test1bis/en_US/foo",
            "/index/url-with-lang/test1bis/en_US/foo",
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

        $this->_doCompareUrlLang("multiview = true", $urlList,$trueResult);
    }

    protected function _doCompareUrlLang($title, $urlList, $trueResult ){
        foreach($urlList as $k=>$urldata){
            try{
                jApp::config()->locale = $urldata[0];
                $url = jUrl::get($urldata[1], $urldata[2]);
                $this->assertEquals($trueResult[$k], $url, 'url '.$k.' - %s');
            }catch(\Jelix\Core\Selector\Exception $e){
                $this->assertTrue(false,'jExceptionSelector: '.$e->getMessage().' ('.$e->getLocaleKey().') %s');
            }catch(jException $e){
                $this->assertTrue(false,'jException: '.$e->getMessage().' ('.$e->getLocaleKey().') %s');
            }
        }
    }

    function testDedicatedModule() {

        $req = jApp::coord()->request;
        $req->urlScriptPath = '/';
        $req->params = array();
 
        $conf = jApp::config();
        $conf->domainName = 'testapp.local';
        $conf->forceHTTPPort = true;
        $conf->forceHTTPSPort = true;
        $conf->urlengine = array(
            'multiview'=>false,
            'basePath'=>'/',
            'notFoundAct'=>'jelix~error:notfound',
            'significantFile'=>'urlsfiles/url_dedicatedmodule.xml',
            'checkHttpsOnParsing'=>true,
            'urlScriptIdenc'=>'index',
            'forceProxyProtocol' =>''
        );

        $conf->_modulesPathList['news']='/';
        $conf->_modulesPathList['articles']='/';

        jUrl::getEngine(true);

        $urlList = array();
        $urlList[]= array('testapp~default:index', array());
        $urlList[]= array('testapp~foo:index', array());
        $urlList[]= array('testapp~foo:bar', array());

        $urlList[]= array('news~default:index', array());
        $urlList[]= array('news~foo:index', array());
        $urlList[]= array('news~foo:bar', array());
        $urlList[]= array('articles~default:index', array());
        $urlList[]= array('articles~foo:index', array());
        $urlList[]= array('articles~foo:bar', array());

        $urlList[]= array('jelix~jforms:getListData@classic', array());

        $trueResult=array(
            "/index.php",
            "/index.php/testapp/foo",
            "/index.php/testapp/foo/bar",
            "/news.php",
            "/news.php/news/foo",
            "/news.php/news/foo/bar",
            "/news.php/mynews",
            "/news.php/mynews/foo",
            "/news.php/mynews/foo/bar",
            "/index.php/jelix/jforms/getListData"
        );

        $this->_doCompareUrl("testDedicatedModule", $urlList, $trueResult);
    }
    
    function testSignificantEngineError(){

       $req = jApp::coord()->request;
       $req->urlScriptPath = '/';
       $req->params = array();

       $conf = jApp::config();
       $conf->urlengine = array(
         'multiview'=>false,
         'basePath'=>'/',
         'notFoundAct'=>'jelix~error:notfound',
         'significantFile'=>'urlsfiles/url_maintests.xml',
         'checkHttpsOnParsing'=>true,
         'urlScriptIdenc'=>'index',
         'forceProxyProtocol' =>''
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

    function testGetFullUrl() {

        $req = jApp::coord()->request;
        $req->urlScriptPath = '/';
        $req->params = array();

        $conf = jApp::config();
        $conf->urlengine = array(
          'multiview'=>false,
          'basePath'=>'/',
          'notFoundAct'=>'jelix~error:notfound',
          'significantFile'=>'urls.xml',
          'urlScriptIdenc'=>'index',
          'forceProxyProtocol' =>''
        );

        // parameters
        //   $_SERVER['HTTPS'] or not
        //   $_SERVER['SERVER_NAME'] ot $conf->domainName
        //   given domainName or not
        //   jelix_tests~urlsig:url3 (http) or jelix_tests~urlsig:url8 (https)

        $_SERVER['HTTP_HOST'] = TESTAPP_HOST;
        $_SERVER['SERVER_NAME'] = TESTAPP_HOST;
        $_SERVER['SERVER_PORT'] = TESTAPP_PORT;

        // ================= HTTP URL
        unset($_SERVER['HTTPS']);

        // reset domain cache
        $this->assertEquals(array(TESTAPP_HOST, TESTAPP_PORT), jServer::getDomainPortFromServer(false));

        // without given domain name, without domain name in config, without https
        $conf->domainName = '';
        jUrl::getEngine(true);
        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,null);
        $this->assertEquals('http://'.TESTAPP_URL_HOST_PORT.'/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,null);
        $this->assertEquals('https://'.TESTAPP_HOST.'/index.php/jelix_tests/urlsig/url8', $url);

        // with given domain name, without domain name in config, without https
        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,'football.local');
        $this->assertEquals('http://football.local/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,'football.local');
        $this->assertEquals('https://football.local/index.php/jelix_tests/urlsig/url8', $url);

        // without given domain name, with domain name in config, without https
        $conf->domainName = 'configdomain.local';
        jUrl::getEngine(true);

        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,null);
        $this->assertEquals('http://configdomain.local'.(TESTAPP_PORT?':'.TESTAPP_PORT:'').'/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,null);
        $this->assertEquals('https://configdomain.local/index.php/jelix_tests/urlsig/url8', $url);


        // with given domain name, with domain name in config, without https
        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,'football.local');
        $this->assertEquals('http://football.local/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,'football.local');
        $this->assertEquals('https://football.local/index.php/jelix_tests/urlsig/url8', $url);


        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '443';

        // reset domain cache
        $this->assertEquals(array(TESTAPP_HOST, 443), jServer::getDomainPortFromServer(false));
        // without given domain name, without domain name in config, with https
        $conf->domainName = '';
        jUrl::getEngine(true);

        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,null);
        $this->assertEquals('https://'.TESTAPP_HOST.'/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,null);
        $this->assertEquals('https://'.TESTAPP_HOST.'/index.php/jelix_tests/urlsig/url8', $url);

        // with given domain name, without domain name in config, with https
        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,'football.local');
        $this->assertEquals('https://football.local/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,'football.local');
        $this->assertEquals('https://football.local/index.php/jelix_tests/urlsig/url8', $url);

        // without given domain name, with domain name in config, with https
        $conf->domainName = 'configdomain.local';
        jUrl::getEngine(true);

        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,null);
        $this->assertEquals('https://configdomain.local/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,null);
        $this->assertEquals('https://configdomain.local/index.php/jelix_tests/urlsig/url8', $url);

        // with given domain name, with domain name in config, with https
        $url = jUrl::getFull('jelix_tests~urlsig:url1',array(),0,'football.local');
        $this->assertEquals('https://football.local/index.php/jelix_tests/urlsig/url1', $url);

        $url = jUrl::getFull('jelix_tests~urlsig:url8',array(),0,'football.local');
        $this->assertEquals('https://football.local/index.php/jelix_tests/urlsig/url8', $url);
    }

    function testGetCurrentUrl() {

        $_SERVER['HTTP_HOST'] = TESTAPP_HOST;
        $_SERVER['PATH_INFO'] = '/zip/yo/';
        $_SERVER['SERVER_NAME'] = TESTAPP_HOST;
        $_SERVER['SERVER_PORT'] = TESTAPP_PORT;
        unset($_SERVER['HTTPS']);
        $conf = jApp::config();
        $conf->forceHTTPPort = '';
        $conf->forceHTTPSPort = '';

        // reset domain cache
        $this->assertEquals(array(TESTAPP_HOST, TESTAPP_PORT), jServer::getDomainPortFromServer(false));

        $url = jUrl::getCurrentUrl(false, true);
        $this->assertEquals('http://'.TESTAPP_URL_HOST_PORT.'/index.php', $url);

        $conf = jApp::config();
        $conf->domainName = TESTAPP_HOST;
        $conf->urlengine = array(
          'multiview'=>false,
          'basePath'=>'/',
          'backendBasePath'=>'/',
          'scriptNameServerVariable'=>'SCRIPT_NAME',
          'notFoundAct'=>'jelix~error:notfound',
          'pathInfoInQueryParameter'=>'',
          'significantFile'=>'urlsfiles/url_maintests.xml',
          'urlScript'=>'/noep.php',
          'urlScriptPath'=>'/',
          'urlScriptName'=>'noep.php',
          'urlScriptId'=>'noep',
          'urlScriptIdenc'=>'noep',
          'documentRoot'=>$conf->urlengine['documentRoot'],
          'checkHttpsOnParsing'=>true,
          'jelixWWWPath' =>$conf->urlengine['jelixWWWPath'],
          'forceProxyProtocol' =>''
        );

        jUrl::getEngine(true);

        $req = jApp::coord()->request = new jClassicRequest();
        $req->init(jApp::coord()->getUrlActionMapper());
        $req->params = array('module'=>'jelix_tests', 'action'=>'urlsig:bug1488', 'var'=>'yo');
        $req->getModuleAction();

        $url = jUrl::getCurrentUrl(false, false);
        $this->assertEquals('/zip/yo/', $url);

        $url = jUrl::getCurrentUrl(false, true);
        $this->assertEquals('http://'.TESTAPP_URL_HOST_PORT.'/zip/yo/', $url);

        $conf = jApp::config();
        $conf->domainName = TESTAPP_HOST;
        $conf->urlengine = array(
          'multiview'=>true,
          'basePath'=>'/',
          'backendBasePath'=>'/',
          'scriptNameServerVariable'=>'SCRIPT_NAME',
          'notFoundAct'=>'jelix~error:notfound',
          'pathInfoInQueryParameter'=>'',
          'significantFile'=>'urlsfiles/url_maintests.xml',
          'urlScript'=>'/noep.php',
          'urlScriptPath'=>'/',
          'urlScriptName'=>'noep.php',
          'urlScriptId'=>'noep',
          'urlScriptIdenc'=>'noep',
          'documentRoot'=>$conf->urlengine['documentRoot'],
          'checkHttpsOnParsing'=>true,
          'jelixWWWPath' =>$conf->urlengine['jelixWWWPath'],
          'forceProxyProtocol' =>''
        );
        jUrl::getEngine(true);

        $req = jApp::coord()->request = new jClassicRequest();
        $req->init(jApp::coord()->getUrlActionMapper());
        $req->params = array('module'=>'jelix_tests', 'action'=>'urlsig:bug1488', 'var'=>'yo', 'foo'=>'bar');
        $req->getModuleAction();

        $url = jUrl::getCurrentUrl(false, false);
        $this->assertEquals('/zip/yo/?foo=bar', $url);

        $url = jUrl::getCurrentUrl(false, true);
        $this->assertEquals('http://'.TESTAPP_URL_HOST_PORT.'/zip/yo/?foo=bar', $url);

    }

    function testRedefinedUrl() {

        $req = jApp::coord()->request;
        $req->urlScriptPath = '/';
        $req->params = array();

        $conf = jApp::config();
        $conf->domainName = 'testapp.local';
        $conf->forceHTTPPort = true;
        $conf->forceHTTPSPort = true;
        $conf->urlengine = array(
            'multiview'=>false,
            'basePath'=>'/',
            'notFoundAct'=>'jelix~error:notfound',
            'significantFile'=>'urlsfiles/url_mainredefined.xml',
            'localSignificantFile'=> 'urlsfiles/url_redefined.xml',
            'checkHttpsOnParsing'=>true,
            'urlScriptIdenc'=>'index',
            'forceProxyProtocol' =>''
        );

        $conf->_modulesPathList['news']='/';
        $conf->_modulesPathList['articles']='/';

        jUrl::getEngine(true);

        $urlList = array();
        $urlList[]= array('testapp~main:index', array());
        $urlList[]= array('testapp~foo:index', array());
        $urlList[]= array('testapp~foo:bar', array());

        $urlList[]= array('news~default:index', array());
        $urlList[]= array('news~foo:index', array());
        $urlList[]= array('news~foo:bar', array());

        $urlList[]= array('articles~default:index', array());
        $urlList[]= array('articles~foo:index', array());
        $urlList[]= array('articles~foo:bar', array());

        $urlList[]= array('jelix~jforms:getListData@classic', array());

        $urlList[]= array('jelix_tests~urlsig:url4', array('first'=>'premier',  'second'=>'deuxieme'));
        $urlList[]= array('jelix_tests~urlsig:display', array('var'=>'chimlou'));

        $trueResult=array(
            "/index.php",
            "/index.php/testapp/foo",
            "/index.php/testapp/foo/bar",

            "/news.php",
            "/news.php/news/foo",
            "/news.php/news/foo/bar",

            "/news.php/articles",
            "/news.php/articles/foo",
            "/news.php/articles/foo/bar",

            "/index.php/jelix/jforms/getListData",
            "/foo/bar.php/withhandler/premier/deuxieme",
            "/foo/bar.php/sopar/chimlou"
        );

        $this->_doCompareUrl("testRedefinedUrl", $urlList, $trueResult);
    }


}
