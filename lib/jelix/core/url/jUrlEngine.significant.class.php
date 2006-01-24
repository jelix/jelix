<?php
/**
* @package    copix
* @subpackage plugins
* @version    $Id: CopixUrlEngine.significant.class.php,v 1.1.2.1 2005/08/17 22:12:50 laurentj Exp $
* @author    Laurent Jouanneau
* @copyright 2001-2005 CopixTeam
* @link      http://copix.aston.fr
* @link      http://copix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/


class CopixUrlEngineSignificant extends CopixUrlEngine {

    /**
     * liste des données pour la création des urls (voir structure plus loin)
     */
    var $dataCreateUrl = array();

    /**
    * liste des données pour l'analyse des urls
    */
    var $dataParseUrl = array();

    function CopixUrlEngineSignificant(){

        $this->coordination = & $GLOBALS['COPIX']['COORD'];
        $this->config = & $GLOBALS['COPIX']['CONFIG'];
        $cplCfg = CopixInclude::SIGNIFICANTURL();

        // on change le nom de fichier de compil car on n'a pas moyen de savoir si
        // la config copix a été changée (disons que CopixInclude n'est pour le moment
        // pas prévu pour vérifier à la fois le fichier de conf, et à la fois les  fichiers
        // de chaque module
        // @todo améliorer CopixInclude sur ce point ?
        $cplCfg[2]='significant.'.md5($this->config->url_default_entrypoint.serialize($this->config->url_specific_entrypoints)).'.php';
        // charger ici les données sur les urls
        $rv = CopixInclude::includeCacheFile($cplCfg);

        $this->dataParseUrl  = & $GLOBALS['SIGNIFICANT_PARSEURL'];
        $this->dataCreateUrl  = & $GLOBALS['SIGNIFICANT_CREATEURL'];
    }

    /**
     *
     */
    function parse($scriptNamePath, $params, $pathinfo ){
        $url = new CopixUrl($scriptNamePath, $params, $pathinfo);

        if ($this->config->url_enable_parser){
            $urloriginal = new CopixUrl($scriptNamePath, $params, $pathinfo);
            if(!$this->_parse(&$url)){
                $url=$urloriginal;
            }
        }
        return $url;
    }

    function _parse(&$url){

            $script = $url->scriptName;

            if(strpos($script, $this->config->url_entrypoint_extension) !== false){
              $script=substr($script,0,- (strlen($this->config->url_entrypoint_extension)));
            }
            if(substr($url->pathInfo,-1) == '/' && $url->pathInfo != '/'){
                $pathinfo = substr($url->pathInfo,0,-1);
            }else{
                 $pathinfo = $url->pathInfo;
            }
            $foundurl = false;

            if(isset($this->dataParseUrl[$script])){
              foreach($this->dataParseUrl[$script] as $mod=>$moduleinfoparsing){
                foreach($moduleinfoparsing as $infoparsing){

                  $url->params['module']=$infoparsing[0];
                  $url->params['desc']=$infoparsing[1];
                  $url->params['action']=$infoparsing[2];

                  if($infoparsing[3]=== false){
                    // on a un tableau du style
                    // array( 0=> 'module', 1=>'desc', 2=>'action', 3=>false,4=>'handler')
                    $cl = $infoparsing[4];
                    $handler = new $cl();
                    if($handler->parse($url)){
                        $foundurl=true;
                        break;
                    }
                  }else{
                      // on a un tableau du style
                      /*
                      array( 0=> 'module', 1=>'desc', 2=>'action', 3=>'pathinfo',
                             4=>array('annee','mois'), // tableau des valeurs dynamiques, classées par ordre croissant
                             5=>array(true, false),
                             6=>array('bla'=>'cequejeveux' ) // tableau des valeurs statiques
                      )
                      */
                      if(preg_match ($infoparsing[3], $pathinfo, $matches)){

                          // on fusionne les parametres statiques
                          if ($infoparsing[6]){
                              $url->params = array_merge ($url->params, $infoparsing[6]);
                          }

                          if(count($matches)){
                            foreach($infoparsing[4] as $k=>$name){
                                if(isset($matches[$k+1])){
                                    if($infoparsing[5][$k]){
                                        $url->params[$name] = CopixUrl::unescape($matches[$k+1]);
                                    }else{
                                        $url->params[$name] = $matches[$k+1];
                                    }
                                 }
                             }
                          }
                          $foundurl = true;
                          break;
                      }
                  }
               }
               if($foundurl)
                  break;
            }
        }

        if(!$foundurl){
            $url->pathInfo='';
            $url->params = $url->getDest($this->config->url_404notfound_dest);
            $foundurl = true;
        }

        return $foundurl;
    }


    /**
    * @param CopixUrl    $url    url à modifier
    */
    function create( &$url){

        /*
        a) recupere module|desc|action -> obtient les infos pour la creation de l'url
        b) récupère un à un les parametres indiqués dans params à partir de CopixUrl
        c) remplace la valeur récupérée dans le result et supprime le paramètre de l'url
        d) remplace scriptname de CopixUrl par le resultat
        */

        $module = $url->getParam ('module');
        if($module === null)
            $module = CopixContext::get();
        if($module == '')
           $module='_';

        $desc= $url->getParam('desc',COPIX_DEFAULT_VALUE_DESC);
        $action = $url->getParam('action',COPIX_DEFAULT_VALUE_ACTION);

        $id = $module.'|'.$desc.'|'.$action;

        $urlinfo = null;
        if (isset ($this->dataCreateUrl [$id])){

            $url->delParam('module');
            $url->delParam('desc');
            $url->delParam('action');
            $urlinfo = &$this->dataCreateUrl[$id];

            if($urlinfo[0]===false){
                $cl = $urlinfo[1];
                $handler = new $cl();
                $handler->create($url);
            }else{
                $dturl = & $urlinfo[0];
                $result = $urlinfo[2];
                foreach ($dturl as $k=>$param){
                    if($urlinfo[1][$k]){
                        $result=str_replace('%'.($k+1), CopixUrl::escape($url->getParam($param,''),true), $result);
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