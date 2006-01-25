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

    public function parse($scriptNamePath, $params, $pathinfo ){
        $url = new jUrl($scriptNamePath, $params, $pathinfo);
        return $url;
    }

    public function create(&$url){
         $url->scriptName = $this->getScript($url->requestType, $url->getParam('module'),$url->getParam('action'));
    }

    protected function getScript($requestType, $module=null, $action=null, $nosuffix=false){
        static $urlspe = null;
        global $gJConfig;

        $script = $gJConfig->urlengine['default_entrypoint'];

        if(count($gJConfig->simple_urlengine_entrypoints)){
           if($urlspe == null){
               $urlspe = array();
               foreach($gJConfig->simple_urlengine_entrypoints as $entrypoint=>$sel){
                 $selectors = preg_split("/[\s,]+/", $sel);
                 foreach($selectors as $sel){
                     $urlspe[$sel]= $entrypoint;
                 }
               }
           }

           $found = false;

           if($action && $action !='' && isset($sep[$module.'~'.$action.'@'.$requestType])){
                $script = $sep[$module.'~'.$action.'@'.$requestType];
                $found = true;
           }

           if($module && $module !='' && !$found &&  isset($sep[$module.'~*@'.$requestType])){
                $script = $sep[$module.'~*@'.$requestType];
                $found = true;
           }

           if(!$found && isset($sep['@'.$requestType])){
               $script = $sep['@'.$requestType];
                $found = true;
           }
        }

        if(!$nosuffix && !$gJConfig->urlengine['multiview_on']){
            $script.=$gJConfig->urlengine['entrypoint_extension'];
        }
        return $script;
    }
}

?>