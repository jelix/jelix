<?php
/**
* @package     jelix
* @subpackage  urls_engine
* @author      Laurent Jouanneau
* @contributor
* @copyright   2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * simple url engine
 * generated url are "dirty" jelix url, with full of parameter in the query (module, action etc..)
 * @package  jelix
 * @subpackage urls_engine
 * @see jIUrlEngine
 */
class simpleUrlEngine implements jIUrlEngine {

    /**
    * Parse some url components
    * @param string $scriptNamePath    /path/index.php
    * @param string $pathinfo          the path info part of the url (part between script name and query)
    * @param array  $params            url parameters (query part e.g. $_REQUEST)
    * @return jUrlAction
    */
    public function parse($scriptNamePath, $pathinfo, $params ){
        // in fact, parse is called only inside jRequest object,
        // so we don't have to "guess" the request type
        // but this is not very good indeed, i know
        return new jUrlAction($params);
    }


    /**
    * Create a jurl object with the given action datas
    * @param jUrlAction $url  information about the action
    * @return jUrl the url correspondant to the action
    */
    public function create($urlact){

         $scriptName = $this->getScript($urlact->requestType, $urlact->getParam('module'),$urlact->getParam('action'));
         $url = new jUrl($scriptName, $urlact->params, '');
         // pour certains types de requete, les paramètres ne sont pas dans l'url
         // donc on les supprime
         // c'est un peu crade de faire ça en dur ici, mais ce serait lourdingue
         // de charger la classe request pour savoir si on peut supprimer ou pas
         if(in_array($urlact->requestType ,array('xmlrpc','jsonrpc','soap')))
            $url->clearParam();

         return $url;
    }

    /**
     * read the configuration and gets the script path corresponding to the given parameters
     * @param string $requestType
     * @param string $module
     * @param string  $action
     */
    protected function getScript($requestType, $module=null, $action=null){
        static $urlspe = null;
        static $urlhttps = null;
        global $gJConfig;

        $script = $gJConfig->urlengine['defaultEntrypoint'];

        if($urlhttps == null){
            $urlhttps=array();
            $selectors = preg_split("/[\s,]+/", $gJConfig->urlengine['simple_urlengine_https']);
            foreach($selectors as $sel2){
                $urlhttps[$sel2]= true;
            }
        }

        $usehttps= false;
        if($action && isset($urlhttps[$module.'~'.$action.'@'.$requestType])){
            $usehttps = true;
        }elseif($module &&  isset($urlhttps[$module.'~*@'.$requestType])){
            $usehttps = true;
        }elseif(isset($urlhttps['@'.$requestType])){
            $usehttps = true;
        }

        if(count($gJConfig->simple_urlengine_entrypoints)){
           if($urlspe == null){
               $urlspe = array();
               foreach($gJConfig->simple_urlengine_entrypoints as $entrypoint=>$sel){
                 $selectors = preg_split("/[\s,]+/", $sel);
                 foreach($selectors as $sel2){
                     $urlspe[$sel2]= $entrypoint;
                 }
               }
           }
           if($action && isset($urlspe[$s1 = $module.'~'.$action.'@'.$requestType])){
                $script = $urlspe[$s1];
           }elseif($module &&  isset($urlspe[$s2 = $module.'~*@'.$requestType])){
                $script = $urlspe[$s2];
           }elseif( isset($urlspe[$s3 = '@'.$requestType])){
               $script = $urlspe[$s3];
           }
        }
        if(!$gJConfig->urlengine['multiview']){
            $script.=$gJConfig->urlengine['entrypointExtension'];
        }

        if($usehttps){
            return 'https://'.$_SERVER['HTTP_HOST'].$gJConfig->urlengine['basePath'].$script;
        }else{
            return $gJConfig->urlengine['basePath'].$script;
        }
    }
}

?>