<?php
/**
* @package     jelix
* @subpackage  urls_engine
* @author      Laurent Jouanneau
* @contributor Thibault PIRONT < nuKs >
* @copyright   2005-2008 Laurent Jouanneau
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


        // lecture du fichier xml
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

         génère dans un fichier propre à chaque entrypoint :

            $PARSE_URL = array($isDefault , $infoparser,$infoparser... )

            où
            $isDefault : indique si c'est un point d'entrée par défaut, et donc si le parser ne trouve rien,
                            si il ignore ou fait une erreur

            $infoparser = array('module','action','selecteur handler')
            ou
            $infoparser = array( 'module','action', 'regexp_pathinfo',
               array('annee','mois'), // tableau des valeurs dynamiques, classées par ordre croissant
               array(true, false), // tableau des valeurs escapes
               array('bla'=>'cequejeveux' ) // tableau des valeurs statiques
            )


         génère dans un fichier commun à tous :

            $CREATE_URL = array(
               'news~show@classic' =>
                  array(0,'entrypoint', https true/false, 'selecteur handler')
                  ou
                  array(1,'entrypoint', https true/false,
                        array('annee','mois','jour','id','titre'), // liste des paramètres de l'url à prendre en compte
                        array(true, false..), // valeur des escapes
                        "/news/%1/%2/%3/%4-%5", // forme de l'url
                        array('bla'=>'cequejeveux' ) // tableau des valeurs statiques, pour comparer 
                                                     quand il y a plusieurs urls vers la même action
                        )
                   quand il y a plusieurs urls vers la même action, il y a plutôt un tableau contenant
                    plusieurs tableaux du type précédent
                    array( 4, array(1,...), array(1,...)...)

                  ou
                  array(2,'entrypoint', https true/false); pour les clés du type "@request"
                  array(3,'entrypoint', https true/false);  pour les clés du type  "module~@request"

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
               //TODO : erreur
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
               // dans le cas d'un point d'entrée qui n'est pas celui par défaut pour le type de requete indiqué
               // si il y a juste un module indiqué alors on sait que toutes les actions
               // concernant ce module passeront par ce point d'entrée.
               if(!$isDefault && !isset($url['action']) && !isset($url['handler'])){
                 $parseInfos[]=array($module, '', '/.*/', array(), array(), array(), false );
                 $createUrlInfos[$module.'~*@'.$requestType] = array(3,$urlep, $urlhttps);
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
                  $createUrlContent.="include_once('".$s->getPath()."');\n";
                  $parseInfos[]=array($module, $action, $selclass, $actionOverride );
                  $createUrlInfos[$module.'~'.$action.'@'.$requestType] = array(0,$urlep, $urlhttps, $selclass);
                  if($actionOverride){
                     foreach($actionOverride as $ao){
                        $createUrlInfos[$module.'~'.$ao.'@'.$requestType] = array(0,$urlep,$urlhttps, $selclass);
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

           $parseContent.='$GLOBALS[\'SIGNIFICANT_PARSEURL\'][\''.rawurlencode($entryPoint).'\'] = '.var_export($parseInfos, true).";\n?>";

           jFile::write(JELIX_APP_TEMP_PATH.'compiled/urlsig/'.$aSelector->file.'.'.rawurlencode($entryPoint).'.entrypoint.php',$parseContent);
        }
        $createUrlContent .='$GLOBALS[\'SIGNIFICANT_CREATEURL\'] ='.var_export($createUrlInfos, true).";\n?>";
        jFile::write(JELIX_APP_TEMP_PATH.'compiled/urlsig/'.$aSelector->file.'.creationinfos.php',$createUrlContent);
        return true;
    }

}

?>