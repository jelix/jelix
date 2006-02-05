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
*/

/**
 * moteur des urls simple responsable du parsing et de la création d'url
 */
class jUrlEngineSimple implements jIUrlEngine {

    public function parse($scriptNamePath, $pathinfo, $params ){
        $url = new jUrl($scriptNamePath, $params, $pathinfo);
        return $url;
    }

    public function create($url){
         $url->scriptName = $this->getScript($url->requestType, $url->getParam('module'),$url->getParam('action'));

         // pour certains types de requete, les paramètres ne sont pas dans l'url
         // donc on les supprime
         // c'est un peu crade de faire ça en dur ici, mais ce serait lourdingue
         // de charger la classe request pour savoir si on peut supprimer ou pas
         if(in_array($url->requestType ,array('xmlrpc','jsonrpc','soap')))
            $url->clearParam();

    }

    protected function getScript($requestType, $module=null, $action=null){
        static $urlspe = null;
        global $gJConfig;

        $script = $gJConfig->urlengine['defaultEntrypoint'];

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
           $found = false;
           if($action && $action !='' && isset($urlspe[$module.'~'.$action.'@'.$requestType])){
                $script = $urlspe[$module.'~'.$action.'@'.$requestType];
                $found = true;
           }
           if($module && $module !='' && !$found &&  isset($urlspe[$module.'~*@'.$requestType])){
                $script = $urlspe[$module.'~*@'.$requestType];
                $found = true;
           }
           if(!$found && isset($urlspe['@'.$requestType])){
               $script = $urlspe['@'.$requestType];
                $found = true;
           }
        }
        if(!$gJConfig->urlengine['multiview']){
            $script.=$gJConfig->urlengine['entrypointExtension'];
        }
        return $script;
    }
}

?>