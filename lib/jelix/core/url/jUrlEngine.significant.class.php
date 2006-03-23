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

class jSelectorUrlHandler extends jSelectorClass {
    public $type = 'urlhandler';
    protected $_suffix = '.urlhandler.php';
}

interface jIUrlSignificantHandler {
   public function parse($url);
   public function create($url);

}

class jUrlEngineSignificant implements jIUrlEngine {

   /**
   * liste des données pour la création des urls (voir structure plus loin)
   */
   protected $dataCreateUrl = null;

   /**
   * liste des données pour l'analyse des urls
   */
   protected $dataParseUrl =  null;

   /**
   *
   */
   public function parse($scriptNamePath, $pathinfo, $params ){
      global $gJConfig;

      $url = new jUrl($scriptNamePath, $params, $pathinfo);

      if ($gJConfig->urlengine['enableParser']){
         $sel = new jSelectorUrlCfgSig('urls.xml');
         jIncluder::inc($sel);
         $basepath = $GLOBALS['gJConfig']->urlengine['basePath'];
         if(strpos($scriptNamePath, $basepath) === 0){
            $snp = substr($scriptNamePath,strlen($basepath));
         }else{
            $snp = $scriptNamePath;
         }
         $pos = strrpos($snp,$gJConfig->urlengine['entrypointExtension']);
         if($pos !== false){
            $snp = substr($snp,0,$pos);
         }

         $file=JELIX_APP_TEMP_PATH.'compiled/urlsig/'.rawurlencode($snp).'.entrypoint.php';
         if(file_exists($file)){
            require_once($file);
            $this->dataCreateUrl = & $GLOBALS['SIGNIFICANT_CREATEURL'];
            $this->dataParseUrl = & $GLOBALS['SIGNIFICANT_PARSEURL'][rawurlencode($snp)];
            if(!$this->_parse($url)){
               // $url peut avoir été modifié par _parse, on remet l'ancien
               $url= new jUrl($scriptNamePath, $params, $pathinfo);
            }
         }
      }
      return $url;
   }

   protected function _parse($url){
      global $gJConfig;
      /*$script = $url->scriptName;
      if(strpos($script, $gJConfig->urlengine['entrypointExtension']) !== false){
         $script=substr($script,0,- (strlen($gJConfig->urlengine['entrypointExtension'])));
      }*/
      
      if(substr($url->pathInfo,-1) == '/' && $url->pathInfo != '/'){
            $pathinfo = substr($url->pathInfo,0,-1);
      }else{
            $pathinfo = $url->pathInfo;
      }

      $foundurl = false;
      $isDefault = false;
      $urlcl = clone $url;
      foreach($this->dataParseUrl as $k=>$infoparsing){
         if($k==0){
            $isDefault = $infoparsing;
            continue;
         }

         if(count($infoparsing) < 5){
            // on a un tableau du style
            // array( 0=> 'module', 1=>'action', 2=>'handler')
            $s = new jSelectorUrlHandler($infoparsing[2]);
            $c ='URLS'.$s->resource;
            $handler =new $c();

            $urlcl->params['module']=$infoparsing[0];
            if($infoparsing[3] && isset($url->params['action'])&& in_array($url->params['action'], $infoparsing[3])){
               $urlcl->params['action']=$url->params['action'];
            }else{
               $urlcl->params['action']=$infoparsing[1];
            }
            if($handler->parse($urlcl)){
               $foundurl=true;
               $url->pathInfo = $urlcl->pathInfo;
               $url->params = $urlcl->params;
               $url->scriptName = $urlcl->scriptName;
               break;
            }
         }else{
            /* on a un tableau du style
            array( 0=>'module', 1=>'action', 2=>'regexp_pathinfo',
               3=>array('annee','mois'), // tableau des valeurs dynamiques, classées par ordre croissant
               4=>array(true, false), // tableau des valeurs escapes
               5=>array('bla'=>'cequejeveux' ) // tableau des valeurs statiques
               6=>false ou array('act','act'...) // autres actions autorisées
            */
            if(preg_match ($infoparsing[2], $pathinfo, $matches)){

               if($infoparsing[0] !='')
                  $url->params['module']=$infoparsing[0];

               if( ! ($infoparsing[6] && isset($url->params['action'])&& in_array($url->params['action'], $infoparsing[6]))){
                  if($infoparsing[1] !='')
                     $url->params['action']=$infoparsing[1];
               }

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
         $url->params = $url->getAction($gJConfig->urlengine['notfoundAct']);
         $foundurl = true;
      }

      return ($isDefault?true:$foundurl);
   }


   /**
   * @param jUrl    $url    url à modifier
   */
   public function create( $url){

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

      $module = $url->getParam('module', jContext::get());
      $action = $url->getParam('action');

      $id = $module.'~'.$action.'@'.$url->requestType;
      $urlinfo = null;
      if (isset ($this->dataCreateUrl [$id])){
         $urlinfo = &$this->dataCreateUrl[$id];
         $url->delParam('module');
         $url->delParam('action');
      }else{
         $id = $module.'~*@'.$url->requestType;
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
         if(!$GLOBALS['gJConfig']->urlengine['multiview']){
            $url->scriptName.=$GLOBALS['gJConfig']->urlengine['entrypointExtension'];
         }
         // pour certains types de requete, les paramètres ne sont pas dans l'url
         // donc on les supprime
         // c'est un peu crade de faire ça en dur ici, mais ce serait lourdingue
         // de charger la classe request pour savoir si on peut supprimer ou pas
         if(in_array($url->requestType ,array('xmlrpc','jsonrpc','soap'))){
            $url->clearParam();
            return;
         }

         if($urlinfo[0]==0){
            $s = new jSelectorUrlHandler($urlinfo[2]);
            $c ='URLS'.$s->resource;
            $handler =new $c();
            $handler->create($url);
         }elseif($urlinfo[0]==1){
            $result = $urlinfo[4];
            foreach ($urlinfo[2] as $k=>$param){
               if($urlinfo[3][$k]){
                  $result=str_replace(':'.$param, jUrl::escape($url->getParam($param,''),true), $result);
               }else{
                  $result=str_replace(':'.$param, $url->getParam($param,''), $result);
               }
               $url->delParam($param);
            }

            $url->pathInfo = $result;
         }elseif($urlinfo[0]==3){
               $url->delParam('module');
         }
      }
   }
}
?>