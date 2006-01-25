<?php
/**
* @package     jelix
* @subpackage  core
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*
* Some parts of this file are took from an experimental version of Copix Framework v2.3dev20050901,
* CopixUrlEngine.significant.class.php,
* copyrighted by CopixTeam and released under GNU Lesser General Public Licence
* author : Laurent Jouanneau
* http://www.copix.org
*/


class jSelectorUrlCfgSig extends jSelectorCfg {
   public $type = 'urlcfgsig';

   public function getCompiler(){
      require_once(JELIX_LIB_CORE_PATH.'url/jUrlCompiler.significant.class.php');
      $o = new jUrlCompilerSignificant();
      return $o;
   }
   public function getCompiledFilePath (){ return JELIX_APP_TEMP_PATH.'compiled/urlsig/creationinfos.php';}
}

class jUrlEngineSignificant implements jIUrlEngine {

   /**
   * liste des données pour la création des urls (voir structure plus loin)
   */
   var $dataCreateUrl = null;

   /**
   * liste des données pour l'analyse des urls
   */
   var $dataParseUrl =  null;

   /**
   *
   */
   public function parse($scriptNamePath, $params, $pathinfo ){

      $url = new jUrl($scriptNamePath, $params, $pathinfo);

      if ($GLOBALS['gJConfig']->urlengine['enable_parser']){
         $sel = new jSelectorUrlCfgSig('urls.xml');
         jIncluder::inc($sel);
         require_once(JELIX_APP_TEMP_PATH.'compiled/urlsig/'.rawurlencode($scriptNamePath).'.entrypoint.php');
         $this->dataCreateUrl = & $GLOBALS['SIGNIFICANT_CREATEURL'];
         $this->dataParseUrl = & $GLOBALS['SIGNIFICANT_PARSEURL'];

         if(!$this->_parse($url)){
            $url= new jUrl($scriptNamePath, $params, $pathinfo);
         }
      }
      return $url;
   }

   protected function _parse($url){
      global $gJConfig;
      /*$script = $url->scriptName;
      if(strpos($script, $gJConfig->urlengine['entrypoint_extension']) !== false){
         $script=substr($script,0,- (strlen($gJConfig->urlengine['entrypoint_extension'])));
      }*/
      if(substr($url->pathInfo,-1) == '/' && $url->pathInfo != '/'){
            $pathinfo = substr($url->pathInfo,0,-1);
      }else{
            $pathinfo = $url->pathInfo;
      }

      $foundurl = false;
      $isDefault = false;
      foreach($this->dataParseUrl as $k=>$infoparsing){
         if($k==0){
            $isDefault = $infoparsing;
            continue;
         }

         $url->params['module']=$infoparsing[0];
         $url->params['action']=$infoparsing[1];

         if(count($infoparsing < 4)){
            // on a un tableau du style
            // array( 0=> 'module', 1=>'action', 2=>'handler')
            $cl = $infoparsing[2];
            $handler = new $cl();
            if($handler->parse($url)){
               $foundurl=true;
               break;
            }
         }else{
            /* on a un tableau du style
            array( 0=>'module', 1=>'action', 2=>'regexp_pathinfo',
               3=>array('annee','mois'), // tableau des valeurs dynamiques, classées par ordre croissant
               4=>array(true, false), // tableau des valeurs escapes
               5=>array('bla'=>'cequejeveux' ) // tableau des valeurs statiques
            */
            if(preg_match ($infoparsing[2], $pathinfo, $matches)){

               // on fusionne les parametres statiques
               if ($infoparsing[5]){
                  $url->params = array_merge ($url->params, $infoparsing[5]);
               }

               if(count($matches)){
                  array_shift($matches);
                  foreach($infoparsing[3] as $k=>$name){
                     if(isset($matches[$k])){
                        if($infoparsing[4][$k]){
                              $url->params[$name] = jUrl::unescape($matches[$k]);
                        }else{
                              $url->params[$name] = $matches[$k];
                        }
                     }
                  }
               }
               $foundurl = true;
               break;
            }
         }
      }
      if(!$foundurl && !$isDefault){
         $url->pathInfo='';
         $url->params = $url->getAction($gJConfig->urlengine['notfound_act']);
         $foundurl = true;
      }

      return ($isDefault?true:$foundurl);
   }


   /**
   * @param jUrl    $url    url à modifier
   */
   public function create( &$url){
      if($this->dataCreateUrl == null){
         $sel = new jSelectorUrlCfgSig('urls.xml');
         jIncluder::inc($sel);
         $this->dataCreateUrl = & $GLOBALS['SIGNIFICANT_CREATEURL'];
      }
      /*
      a) recupere module~action@request -> obtient les infos pour la creation de l'url
      b) récupère un à un les parametres indiqués dans params à partir de jUrl
      c) remplace la valeur récupérée dans le result et supprime le paramètre de l'url
      d) remplace scriptname de jUrl par le resultat
      */

      $module = $url->getParam ('module', jContext::get());
      $action = $url->getParam('action');

      $id = $module.'~'.$action.'@'.$url->requestType;
      $urlinfo = null;
      if (isset ($this->dataCreateUrl [$id])){
         $urlinfo = &$this->dataCreateUrl[$id];
         $url->delParam('module');
         $url->delParam('action');
      }else{
         $id = $module.'~@'.$url->requestType;
         if (isset ($this->dataCreateUrl [$id])){
            $urlinfo = &$this->dataCreateUrl[$id];
            $url->delParam('module');
         }else{
            $id = '@'.$url->requestType;
            if (isset ($this->dataCreateUrl [$id])){
               $urlinfo = &$this->dataCreateUrl[$id];
            }
         }
      }

      if($urlinfo != null){
         /*array(
         'news~show@classic' =>
            array(0,'entrypoint','handler')
            ou
            array(1,'entrypoint',
                  array('annee','mois','jour','id','titre'), // liste des paramètres de l'url à prendre en compte
                  array(true, false..), // valeur des escapes
                  "/news/%1/%2/%3/%4-%5", // forme de l'url
                  )
            ou
            array(2,'entrypoint'); pour les clés du type "@request" ou "module~@request"
         */

         $url->scriptName = $urlinfo[1];

         if($urlinfo[0]==0){
            $cl = $urlinfo[1];
            $handler = new $cl();
            $handler->create($url);
         }elseif($urlinfo[0]==1){
            $result = $urlinfo[4];
            foreach ($urlinfo[2] as $k=>$param){
               if($urlinfo[3][$k]){
                  $result=str_replace('%'.($k+1), jUrl::escape($url->getParam($param,''),true), $result);
               }else{
                  $result=str_replace('%'.($k+1), $url->getParam($param,''), $result);
               }
               $url->delParam($param);
            }

            $url->pathInfo = $result;
         }
      }
   }
}
?>