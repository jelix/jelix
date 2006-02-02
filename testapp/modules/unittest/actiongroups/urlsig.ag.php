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


class AGUrlsig extends jActionGroup {

   function getDummyUrl() {
      $tpl = & new CopixTpl ();
      $tpl->assign ('TITLE_PAGE','test urls significatifs' );
      $savedpathinfo = $gJConfig->url_path_info;
      $main='<p>Page avec url de test..</p><p><a href="'.CopixUrl::get('default').'">Retour</a></p>';
      $tpl->assign('MAIN', $main);
      return new CopixActionReturn (COPIX_AR_DISPLAY, $tpl);
   }


   function getTestCreate() {
      global $gJCoord, $gJConfig;

      $rep = $this->getResponse('default');
      $rep->title = 'test urls significatifs';

      $main='<h2>Tests sur la création des urls</h2>';

      $savedconfig = clone $gJConfig;
      //$savedvars = $gJCoord->request->params;

      $urlList=array();
      $urlList[]= array('url1', array('mois'=>'10',  'annee'=>'2005', 'id'=>'35'));
      $urlList[]= array('unittest|urltest|url2', array('mois'=>'05',  'annee'=>'2004'));
      $urlList[]= array('url3', array('rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fête au village'));
      $urlList[]= array('unittest|urlfoo|url4', array('first'=>'premier',  'second'=>'deuxieme'));

      // celle ci n'a pas de définition dans module.xml *exprés*
      $urlList[]= array('url5', array('foo'=>'oof',  'bar'=>'rab'));

      $gJConfig->url_path_info='';
      $gJConfig->url_enable_parser = false;
      $gJConfig->url_default_entrypoint='index';
      $gJConfig->url_specific_entrypoints = array(
        'unittest|urltest'=>'testnews',
        'unittest|urlfoo'=>'foo/bar'
      );

      // test avec le moteur simple

      $main.='<h3>moteur "simple"</h3>';
      $gJConfig->url_engine='simple';
      $gJConfig->url_multiview_on = false;
      $trueResult=array(
          "index.php?mois=10&annee=2005&id=35&module=unittest&action=url1",
          "testnews.php?mois=05&annee=2004&module=unittest&action=url2",
          "index.php?rubrique=actualite&id_art=65&article=c%27est+la+f%EAte+au+village&module=unittest&desc=default&action=url3",
          "foo/bar.php?first=premier&second=deuxieme&module=unittest&action=url4",
          "index.php?foo=oof&bar=rab&module=unittest&action=url5"
       );
      $main.=$this->_testcreate($urlList, $trueResult);


      // test avec le moteur significanturl
      $main.='<h3>moteur "significanturl"</h3>';

      $gJConfig->url_engine='significant';
      CopixUrl::getEngine(true);

      $main.='<h4>multiview = on</h4>';
      $gJConfig->url_multiview_on = true;
      $trueResult=array(
         "index/test/news/2005/10/35",
          "testnews/2004/05",
          "index/test/cms/actualite/65-c%27est-la-f%EAte-au-village",
          "foo/bar/withhandler/premier/deuxieme",
          "index?foo=oof&bar=rab&module=unittest&action=url5"
      );
      $main.=$this->_testcreate($urlList, $trueResult);

      $main.='<h4>multiview = off</h4>';
      $gJConfig->url_multiview_on = false;


      $trueResult=array(
          "index.php/test/news/2005/10/35",
          "testnews.php/2004/05",
          "index.php/test/cms/actualite/65-c%27est-la-f%EAte-au-village",
          "foo/bar.php/withhandler/premier/deuxieme",
          "index.php?foo=oof&bar=rab&module=unittest&desc=default&action=url5"
       );
      $main.=$this->_testcreate($urlList, $trueResult);

      // moteur significanturl2

      $main.='<h3>moteur "significanturl2" (module en prefix)</h3>';
      $gJConfig->url_engine='significant2';
      CopixUrl::getEngine(true);

      $main.='<h4>multiview = on</h4>';
      $gJConfig->url_multiview_on = true;
      $trueResult=array(
         "index/unittest/test/news/2005/10/35",
          "testnews/unittest/2004/05",
          "index/unittest/test/cms/actualite/65-c%27est-la-f%EAte-au-village",
          "foo/bar/unittest/withhandler/premier/deuxieme",
          "index?foo=oof&bar=rab&module=unittest&desc=default&action=url5"
      );
      $main.=$this->_testcreate($urlList, $trueResult);

      $main.='<h4>multiview = off</h4>';
      $gJConfig->url_multiview_on = false;


      $trueResult=array(
          "index.php/unittest/test/news/2005/10/35",
          "testnews.php/unittest/2004/05",
          "index.php/unittest/test/cms/actualite/65-c%27est-la-f%EAte-au-village",
          "foo/bar.php/unittest/withhandler/premier/deuxieme",
          "index.php?foo=oof&bar=rab&module=unittest&desc=default&action=url5"
       );
      $main.=$this->_testcreate($urlList, $trueResult);

      $main.='<h3>moteur "classicsignificant"</h3>';
      $gJConfig->url_engine='classicsignificant';
      CopixUrl::getEngine(true);

      $main.='<h4>multiview = on</h4>';
      $gJConfig->url_multiview_on = true;
      $trueResult=array(
         "index/unittest/default/url1?mois=10&annee=2005&id=35",
          "testnews/unittest/urltest/url2?mois=05&annee=2004",
          "index/unittest/default/url3?rubrique=actualite&id_art=65&article=c%27est+la+f%EAte+au+village",
          "foo/bar/unittest/withhandler/premier/deuxieme",
          "index/unittest/default/url5?foo=oof&bar=rab"
      );
      $main.=$this->_testcreate($urlList, $trueResult);

      $main.='<h4>multiview = off</h4>';
      $gJConfig->url_multiview_on = false;


      $trueResult=array(
         "index.php/unittest/default/url1?mois=10&annee=2005&id=35",
          "testnews.php/unittest/urltest/url2?mois=05&annee=2004",
          "index.php/unittest/default/url3?rubrique=actualite&id_art=65&article=c%27est+la+f%EAte+au+village",
          "foo/bar.php/unittest/withhandler/premier/deuxieme",
          "index.php/unittest/default/url5?foo=oof&bar=rab"
       );
      $main.=$this->_testcreate($urlList, $trueResult);



      $gJCoord->vars = $savedvars;
      $gJConfig=$savedconfig;

      $main.='<hr /><p><a href="?module=unittest&amp;action=urlsigparse">Tester l\'analyse d\'url</a></p>';

      $tpl->assign('MAIN', $main);
      return new CopixActionReturn (COPIX_AR_DISPLAY, $tpl);
   }

