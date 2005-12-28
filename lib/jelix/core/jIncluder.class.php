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
*
* Quelques lignes de codes sont sous Copyright 2001-2005 CopixTeam (licence LGPL)
* et sont issues de la classe CopixInclude de Copix 2.3dev20050901.
* Auteur initial : Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/


// pour les compilations avec 1 fichier source
interface jISimpleCompiler {
    public function compile($aSelector);
}

//  pour les compilations avec plusieurs fichiers sources
interface jIMultiFileCompiler {
    public function compileItem($sourceFile, $module);
    public function endCompile($cachefile);
}


class jIncluder {
    protected static $_includedFiles = array();


    public static function EVENT(){
        return  array('jEventCompiler',
                    'events/jEventCompiler.class.php',
                    'events.xml',
                    'events.php'
        );
    }

    public static function URL(){
        return  array('jUrlCompiler',
                    'core/url/jUrlCompiler.class.php',
                    'actions.xml',
                    'urls.php'
        );
    }


    /**
     * @param    jISelector   $aSelectorId    selecteur du fichier à compiler
     * @return   array    contenant l'objet selecteur correspondant à $aSelectorId, et 2 booleans indiquant si il a fallu compiler et si la compilation s'est bien passée
    */
    public static function inc($aSelector=''){
       global $gJConfig,$gJCoord;

        if(is_string($aSelector)){
            $aSelector = jSelectorFactory::create($aSelector);
        }

        if(!$aSelector->isValid()){
            return array('selector'=>$aSelector, 'compilation'=>false, 'compileok'=>false);
        }

        $cachefile = $aSelector->getCompiledFilePath();

        if($cachefile == '' || isset(jIncluder::$_includedFiles[$cachefile])){
            return array('selector'=>$aSelector, 'compilation'=>false, 'compileok'=>true);
        }

        $mustCompile = $gJConfig->compilation['force'] || !file_exists($cachefile);
        $sourcefile = $aSelector->getPath();

        if($sourcefile == ''){
           trigger_error(jLocale::get('jelix~errors.includer.source.missing',array( $aSelector->toString(true))), E_USER_ERROR);
           return array('selector'=>$aSelector, 'compilation'=>false, 'compileok'=>false);
        }

        if($gJConfig->compilation['check_cache_filetime'] && !$mustCompile){
            if( filemtime($sourcefile) > filemtime($cachefile)){
                $mustCompile = true;
            }
        }

        $compileok=true;
        if($mustCompile){
            $compiler = $aSelector->getCompiler();

            if($compiler && $compileok=$compiler->compile($aSelector)){
                require_once($cachefile);
                jIncluder::$_includedFiles[$cachefile]=true;
            }
        }else{
            require_once($cachefile);
            jIncluder::$_includedFiles[$cachefile]=true;
        }

        return array('selector'=>$aSelector, 'compilation'=>$mustCompile, 'compileok'=>$compileok);
    }

        /**
         * @param    array    aType
            = array(
            'nom classe compilateur',
            'chemin compilateur relatif à lib/jelix/',
            'foo.xml', // nom du fichier à compiler
            'foo.php',  //fichier cache
            );
        * @return   array    contenant 2 booleans indiquant si il a fallu compiler et si la compilation s'est bien passée
        */
    public static function incAll($aType){

        global $gJConfig,$gJCoord;
        $cachefile = JELIX_APP_TEMP_PATH.'compiled/'.$aType[3];
        if(isset(jIncluder::$_includedFiles[$cachefile])){
            return array('compilation'=>false, 'compileok'=>true);
        }

        $mustCompile = $gJConfig->compilation['force'] || !file_exists($cachefile);
        $checkCompile = $gJConfig->compilation['check_cache_filetime'];

        if(!$mustCompile && $checkCompile){
            $compiledate = filemtime($cachefile);
            foreach($gJCoord->modulesPathList as $module=>$path){
                $sourcefile = $path.$aType[2];
                if (is_readable ($sourcefile)){
                    if( filemtime($sourcefile) > $compiledate){
                        $mustCompile = true;
                        break;
                    }
                }
            }
        }

        $compileok=true;
        if($mustCompile){
            require_once(JELIX_LIB_PATH.$aType[1]);
            $compiler = new $aType[0];

            foreach($gJCoord->modulesPathList as $module=>$path){
                $compileok=$compiler->compileItem($path.$aType[2], $module);
                if(!$compileok) break;
            }

            if($compileok){
                $compiler->endCompile($cachefile);
                require_once($cachefile);
                jIncluder::$_includedFiles[$cachefile]=true;
            }
        }else{
            require_once($cachefile);
            jIncluder::$_includedFiles[$cachefile]=true;
        }
        return array('compilation'=>$mustCompile, 'compileok'=>$compileok);
    }
}

?>