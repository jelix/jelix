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
      $this->oldParams = $gJCoord->request->url->params;
      $this->oldRequestType = $gJCoord->request->type;
      $this->oldUrlengineConf = $gJConfig->urlengine;
      $this->simple_urlengine_entrypoints = $gJConfig->simple_urlengine_entrypoints;
      $this->oldModule = $gJConfig->_modulesPathList;
    }

    function tearDown() {
      global $gJCoord, $gJConfig;

      $gJCoord->request->url_script_path=$this->oldUrlScriptPath;
      $gJCoord->request->url->params=$this->oldParams;
      $gJCoord->request->type=$this->oldRequestType;
      $gJConfig->urlengine = $this->oldUrlengineConf;
      $gJConfig->simple_urlengine_entrypoints = $this->simple_urlengine_entrypoints;
      $gJConfig->_modulesPathList=$this->oldModule ;
    }


    function testSimpleEngine() {
       global $gJConfig, $gJCoord;

       $gJCoord->request->url_script_path='/';
       $gJCoord->request->url->params=array();
       //$gJCoord->request->type=;
       $gJConfig->urlengine = array(
         'engine'=>'simple',
         'enableParser'=>true,
         'multiview'=>false,
         'basePath'=>'/',
         'defaultEntrypoint'=>'index',
         'entrypointExtension'=>'.php',
         'notfoundAct'=>'jelix~notfound'
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
      $urlList[]= array('foo~bar@xmlrpc', array('aaa'=>'bbb'));
      $urlList[]= array('jelix~bar@xmlrpc', array('aaa'=>'bbb'));

      $trueResult=array(
          "/index.php?mois=10&annee=2005&id=35&module=unittest&action=urlsig_url1",
          "/testnews.php?mois=05&annee=2004&module=unittest&action=urlsig_url2",
          "/testnews.php?rubrique=actualite&id_art=65&article=c%27est+la+f%EAte+au+village&module=unittest&action=urlsig_url3",
          "/foo/bar.php?first=premier&second=deuxieme&module=unittest&action=urlsig_url4",
          "/index.php?foo=oof&bar=rab&module=unittest&action=urlsig_url5",
          false,
          "/xmlrpc.php",
       );

      $this->sendMessage("simple, multiview = false");
      foreach($urlList as $k=>$urldata){
          try{
            $url = jUrl::getStr ($urldata[0], $urldata[1]);
         }catch(jExceptionSelector $e){
            $url = false;
         }
         $this->assertTrue( ($url == $trueResult[$k]), 'url attendue='.$trueResult[$k].'   url créée='.$url );
      }


      $this->sendMessage("simple, multiview = true");
      $gJConfig->urlengine['multiview']=true;
      $trueResult=array(
          "/index?mois=10&annee=2005&id=35&module=unittest&action=urlsig_url1",
          "/testnews?mois=05&annee=2004&module=unittest&action=urlsig_url2",
          "/testnews?rubrique=actualite&id_art=65&article=c%27est+la+f%EAte+au+village&module=unittest&action=urlsig_url3",
          "/foo/bar?first=premier&second=deuxieme&module=unittest&action=urlsig_url4",
          "/index?foo=oof&bar=rab&module=unittest&action=urlsig_url5",
          false,
          "/xmlrpc",
       );

      foreach($urlList as $k=>$urldata){
          try{
            $url = jUrl::getStr ($urldata[0], $urldata[1]);
         }catch(jExceptionSelector $e){
            $url = false;
         }
         $this->assertTrue( ($url == $trueResult[$k]), 'url attendue='.$trueResult[$k].'   url créée='.$url );
      }

      //$this->sendMessage("évenement simple");
      //$this->assertTrue($temoin == $response, 'Premier evènement');
    }


    function testSignificantEngine() {
       global $gJConfig, $gJCoord;

       $gJCoord->request->url_script_path='/';
       $gJCoord->request->url->params=array();
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
      $urlList[]= array('unittest~urlsig_url4', array('first'=>'premier',  'second'=>'deuxieme'));
      // celle ci n'a pas de définition dans urls.xml *exprés*
      $urlList[]= array('urlsig_url5', array('foo'=>'oof',  'bar'=>'rab'));
      $urlList[]= array('foo~bar@xmlrpc', array('aaa'=>'bbb'));
      $urlList[]= array('jelix~bar@xmlrpc', array('aaa'=>'bbb'));
      $urlList[]= array('news~bar', array('aaa'=>'bbb'));

      $trueResult=array(
          "/index.php/test/news/2005/10/35",
          "/testnews.php/2004/05",
          "/index.php/test/cms/actualite/65-c-est-la-fete-au-village",
          "/foo/bar.php/withhandler/premier/deuxieme",
          "/index.php?foo=oof&bar=rab&module=unittest&action=urlsig_url5",
          false,
          "/xmlrpc.php",
          "/news.php?aaa=bbb&action=default_bar"
       );

      $this->sendMessage("significant, multiview = false");
      foreach($urlList as $k=>$urldata){
         try{
            $url = jUrl::getStr ($urldata[0], $urldata[1]);
         }catch(jExceptionSelector $e){
            $url = false;
         }
         $this->assertTrue( ($url == $trueResult[$k]), 'url attendue='.$trueResult[$k].'   url créée='.$url );
      }

      $this->sendMessage("significant, multiview = true");
      $gJConfig->urlengine['multiview']=true;
      $trueResult=array(
          "/index/test/news/2005/10/35",
          "/testnews/2004/05",
          "/index/test/cms/actualite/65-c-est-la-fete-au-village",
          "/foo/bar/withhandler/premier/deuxieme",
          "/index?foo=oof&bar=rab&module=unittest&action=urlsig_url5",
          false,
          "/xmlrpc",
          "/news?aaa=bbb&action=default_bar"
       );

      foreach($urlList as $k=>$urldata){
         try{
            $url = jUrl::getStr ($urldata[0], $urldata[1]);
         }catch(jExceptionSelector $e){
            $url = false;
         }
         $this->assertTrue( ($url == $trueResult[$k]), 'url attendue='.$trueResult[$k].'   url créée='.$url );
      }

    }

}
?>