   function getTestParsing() {
      $tpl = & new CopixTpl ();
      $tpl->assign ('TITLE_PAGE','test urls significatifs' );

      $main='<h2>Tests sur l\'analyse d\'urls</h2>';

      if(CopixConfig::isPHP5()){
         $savedconfig = $gJConfig.clone();
      }else{
         $savedconfig = $gJConfig;
      }
      $savedvars = $gJCoord->vars;


      $result=array();
      $result[]= array('module'=>'unittest', 'desc'=>'default', 'action'=>'url1', 'mois'=>'10',  'annee'=>'2005', 'id'=>'35');
      $result[]= array('module'=>'unittest', 'desc'=>'urltest', 'action'=>'url2', 'mois'=>'05',  'annee'=>'2004', 'mystatic' => 'valeur statique');
      $result[]= array('module'=>'unittest', 'desc'=>'default', 'action'=>'url3', 'rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fête au village');
      $result[]= array('module'=>'unittest', 'desc'=>'urlfoo', 'action'=>'url4', 'first'=>'premier',  'second'=>'deuxieme');

      $gJConfig->url_path_info='';
      $gJConfig->url_enable_parser = true;
      $gJConfig->url_default_entrypoint='index';
      $gJConfig->url_specific_entrypoints = array(
        'unittest|urltest'=>'testnews',
        'unittest|urlfoo'=>'foo/bar'
      );


      $main.='<h3>moteur "simple"</h3>';
      $gJConfig->url_engine='simple';
      CopixUrl::getEngine(true);
      $gJConfig->url_multiview_on = false;

      $main.='<h4>multiview off</h4>';
      $request=array(
          array('index.php',"",array('module'=>'unittest', 'desc'=>'default', 'action'=>'url1', 'mois'=>'10',  'annee'=>'2005', 'id'=>'35')),
          array('testnews.php',"",array('module'=>'unittest', 'desc'=>'urltest', 'action'=>'url2', 'mois'=>'05',  'annee'=>'2004', 'mystatic' => 'valeur statique')),
          array('index.php',"",array('module'=>'unittest', 'desc'=>'default', 'action'=>'url3', 'rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fête au village')),
          array('foo/bar.php',"",array('module'=>'unittest', 'desc'=>'urlfoo', 'action'=>'url4', 'first'=>'premier',  'second'=>'deuxieme'))
      );
      $main.=$this->_testparse($request, $result);

      $main.='<h4>multiview on</h4>';
      $gJConfig->url_multiview_on = true;
      $request=array(
          array('index',"",array('module'=>'unittest', 'desc'=>'default', 'action'=>'url1', 'mois'=>'10',  'annee'=>'2005', 'id'=>'35')),
          array('testnews',"",array('module'=>'unittest', 'desc'=>'urltest', 'action'=>'url2', 'mois'=>'05',  'annee'=>'2004', 'mystatic' => 'valeur statique')),
          array('index',"",array('module'=>'unittest', 'desc'=>'default', 'action'=>'url3', 'rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fête au village')),
          array('foo/bar',"",array('module'=>'unittest', 'desc'=>'urlfoo', 'action'=>'url4', 'first'=>'premier',  'second'=>'deuxieme'))
      );
      $main.=$this->_testparse($request, $result);




      $main.='<h3>moteur "significant"</h3>';
      $gJConfig->url_engine='significant';
      CopixUrl::getEngine(true);
      $gJConfig->url_multiview_on = false;

      $main.='<h4>multiview off</h4>';
      $request=array(
          array('index.php',"/test/news/2005/10/35",array()),
          array('testnews.php',"/2004/05",array()),
          array('index.php',"/test/cms/actualite/65-c'est-la-fête-au-village",array()),
          array('foo/bar.php',"/withhandler/premier/deuxieme",array())
      );
      $main.=$this->_testparse($request, $result);

      $main.='<h4>multiview on</h4>';
      $gJConfig->url_multiview_on = true;
      $request=array(
          array('index',"/test/news/2005/10/35",array()),
          array('testnews',"/2004/05",array()),
          array('index',"/test/cms/actualite/65-c'est-la-fête-au-village",array()),
          array('foo/bar',"/withhandler/premier/deuxieme",array())
      );
      $main.=$this->_testparse($request, $result);


      $main.='<h3>moteur "significant2"</h3>';
      $gJConfig->url_engine='significant2';
      CopixUrl::getEngine(true);
      $gJConfig->url_multiview_on = false;
      $main.='<h4>multiview off</h4>';
      $request=array(
          array('index.php', "/unittest/test/news/2005/10/35",array()),
          array('testnews.php',"/unittest/2004/05",array()),
          array('index.php',"/unittest/test/cms/actualite/65-c'est-la-fête-au-village",array()),
          array('foo/bar.php',"/unittest/withhandler/premier/deuxieme",array())
       );
      $main.=$this->_testparse($request, $result);

      $main.='<h4>multiview on</h4>';
      $gJConfig->url_multiview_on = true;
      $request=array(
          array('index',"/unittest/test/news/2005/10/35",array()),
          array('testnews',"/unittest/2004/05",array()),
          array('index',"/unittest/test/cms/actualite/65-c'est-la-fête-au-village",array()),
          array('foo/bar',"/unittest/withhandler/premier/deuxieme",array())
       );
       $main.=$this->_testparse($request, $result);

      $main.='<h3>moteur "classicsignificant"</h3>';
      $gJConfig->url_engine='classicsignificant';
      CopixUrl::getEngine(true);
      $gJConfig->url_multiview_on = false;
      $main.='<h4>multiview off</h4>';
      $request=array(
          array('index.php',"/unittest/default/url1",array('mois'=>'10',  'annee'=>'2005', 'id'=>'35')),
          array('testnews.php',"/unittest/urltest/url2",array('mois'=>'05',  'annee'=>'2004', 'mystatic' => 'valeur statique')),
          array('index.php',"/unittest/default/url3",array('rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fête au village')),
          array('foo/bar.php',"/unittest/withhandler/premier/deuxieme",array())
       );
      $main.=$this->_testparse($request, $result);

      $main.='<h4>multiview on</h4>';
      $gJConfig->url_multiview_on = true;
      $request=array(
          array('index',"/unittest/default/url1",array('mois'=>'10',  'annee'=>'2005', 'id'=>'35')),
          array('testnews',"/unittest/urltest/url2",array('mois'=>'05',  'annee'=>'2004', 'mystatic' => 'valeur statique')),
          array('index',"/unittest/default/url3",array('rubrique'=>'actualite',  'id_art'=>'65', 'article'=>'c\'est la fête au village')),
          array('foo/bar',"/unittest/withhandler/premier/deuxieme",array())
       );
       $main.=$this->_testparse($request, $result);



      $gJCoord->vars = $savedvars;
      $gJConfig=$savedconfig;

      $main.='<hr /><p><a href="?module=unittest&amp;action=urlsigcreate">Tester la création d\'url</a></p>';

      $tpl->assign('MAIN', $main);

      return new CopixActionReturn (COPIX_AR_DISPLAY, $tpl);
   }


