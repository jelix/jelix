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
*/


/**
* Compilateur pour le moteur d'url significatifs
*/
class jUrlCompilerSignificant implements jISimpleCompiler{

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
            $isDefault : indique si c'est un point d'entrée par défaut, et donc si le parser ne trouve rien, si il ignore ou fait une erreur

            $infoparser = array('module','action','nom handler')
            ou
            $infoparser = array( 'module','action', 'regexp_pathinfo',
               array('annee','mois'), // tableau des valeurs dynamiques, classées par ordre croissant
               array(true, false), // tableau des valeurs escapes
               array('bla'=>'cequejeveux' ) // tableau des valeurs statiques
            )


         génère dans un fichier commun à tous :

            $CREATE_URL = array(
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
        $typeparam = array('string'=>'([^\/]+)','char'=>'([^\/])', 'letter'=>'(\w)',
           'number'=>'(\d+)', 'int'=>'(\d+)', 'integer'=>'(\d+)', 'digit'=>'(\d)',
           'date'=>'([0-2]\d{3}\-(?:0[1-9]|1[0-2])\-(?:[0-2][1-9]|3[0-1]))', 'year'=>'([0-2]\d{3})', 'month'=>'(0[1-9]|1[0-2])', 'day'=>'([0-2][1-9]|3[0-1])'
           );
        $createUrlInfos=array();
        $createUrlContent="<?php \n";
        $defaultEntrypoints=array();
        $file = new jFile();
        foreach($xml->children() as $name=>$tag){
           if(!preg_match("/^(.*)entrypoint$/", $name,$m)){
               //TODO : erreur
               continue;
           }
           $requestType= $m[1];
           $entryPoint = (string)$tag['name'];
           $isDefault =  (isset($tag['default']) ? (((string)$tag['default']) == 'true'):false);
           $parseInfos = array($isDefault);

           // si c'est le point d'entrée par défaut pour le type de requet indiqué
           // alors on indique une regle supplementaire que matcherons
           // toutes les urls qui ne correspondent pas aux autres rêgles
           if($isDefault){
             $createUrlInfos['@'.$requestType]=array(2,$entryPoint);
           }

           $parseContent = "<?php \n";
           foreach($tag->url as $url){
               $module = (string)$url['module'];

               // dans le cas d'un point d'entrée qui n'est pas celui par défaut pour le type de requete indiqué
               // si il y a juste un module indiqué alors on sait que toutes les actions
               // concernant ce module passeront par ce point d'entrée.
               if(!$isDefault && !isset($url['action']) && !isset($url['handler'])){
                 $parseInfos[]=array($module, '', '/.*/', array(), array(), array(), false );
                 $createUrlInfos[$module.'~*@'.$requestType] = array(3,$entryPoint);
                 continue;
               }

               $action = (string)$url['action'];
               if(isset($url['actionoverride'])){
                  $actionOverride = preg_split("/[\s,]+/", (string)$url['actionoverride']);
               }else{
                  $actionOverride = false;
               }

               // si il y a un handler indiqué, on sait alors que pour le module et action indiqué
               // il faut passer par cette classe handler pour le parsing et la creation de l'url
               if(isset($url['handler'])){
                  $class = (string)$url['handler'];
                  $s= new jSelectorUrlHandler($module.'~'.$class);
                  $createUrlContent.="include_once('".$s->getPath()."');\n";
                  $parseInfos[]=array($module, $action, $class, $actionOverride );
                  $createUrlInfos[$module.'~'.$action.'@'.$requestType] = array(0,$entryPoint, $class);
                  if($actionOverride){
                     foreach($actionOverride as $ao){
                        $createUrlInfos[$module.'~'.$ao.'@'.$requestType] = array(0,$entryPoint, $class);
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
                        }

                        if (isset ($var['regexp'])){
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
               $liststatics = array();
               foreach($url->static as $var){
                  $liststatics[(string)$var['name']] =(string)$var['value'];
               }
               $parseInfos[]=array($module, $action, '!^'.$regexppath.'$!', $listparam, $escapes, $liststatics, $actionOverride );
               $createUrlInfos[$module.'~'.$action.'@'.$requestType] = array(1,$entryPoint, $listparam, $escapes,$path);
               if($actionOverride){
                  foreach($actionOverride as $ao){
                     $createUrlInfos[$module.'~'.$ao.'@'.$requestType] = array(1,$entryPoint, $listparam, $escapes,$path);
                  }
               }
           }

           $parseContent.='$GLOBALS[\'SIGNIFICANT_PARSEURL\'][\''.rawurlencode($entryPoint).'\'] = '.var_export($parseInfos, true).";\n?>";

           $file->write(JELIX_APP_TEMP_PATH.'compiled/urlsig/'.rawurlencode($entryPoint).'.entrypoint.php',$parseContent);
        }
        $createUrlContent .='$GLOBALS[\'SIGNIFICANT_CREATEURL\'] ='.var_export($createUrlInfos, true).";\n?>";
        $file->write(JELIX_APP_TEMP_PATH.'compiled/urlsig/creationinfos.php',$createUrlContent);
        return true;
    }

}

?>
