<?php
/**
* @package     testapp
* @subpackage  unittest module
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
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


    function setUp() {
      global $gJCoord, $gJConfig;

      $this->oldUrlScriptPath = $gJCoord->request->url_script_path;
      $this->oldParams = $gJCoord->request->params;
      $this->oldRequestType = $gJCoord->request->type;
      $this->oldUrlengineConf = $gJConfig->urlengine;
      $this->simple_urlengine_entrypoints = $gJConfig->simple_urlengine_entrypoints;
      $this->oldModule = $gJConfig->_modulesPathList;
    }

    function tearDown() {
      global $gJCoord, $gJConfig;

      $gJCoord->request->url_script_path=$this->oldUrlScriptPath;
      $gJCoord->request->params=$this->oldParams;
      $gJCoord->request->type=$this->oldRequestType;
      $gJConfig->urlengine = $this->oldUrlengineConf;
      $gJConfig->simple_urlengine_entrypoints = $this->simple_urlengine_entrypoints;
      $gJConfig->_modulesPathList=$this->oldModule ;
    }


    protected function _doCompareUrl($title, $urlList,$trueResult ){
        $this->sendMessage($title);
        foreach($urlList as $k=>$urldata){
            try{
                $url = jUrl::get($urldata[0], $urldata[1]);
                $this->assertTrue( ($url == $trueResult[$k]), 'url attendue='.$trueResult[$k].'   url créée='.$url );
            }catch(jExceptionSelector $e){
                $this->assertTrue(false,'jExceptionSelector: '.$e->getMessage());
            }catch(jException $e){
                $this->assertTrue(false,'jException: '.$e->getMessage());
            }
        }
    }
    protected function _doCompareError($title, $urlList,$trueResult ){
        $this->sendMessage($title);

        $labels = array('Exception','jException','jExceptionSelector');

        foreach($urlList as $k=>$urldata){
            $res = $trueResult[$k];
            $msg = 'Attendu : exception='.$labels[$res[0]].' code='.$res[1];
            $msg2 = $msg.' localkey='.$res[2];

            try{
                $url = jUrl::get($urldata[0], $urldata[1]);
                $this->assertTrue( false, ($res[0]?$msg2:$msg).'<br>Survenue : aucune !!!');
            }catch(jExceptionSelector $e){
                $msgerr = '<br>Survenue : exception=jExceptionSelector code='.$e->getCode().' localkey='.$e->getMessage();
                $this->assertTrue( ($res[0]==2) ,$msg2.$msgerr);
            }catch(jException $e){
                $msgerr = '<br>Survenue : exception=jException code='.$e->getCode().' localkey='.$e->getMessage();
                $this->assertTrue( ($res[0]==1) ,$msg2.$msgerr);
            }catch(Exception $e){
                $msgerr = '<br>Survenue : exception=Exception code='.$e->getCode();
                $this->assertTrue( ($res[0]==0) ,$msg.$msgerr);

            }
        }
    }

    function testSimpleEngine() {
       global $gJConfig, $gJCoord;

       $gJCoord->request->url_script_path='/';
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
         'simple_urlengine_https'=>'unittest~urlsig_url8@classic @xmlrpc',
       );
      /* $gJConfig->simple_urlengine_entrypoints = array(
          'index' => "@classic",
          'testnews'=>"unittest~url2@classic",
          'foo/bar'=>"unittest~url4@classic",
          'xmlrpc' => "@xmlrpc",
          'jsonrpc' => "@jsonrpc"
       );*/


      $urlList=array();
      $urlList[]= array('urlsig_url1', array('mois'=>'10',  'annee'=>'2005', 'id'=>'35'));
      $urlList[]= array('urlsig_url2', array('mois'=>'05',  'annee'=>'2004'));
      $urlList[]= array('unittest~urlsig_url3', array('rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fête au village'));
      $urlList[]= array('unittest~urlsig_url4', array('first'=>'premier',  'second'=>'deuxieme'));
      // celle ci n'a pas de définition dans urls.xml *exprés*
      $urlList[]= array('urlsig_url5', array('foo'=>'oof',  'bar'=>'rab'));
      $urlList[]= array('jelix~bar@xmlrpc', array('aaa'=>'bbb'));
      $urlList[]= array('unittest~urlsig_url8', array('rubrique'=>'vetements',  'id_article'=>'98'));

      $trueResult=array(
          "/index.php?mois=10&annee=2005&id=35&module=unittest&action=urlsig_url1",
          "/testnews.php?mois=05&annee=2004&module=unittest&action=urlsig_url2",
          "/testnews.php?rubrique=actualite&id_art=65&article=c%27est+la+f%EAte+au+village&module=unittest&action=urlsig_url3",
          "/foo/bar.php?first=premier&second=deuxieme&module=unittest&action=urlsig_url4",
          "/index.php?foo=oof&bar=rab&module=unittest&action=urlsig_url5",
          "/xmlrpc.php",
          "/index.php?rubrique=vetements&id_article=98&module=unittest&action=urlsig_url8",
       );


      $trueResult[5]='https://'.$_SERVER['HTTP_HOST'].$trueResult[5];
      $trueResult[6]='https://'.$_SERVER['HTTP_HOST'].$trueResult[6];
      $this->_doCompareUrl("simple, multiview = false", $urlList,$trueResult);

      $gJConfig->urlengine['multiview']=true;
      $trueResult=array(
          "/index?mois=10&annee=2005&id=35&module=unittest&action=urlsig_url1",
          "/testnews?mois=05&annee=2004&module=unittest&action=urlsig_url2",
          "/testnews?rubrique=actualite&id_art=65&article=c%27est+la+f%EAte+au+village&module=unittest&action=urlsig_url3",
          "/foo/bar?first=premier&second=deuxieme&module=unittest&action=urlsig_url4",
          "/index?foo=oof&bar=rab&module=unittest&action=urlsig_url5",
          "/xmlrpc",
          "/index?rubrique=vetements&id_article=98&module=unittest&action=urlsig_url8",
       );
      $trueResult[5]='https://'.$_SERVER['HTTP_HOST'].$trueResult[5];
      $trueResult[6]='https://'.$_SERVER['HTTP_HOST'].$trueResult[6];
      $this->_doCompareUrl("simple, multiview = true", $urlList,$trueResult);

    }



    function testSimpleEngineError(){
       global $gJConfig, $gJCoord;

       $gJCoord->request->url_script_path='/';
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
         'simple_urlengine_https'=>'unittest~urlsig_url8@classic @xmlrpc',
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

       $gJCoord->request->url_script_path='/';
       $gJCoord->request->params=array();
       //$gJCoord->request->type=;
       $gJConfig->urlengine = array(
         'engine'=>'significant',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
         'entrypointExtension'=>'.php',
         'notfoundAct'=>'jelix~notfound'
       );

      $gJConfig->_modulesPathList['news']='/';

      jUrl::getEngine(true); // on recharge le nouveau moteur d'url


      $urlList=array();
      $urlList[]= array('urlsig_url1', array('mois'=>'10',  'annee'=>'2005', 'id'=>'35'));
      $urlList[]= array('urlsig_url2', array('mois'=>'05',  'annee'=>'2004'));
      $urlList[]= array('unittest~urlsig_url3', array('rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fête au village'));
      $urlList[]= array('unittest~urlsig_url6', array('rubrique'=>'actualite',  'id_art'=>'65'));
      $urlList[]= array('unittest~urlsig_url4', array('first'=>'premier',  'second'=>'deuxieme'));
      // celle ci n'a pas de définition dans urls.xml *exprés*
      $urlList[]= array('urlsig_url5', array('foo'=>'oof',  'bar'=>'rab'));
      $urlList[]= array('jelix~bar@xmlrpc', array('aaa'=>'bbb'));
      $urlList[]= array('news~bar', array('aaa'=>'bbb'));
      $urlList[]= array('unittest~urlsig_url8', array('rubrique'=>'vetements',  'id_article'=>'98'));

      $trueResult=array(
          "/index.php/test/news/2005/10/35",
          "/testnews.php/2004/05",
          "/index.php/test/cms/actualite/65-c-est-la-fete-au-village",
          "/test/cms2/actualite/65",
          "/foo/bar.php/withhandler/premier/deuxieme",
          "/index.php?foo=oof&bar=rab&module=unittest&action=urlsig_url5",
          "/xmlrpc.php",
          "/news.php?aaa=bbb&action=default_bar",
          "/index.php/shop/vetements/98"

       );

      $trueResult[8]='https://'.$_SERVER['HTTP_HOST'].$trueResult[8];
      $this->_doCompareUrl("significant, multiview = false", $urlList,$trueResult);


      $gJConfig->urlengine['multiview']=true;
      $trueResult=array(
          "/index/test/news/2005/10/35",
          "/testnews/2004/05",
          "/index/test/cms/actualite/65-c-est-la-fete-au-village",
          "/test/cms2/actualite/65",
          "/foo/bar/withhandler/premier/deuxieme",
          "/index?foo=oof&bar=rab&module=unittest&action=urlsig_url5",
          "/xmlrpc",
          "/news?aaa=bbb&action=default_bar",
          "/index/shop/vetements/98"
       );
      $trueResult[8]='https://'.$_SERVER['HTTP_HOST'].$trueResult[8];
      $this->_doCompareUrl("significant, multiview = true", $urlList,$trueResult);

    }


    function testSignificantEngineError(){
       global $gJConfig, $gJCoord;

       $gJCoord->request->url_script_path='/';
       $gJCoord->request->params=array();
       //$gJCoord->request->type=;
       $gJConfig->urlengine = array(
         'engine'=>'significant',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
         'entrypointExtension'=>'.php',
         'notfoundAct'=>'jelix~notfound'
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




}
?>