   function _testcreate($urls, $trueResults){
        $out='<ol style="overflow:auto;min-height:'.(count($urls)*3).'em;">';
        foreach($urls as $k=>$url){
            $result = CopixUrl::get($url[0],$url[1]);
            if($result == $trueResults[$k]){
                $out.='<li>OK '.htmlspecialchars($result).'</li>';
            }else{
                $out.='<li><span style="color:red;font-weight:bold;">BAD</span>
                     <ul>
                         <li>Résultat : '.htmlspecialchars($result).'</li>
                         <li>Résultat attendu : '.htmlspecialchars($trueResults[$k]).'</li></ul></li>';
            }
        }
        $out.='</ol>';
        return $out;
   }

   function _testparse($urls, $trueResults){
        $out='<ol style="overflow:auto;min-height:'.(count($urls)*3).'em;">';

        foreach($urls as $k=>$info){
            $res = CopixUrl::parse($info[0], $info[2], $info[1] );

            ksort($res->params);
            ksort($trueResults[$k]);
            $result = var_export($res->params, true);
            $shouldbe = var_export($trueResults[$k], true);

            if($result == $shouldbe){
                $out.='<li>OK '.htmlspecialchars($result).'</li>';
            }else{
                $out.='<li><span style="color:red;font-weight:bold;">BAD</span>
                     <ul>
                         <li>Résultat : '.htmlspecialchars($result).'</li>
                         <li>Résultat attendu : '.htmlspecialchars($shouldbe).'</li></ul></li>';
            }
        }
        $out.='</ol>';
        return $out;
   }

}

?>
