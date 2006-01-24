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

class jSelectorUrlHandler extends jSelectorClass {
    public $type = 'urlhandler';
    protected $_dirname = 'classes/';
    protected $_suffix = '.urlhandler.php';

}

/**
* Compilateur pour le moteur d'url significatifs
*/
class jUrlCompiler implements jISimpleCompiler{

    public function compile($aSelector){
        global $gJCoord;

        $sourceFile = $selector->getPath();
        $cachefile = $selector->getCompiledFilePath();


        // lecture du fichier xml
        $xml = simplexml_load_file ( $sourceFile);
        if(!$xml){
           return false;
        }
        /*
        <urls>
         <classicentrypoint name="index" default="true">
            <url path="/test/:mois/:annee" module="" action="">
                  <var name="mois" escape="true" regexp="\d{4}"/>
                  <var name="annee" escape="false" />
                  <staticvar name="bla" value="cequejeveux" />
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
                  array('entrypoint', false,'handler')
                  ou
                  array('entrypoint',
                        array('annee','mois','jour','id','titre'), // liste des paramètres de l'url à prendre en compte
                        array(true, false..), // valeur des escapes
                        "/news/%1/%2/%3/%4-%5", // forme de l'url
                        )

        */
        $createUrlInfos=array();
        foreach($xml->children() as $name=>$tag){
           if(!preg_match("/^(.*)EntryPoint$/", $name,$m)){
               //TODO : erreur
               continue;
           }
           $requestType= $m[1];
           $entryPoint = (string)$tag['name'];
           $isDefault =  (isset($tag['default']) ? (((string)$tag['default']) == 'true'):false);
           $parseInfos = array($isDefault);
           $includes = array();
           foreach($tag->url as $url){
               $module = (string)$url['module'];
               $action = (string)$url['action'];
               if(isset($url['handler'])){
                  $class = (string)$url['handler'];
                  $parseInfos[]=array($module, $action, $class );
                  $s= new jSelectorUrlHandler($module.'~'.$action)
                  $includes[]= $s->getPath();
                  $createUrlInfos[$module.'~'.$action.'@'.$requestType] = array($entryPoint, false, $class);
                  continue;
               }
               $path = (string)$url['path'];
               $listparam=array();
               if(preg_match_all("/\:([a-zA-Z]+)/",$path,$m, PREG_PATTERN_ORDER)){
                  $listparam=$m[1];

                  foreach($url->var as $var){
                     $nom = (string) $var['name'];
                     if (isset ($var['escape'])){
                        $escape = (((string)$var['escape']) == 'true');
                     }else{
                        $escape = false;
                     }
                     if (isset ($var['regexp'])){
                        $regexp = '('.(string)$var['regexp'].')';
                     }else{
                        $regexp = '([^\/]+)';
                     }

                     $path = str_replace(':'.$name, $regexp, $path);
                  }
               }
               foreach($url->staticvar as $var){

               }
               $parseInfos[]=array($module, $action, $class );
               $createUrlInfos[$module.'~'.$action.'@'.$requestType] = array($entryPoint, $listparam, $escapes,$path);
           }


        }

    }

}

?>