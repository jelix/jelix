<?php
/**
* @package     jelix
* @subpackage  urls_engine
* @author      Laurent Jouanneau
* @contributor Thibault PIRONT < nuKs >
* @copyright   2005-2009 Laurent Jouanneau
* @copyright   2007 Thibault PIRONT
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Compiler for significant url engine
* @package  jelix
* @subpackage urls_engine
*/
class jSignificantUrlsCompiler implements jISimpleCompiler{

    public function compile($aSelector){
        global $gJCoord;

        $sourceFile = $aSelector->getPath();
        $cachefile = $aSelector->getCompiledFilePath();

        $xml = simplexml_load_file ( $sourceFile);
        if(!$xml){
           return false;
        }
        /*
        <urls>
         <classicentrypoint name="index" default="true">
            <url pathinfo="/test/:mois/:annee" module="" action="">
                  <param name="mois" escape="true" regexp="\d{4}"/>
                  <param name="annee" escape="false" />
                  <static name="bla" value="cequejeveux" />
            </url>
            <url handler="" module="" action=""  />
         </classicentrypoint>
        </urls>

        The compiler generates two files.

        It generates a php file for each entrypoint. A file contains a $PARSE_URL
        array:

            $PARSE_URL = array($isDefault, $infoparser, $infoparser, ... )

        where:
            $isDefault: true if it is the default entry point. In this case and
            where the url parser doesn't find a corresponding action, it will
            ignore else it will generate an error
            
            $infoparser = array('module','action', 'regexp_pathinfo',
                                'handler selector', array('secondaries','actions'))
            or
            $infoparser = array('module','action','regexp_pathinfo',
               array('year','month'), // list of dynamic value included in the url,
                                      // alphabetical ascendant order
               array(true, false),    // list of boolean which indicates for each
                                      // dynamic value, if it is an escaped value or not
               array('bla'=>'whatIWant' ), // list of static values
               array('secondaries','actions')
            )

        It generates an other file common to all entry point. It contains an
        array which contains informations to create urls

            $CREATE_URL = array(
               'news~show@classic' => // the action selector
                  array(0,'entrypoint', https true/false, 'handler selector')
                  or
                  array(1,'entrypoint', https true/false,
                        array('year','month',), // list of dynamic values included in the url
                        array(true, false..), // list of boolean which indicates for each
                                              // dynamic value, if it is an escaped value or not
                        "/news/%1/%2/", // the url 
                        array('bla'=>'whatIWant' ) // list of static values
                        )
                  or
                  When there are  several urls to the same action, it is an array of this kind of the previous array:
                  array(4, array(1,...), array(1,...)...)

                  or
                  array(2,'entrypoint', https true/false), // for the patterns "@request"
                  array(3,'entrypoint', https true/false), // for the patterns "module~@request"
        */

        $typeparam = array('string'=>'([^\/]+)','char'=>'([^\/])', 'letter'=>'(\w)',
           'number'=>'(\d+)', 'int'=>'(\d+)', 'integer'=>'(\d+)', 'digit'=>'(\d)',
           'date'=>'([0-2]\d{3}\-(?:0[1-9]|1[0-2])\-(?:[0-2][1-9]|3[0-1]))', 
            'year'=>'([0-2]\d{3})', 'month'=>'(0[1-9]|1[0-2])', 'day'=>'([0-2][1-9]|[1-2]0|3[0-1])'
           );
        $createUrlInfos=array();
        $createUrlContent="<?php \n";
        $defaultEntrypoints=array();

        foreach($xml->children() as $name=>$tag){
           if(!preg_match("/^(.*)entrypoint$/", $name,$m)){
               //TODO : error
               continue;
           }
           $requestType= $m[1];
           $entryPoint = (string)$tag['name'];
           $isDefault =  (isset($tag['default']) ? (((string)$tag['default']) == 'true'):false);
           $isHttps = (isset($tag['https']) ? (((string)$tag['https']) == 'true'):false);
           $generatedentrypoint =$entryPoint;
           if(isset($tag['noentrypoint']) && (string)$tag['noentrypoint'] == 'true')
                $generatedentrypoint = '';
           $parseInfos = array($isDefault);

           // si c'est le point d'entrée par défaut pour le type de requet indiqué
           // alors on indique une regle supplementaire que matcherons
           // toutes les urls qui ne correspondent pas aux autres rêgles
           if($isDefault){
             $createUrlInfos['@'.$requestType]=array(2,$entryPoint, $isHttps);
           }

           $createUrlInfosDedicatedModules = array();
           $parseContent = "<?php \n";
           foreach($tag->url as $url){
               $module = (string)$url['module'];
               if(isset($url['https'])){
                   $urlhttps=(((string)$url['https']) == 'true');
               }else{
                   $urlhttps=$isHttps;
               }
               if(isset($url['noentrypoint']) && ((string)$url['noentrypoint']) == 'true'){
                   $urlep='';
               }else{
                   $urlep=$generatedentrypoint;
               }

               // in the case of a non default entry point, if there is just an
               // <url module="" />, so all actions of this module will be assigned
               // to this entry point.
               if(!$isDefault && !isset($url['action']) && !isset($url['handler'])){
                 $parseInfos[] = array($module, '', '/.*/', array(), array(), array(), false );
                 $createUrlInfosDedicatedModules[$module.'~*@'.$requestType] = array(3, $urlep, $urlhttps, true);
                 continue;
               }

               $action = (string)$url['action'];

               if (strpos($action, ':') === false) {
                  $action = 'default:'.$action;
               }

               if(isset($url['actionoverride'])){
                  $actionOverride = preg_split("/[\s,]+/", (string)$url['actionoverride']);
                  foreach ($actionOverride as &$each) {
                     if (strpos($each, ':') === false) {
                        $each = 'default:'.$each;
                     }
                  }
               }else{
                  $actionOverride = false;
               }

               // si il y a un handler indiqué, on sait alors que pour le module et action indiqué
               // il faut passer par cette classe handler pour le parsing et la creation de l'url
               if(isset($url['handler'])){
                  $class = (string)$url['handler'];
                  // il faut absolument un nom de module dans le selecteur, car lors de l'analyse de l'url
                  // dans le request, il n'y a pas de module connu dans le context (normal...)
                  $p= strpos($class,'~');
                  if($p === false)
                    $selclass = $module.'~'.$class;
                  elseif( $p == 0)
                    $selclass = $module.$class;
                  else
                    $selclass = $class;
                  $s= new jSelectorUrlHandler($selclass);
                  if(!isset($url['action'])) {
                    $action = '*';
                  }
                  $regexp = '';
                  $pathinfo = '';
                  if(isset($url['pathinfo'])){
                    $pathinfo = '/'.trim($url['pathinfo'],'/');
                    if ($pathinfo !='/') {
                        $regexp = '!^'.preg_quote($pathinfo,'!').'(/.*)?$!';   
                    }
                  }
                  $createUrlContent.="include_once('".$s->getPath()."');\n";
                  $parseInfos[]=array($module, $action, $regexp, $selclass, $actionOverride );
                  $createUrlInfos[$module.'~'.$action.'@'.$requestType] = array(0,$urlep, $urlhttps, $selclass, $pathinfo);
                  if($actionOverride){
                     foreach($actionOverride as $ao){
                        $createUrlInfos[$module.'~'.$ao.'@'.$requestType] = array(0,$urlep,$urlhttps, $selclass, $pathinfo);
                     }
                  }
                  continue;
               }

               $listparam=array();
               $escapes = array();
               if(isset($url['pathinfo'])){
                  $path = (string)$url['pathinfo'];
                  $regexppath = $path;

                  if(preg_match_all("/\:([a-zA-Z_]+)/",$path,$m, PREG_PATTERN_ORDER)){
                      $listparam=$m[1];

                      foreach($url->param as $var){

                        $nom = (string) $var['name'];
                        $k = array_search($nom, $listparam);
                        if($k === false){
                          // TODO error
                          continue;
                        }

                        if (isset ($var['escape'])){
                            $escapes[$k] = (((string)$var['escape']) == 'true');
                        }else{
                            $escapes[$k] = false;
                        }

                        if(isset($var['type'])){
                           if(isset($typeparam[(string)$var['type']]))
                              $regexp = $typeparam[(string)$var['type']];
                           else
                              $regexp = '([^\/]+)';
                        }else if (isset ($var['regexp'])){
                            $regexp = '('.(string)$var['regexp'].')';
                        }else{
                            $regexp = '([^\/]+)';
                        }

                        $regexppath = str_replace(':'.$nom, $regexp, $regexppath);
                      }

                      foreach($listparam as $k=>$name){
                        if(isset($escapes[$k])){
                           continue;
                        }
                        $escapes[$k] = false;
                        $regexppath = str_replace(':'.$name, '([^\/]+)', $regexppath);
                      }
                  }
               }else{
                 $regexppath='.*';
                 $path='';
               }
               if(isset($url['optionalTrailingSlash']) && $url['optionalTrailingSlash'] == 'true'){
                    if(substr($regexppath, -1) == '/'){
                        $regexppath.='?';
                    }else{
                        $regexppath.='\/?';
                    }
               }

               $liststatics = array();
               foreach($url->static as $var){
                  $liststatics[(string)$var['name']] =(string)$var['value'];
               }
               $parseInfos[]=array($module, $action, '!^'.$regexppath.'$!', $listparam, $escapes, $liststatics, $actionOverride );
               $cuisel = $module.'~'.$action.'@'.$requestType;
               $arr = array(1,$urlep, $urlhttps, $listparam, $escapes,$path, false, $liststatics);
               if(isset($createUrlInfos[$cuisel])){
                    if($createUrlInfos[$cuisel][0] == 4){
                        $createUrlInfos[$cuisel][] = $arr;
                    }else{
                        $createUrlInfos[$cuisel] = array( 4, $createUrlInfos[$cuisel] , $arr);
                    }
               }else{
                   $createUrlInfos[$cuisel] = $arr;
               }
               if($actionOverride){
                  foreach($actionOverride as $ao){
                     $cuisel = $module.'~'.$ao.'@'.$requestType;
                     $arr = array(1,$urlep, $urlhttps, $listparam, $escapes,$path, true, $liststatics);
                     if(isset($createUrlInfos[$cuisel])){
                        if($createUrlInfos[$cuisel][0] == 4){
                            $createUrlInfos[$cuisel][] = $arr;
                        }else{
                            $createUrlInfos[$cuisel] = array( 4, $createUrlInfos[$cuisel] , $arr);
                        }
                     }else{
                        $createUrlInfos[$cuisel] = $arr;
                     }
                  }
               }
           }
           $c = count($createUrlInfosDedicatedModules);
           foreach($createUrlInfosDedicatedModules as $k=>$inf) {
              if ($c > 1)
                $inf[3] = false;
              $createUrlInfos[$k] = $inf;
           }

           $parseContent.='$GLOBALS[\'SIGNIFICANT_PARSEURL\'][\''.rawurlencode($entryPoint).'\'] = '.var_export($parseInfos, true).";\n?>";

           jFile::write(JELIX_APP_TEMP_PATH.'compiled/urlsig/'.$aSelector->file.'.'.rawurlencode($entryPoint).'.entrypoint.php',$parseContent);
        }
        $createUrlContent .='$GLOBALS[\'SIGNIFICANT_CREATEURL\'] ='.var_export($createUrlInfos, true).";\n?>";
        jFile::write(JELIX_APP_TEMP_PATH.'compiled/urlsig/'.$aSelector->file.'.creationinfos.php',$createUrlContent);
        return true;
    }

}

