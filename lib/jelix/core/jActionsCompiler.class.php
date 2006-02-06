<?php
/**
* @package    jelix
* @subpackage core
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jActionsCompiler implements jISimpleCompiler {

    public function compile($selector){
        global $gJCoord;
        $sel = clone $selector;

        $sourceFile = $selector->getPath();
        $cachefile = $selector->getCompiledFilePath();

        jContext::push($sel->module);

        // effacement des anciens fichiers compilés pour effacer les actions qui n'existent plus
        $cachedir= $selector->getCacheDir();
        if ($handle = opendir($cachedir)) {
            $f =$sel->module.'~';
            while (false !== ($file = readdir($handle))) {
               if(strpos($file,$f) === 0)
                  unlink($cachedir.$file);
            }
            closedir($handle);
        }

        // compilation du fichier xml
        $xml = simplexml_load_file ( $sourceFile);
        if(!$xml){
           jContext::pop();
           return false;
        }

        $foundAction=false;
        foreach($xml->request as $req){
            if(isset($req['type'])){
                $requesttype=$req['type'];
            }else{
                trigger_error(jLocale::get('jelix~errors.ac.xml.request.type.attr.missing',array($sourceFile)), E_USER_ERROR);
                jContext::pop();
                return false;
            }
            if(isset($req['defaultag'])){
                $defaultag=$req['defaultag'];
            }else{
               $defaultag = '';
            }
            $commonPluginParams=$this->_readPluginParams($req);
            $defaultResponse = '';
            $commonResponses= $this->_readResponses($req, $defaultResponse);

            foreach($req->action as $action){

               $pluginParams = array_merge($commonPluginParams, $this->_readPluginParams($action));
               $defrep='';
               $responses = array_merge($commonResponses, $this->_readResponses($action, $defrep));
               if($defrep == ''){
                 if($defaultResponse ==''){
                   reset($responses);
                   $defrep=key($responses);
                 }else{
                   $defrep = $defaultResponse;
                 }
               }
               
               if(isset($action['ag'])){
                  $ag=(string)$action['ag'];
               }else{
                  $ag = $defaultag;
               }
               
               $actionsel = new jSelectorAg($ag);
               if(!$actionsel->isValid()){
                  trigger_error(jLocale::get('jelix~errors.ac.xml.ag.selector.invalid',array($ag,$action['name'], $sourceFile) ),E_USER_ERROR);
                  jContext::pop();
                  return false;
               }
               $path = $actionsel->getPath();
               if(isset($action['method']))
                 $method = $action['method'];
               else
                 $method = $action['name'];                 
               $content ="<?php\n".'$GLOBALS[\'gJCoord\']->action = new jActionDesc(\''.$action['name'].'\',\''.$path.'\',\'AG'.$actionsel->resource.'\',\''.$method.'\',\''.$defrep.'\');'."\n";
               if(count($pluginParams)){
                   $content .= '$GLOBALS[\'gJCoord\']->action->pluginParams = '.var_export($pluginParams,true).";\n";
               }
               $content .= '$GLOBALS[\'gJCoord\']->action->responses = '.var_export($responses,true).";\n";
               $content.='?>';

               $sel->resource = $action['name'];
               $sel->request = $requesttype;
               $cache = $sel->getCompiledFilePath();
               if($cache == $cachefile)
                   $foundAction = true;
               $file = new jFile();
               $file->write($cache, $content);
            }
        }
        jContext::pop();
        return $foundAction;
    }
/*
<actions>
  <actiongroup requesttype="">
      <!-- parametres communs à toutes les actions du group -->
      <pluginparam name="" value="" />
      <pluginparam name="" value="" />
      <response name="" type="" parameter="value" />
      <response name="" type="" parameter="value" />

      <action name="" ag="" method="" >
          <pluginparam name="" value="" />
          <pluginparam name="" value="" />
          <!-- default indique la reponse à instancier par défaut. Si aucune réponse par défaut : pas instanciée-->
          <response default="true" name="" type="html,redirect,xul.." parameter="value" />
          <response name="" type="" parameter="value" />
      </action>
  </actiongroup>
</actions>
*/

    private function _readPluginParams($tag){
        $pps=array();
        if(isset($tag->pluginparam)){
            foreach($tag->pluginparam as $pp){
                $pps[$pp['name']]=$pp['value'];
            }
        }
        return $pps;
    }

    private function _readResponses($tag, &$defaultResponse){
        $reps=array();
        if(isset($tag->response)){
            foreach($tag->response as $rep){

                $attr= array();
                $na = array('name','type','default');
                foreach($rep->attributes() as $a => $b) {
                    if(!in_array($a,$na)){
                        $attr[$a]=(string)$b;
                    }
                }

                $name = (string)$rep['name'];
                $type = (string)$rep['type'];
                $default = (isset($rep['default'])? (string)$rep['default']:'false');

                if($default == 'true')
                    $defaultResponse = $name;
                $reps[$name]=array($type, $attr);
            }
            /*if(!isset($reps['default'])){
                $reps['default'] = $reps[$defaultresponse];
            }*/
        }
        return $reps;
    }
}
?>