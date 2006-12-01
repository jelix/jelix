<?php
/**
* @package     jBuildTools
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
/*
options :
    -i fichier.ini
    -D VAR=VALEUR
*/

require_once(dirname(__FILE__).'/jManifest.class.php');
require_once(dirname(__FILE__).'/jCmdUtils.class.php');



class Env {

    private function __construct(){ }



    static public function set($name,$value, $onlyIfNotExists=false){
        if($onlyIfNotExists && isset($GLOBALS[$name]) && $GLOBALS[$name] !='') return;
        if(!self::verifyName($name)) return;
        $GLOBALS[$name]=$value;
    }

    static public function addArray($arr){
        foreach($arr as $k=>$v){
            if(self::verifyName($k))
                $GLOBALS[$k]=$v;
        }
    }

    static public function addIni($file){
        if($arr = parse_ini_file($file,false)){
            foreach($arr as $k=>$v){
                if(self::verifyName($k))
                    $GLOBALS[$k]=$v;
            }
        }else{
            die("can't read ini file\n");
        }
    }

    static public function init($varnames){
        foreach($varnames as $name){
            if(!isset($GLOBALS[$name]) && self::verifyName($name))
                $GLOBALS[$name] = '';
        }
    }

    static public function initBool($varnames){
        foreach($varnames as $name){
            if(!self::verifyName($name)) continue;

            if(isset($GLOBALS[$name]) && $GLOBALS[$name] != '0'){
                if($GLOBALS[$name] != '') $GLOBALS[$name] = '1';
            }else{
                $GLOBALS[$name] = '';
            }
        }
    }

    static public function setFromFile($name,$file, $onlyIfNotExists=false){
        if($onlyIfNotExists && isset($GLOBALS[$name]) && $GLOBALS[$name] !='') return;
        if(!self::verifyName($name)) return;
        $GLOBALS[$name]=file_get_contents($file);
    }

    static public function verifyName($name, $verbose=true){
        static $var= array('_ENV','_GET','_POST','_SERVER','GLOBALS','_FILES', '_COOKIE',
        'HTTP_ENV_VARS','HTTP_POST_VARS','HTTP_GET_VARS','HTTP_COOKIE_VARS',
        'HTTP_SERVER_VARS','HTTP_POST_FILES','_REQUEST');

        if(in_array($name,$var )){
            if($verbose) echo "warning: invalid variable name ($name)\n";
            return false;
        }else{
            return true;
        }

    }

}


class Subversion {
    static public function revision($path='.'){
        $path=jBuildUtils::normalizeDir($path).'.svn/entries';
        $rev=-1;
        if(file_exists($path)){
            /* FIXME : namespace invalide dans les fichiers entries, on ne peut
              donc pas les lire à partir de simplxml ou dom

            $svninfo = simplexml_load_file ( $path);
            if(isset($svninfo->entry[0]))
                $rev=$svninfo->entry[0]['revision'];
            */
            $rev=`svn info | grep -E "vision" -m 1`;
            if(preg_match("/vision\s*:\s*(\d+)/",$rev, $m))
                $rev=$m[1];
        }
        return $rev;
    }
}


function init(){
    $sws = array('-v'=>false, '-D'=>2);
    $params = array('ini'=>true);

    list($switches, $parameters) = jCmdUtils::getOptionsAndParams($_SERVER['argv'], $sws, $params);

    if(isset($parameters['ini'])){
        ENV::addIni($parameters['ini']);
    }

    if(isset($switches['-D'])){
        foreach($switches['-D'] as $var){
            if(preg_match("/^(\w+)=(.*)$/",$var,$m)){
                ENV::set($m[1],$m[2]);
            }else
                throw new Exception('bad syntax for -D option  :'.$var."\n");
        }
    }
    if(isset($switches['-v'])){
        ENV::set('VERBOSE_MODE',true);
    }
}


function debugVars(){
    foreach ($GLOBALS as $n=>$v){
        if(ENV::verifyName($n,false)){
            echo $n, " = ";
            var_export($v);
            echo "\n";
        }
    }
}

try{
    init();
}catch(Exception $e){
    echo "jBuildTools error : " , $e->getMessage(),"\n";
    echo "  options :  [-v] [-D foo=bar]* fichier.ini
      -v  : verbose mode
      -D  : declare a variable and its value
";

    exit(1);
}


?>