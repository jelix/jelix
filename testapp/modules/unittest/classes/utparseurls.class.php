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

class UTParseUrls extends UnitTestCase {
    protected $oldUrlScriptPath;
    protected $oldParams;
    protected $oldRequestType;
    protected $oldUrlengineConf;
    protected $simple_urlengine_entrypoints;


    function setUp() {
      global $gJCoord, $gJConfig;

      $this->oldUrlScriptPath = $gJCoord->request->url_script_path;
      $this->oldParams = $gJCoord->request->url->params;
      $this->oldRequestType = $gJCoord->request->type;
      $this->oldUrlengineConf = $gJConfig->urlengine;
      $this->simple_urlengine_entrypoints = $gJConfig->simple_urlengine_entrypoints;
    }

    function tearDown() {
      global $gJCoord, $gJConfig;

      $gJCoord->request->url_script_path=$this->oldUrlScriptPath;
      $gJCoord->request->url->params=$this->oldParams;
      $gJCoord->request->type=$this->oldRequestType;
      $gJConfig->urlengine = $this->oldUrlengineConf;
      $gJConfig->simple_urlengine_entrypoints = $this->simple_urlengine_entrypoints;
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

      jUrl::getEngine(true); // on recharge le nouveau moteur d'url


      $resultList=array();
      $resultList[]= array('module'=>'unittest', 'action'=>'urlsig_url1', 'mois'=>'10',  'annee'=>'2005', 'id'=>'35');
      $resultList[]= array('module'=>'unittest', 'action'=>'urlsig_url8', 'mois'=>'10',  'annee'=>'2005', 'id'=>'35');
      $resultList[]= array('module'=>'unittest', 'action'=>'urlsig_url2', 'mois'=>'05',  'annee'=>'2004', "mystatic"=>"valeur statique");
      $resultList[]= array('module'=>'unittest', 'action'=>'urlsig_url3', 'rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c est la fete au village');
      $resultList[]= array('module'=>'unittest', 'action'=>'urlsig_url4', 'first'=>'premier',  'second'=>'deuxieme');
      // celle ci n'a pas de définition dans urls.xml *exprés*
      $resultList[]= array('module'=>'unittest', 'action'=>'urlsig_url5', 'foo'=>'oof',  'bar'=>'rab');
      $resultList[]= array();
      $resultList[]= array('module'=>'news', 'action'=>'main_bar', 'aaa'=>'bbb');

      $request=array(
          array("index.php","/test/news/2005/10/35",array()),
          array("index.php","/test/news/2005/10/35",array("action"=>"urlsig_url8")),
          array("testnews.php","/2004/05",array()),
          array("index.php","/test/cms/actualite/65-c-est-la-fete-au-village",array()),
          array("foo/bar.php","/withhandler/premier/deuxieme",array()),
          array("index.php",'',array('module'=>'unittest', 'action'=>'urlsig_url5', 'foo'=>'oof',  'bar'=>'rab')),
          array("xmlrpc.php","",array()),
          array("news.php","",array('aaa'=>'bbb','action'=>'main_bar'))
       );

      $this->sendMessage("significant, multiview = false");
      foreach($request as $k=>$urldata){
         $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
         $p = $url->params;
         ksort($p);
         ksort($resultList[$k]);

         $this->assertTrue( ($p == $resultList[$k]), 'crée:'.var_export($p,true).' attendu:'.var_export($resultList[$k],true));
      }


      $this->sendMessage("significant, multiview = true");
      $gJConfig->urlengine['multiview']=true;
      $request=array(
          array("index","/test/news/2005/10/35",array()),
          array("index","/test/news/2005/10/35",array("action"=>"urlsig_url8")),
          array("testnews","/2004/05",array()),
          array("index","/test/cms/actualite/65-c-est-la-fete-au-village",array()),
          array("foo/bar","/withhandler/premier/deuxieme",array()),
          array("index",'',array('module'=>'unittest', 'action'=>'urlsig_url5', 'foo'=>'oof',  'bar'=>'rab')),
          array("xmlrpc","",array()),
          array("news","",array('aaa'=>'bbb','action'=>'main_bar'))
       );
      foreach($request as $k=>$urldata){
         $url = jUrl::parse ($urldata[0], $urldata[1], $urldata[2]);
         $p = $url->params;
         ksort($p);
         ksort($resultList[$k]);

         $this->assertTrue( ($p == $resultList[$k]), 'crée:'.var_export($p,true).' attendu:'.var_export($resultList[$k],true));
      }

    }
}

?>