<?php

/**
* @package     jBuildTools
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

// arguments :  fichier.lf   chemin_source chemin_dist

require_once(dirname(__FILE__).'/preprocessor.lib.php');

if($_SERVER['argc'] < 4){
   exit(1);
}
array_shift($_SERVER['argv']); // shift the script name

$options = array('verbose'=>false);

if(substr($_SERVER['argv'][0],0,1) == '-'){
  $sw = substr(array_shift($_SERVER['argv']),1);
  $options['verbose'] = (strpos('v', $sw) !== false);

}
/*$env = $_SERVER;
$options = array('verbose'=>false);
$arrargv = $_SERVER['argv'];
foreach($arrargv as $argv){

  if($argv{0} == '-'){
    $sw = substr(array_shift($_SERVER['argv']),1);
    if($sw{0} =='D'){

    }else{
      $options['verbose'] = (strpos('v', $sw) !== false);
    }
  }else{
    break;
  }
}*/
list($ficlist, $sourcedir, $distdir) = $_SERVER['argv'];


function createDir ($dir){
    if (!file_exists($dir)) {
        createDir(dirname($dir));
        mkdir($dir, 0775);
    }
}

function normalizeDir($dirpath){

  if(substr($dirpath,-1) != '/'){
    $dirpath.='/';
  }
  return $dirpath;
}

$sourcedir = normalizeDir($sourcedir);
$distdir =  normalizeDir($distdir);

$script = file($ficlist);
$hasError=false;
$currentdestdir = '';
$currentsrcdir = '';
$preproc = new jPreProcessor();

foreach($script as $nbline=>$line){
  $nbline++;
  if(preg_match('!^(cd|sd|dd|\*)?\s+([a-zA-Z0-9\/.\-_]+)\s*(?:\(([a-zA-Z0-9\/.\-_]*)\))?\s*$!m', $line, $m)){
    if($m[1] == 'dd'){
      $currentdestdir = normalizeDir($m[2]);
      createDir($distdir.$currentdestdir);
    }elseif($m[1] == 'sd'){
      $currentsrcdir = normalizeDir($m[2]);
    }elseif($m[1] == 'cd'){
      $currentsrcdir = normalizeDir($m[2]);
      $currentdestdir = normalizeDir($m[2]);
      createDir($distdir.$currentdestdir);
    }else{
      if($m[2] == ''){
        echo "$ficlist : file required on line $nbline \n";
        $hasError=true;
        break;
      }
      if(!isset($m[3]) || $m[3]=='') $m[3]=$m[2];

      $destfile = $distdir.$currentdestdir.$m[3];

      if($m[1]=='*'){
        if($options['verbose']){
            echo "process  ".$sourcedir.$currentsrcdir.$m[2]."\tto\t".$destfile."\n";
        }

        $preproc->setVars($_SERVER);
        $contents = $preproc->run(file_get_contents($sourcedir.$currentsrcdir.$m[2]));
        if($contents===false){
            echo "$ficlist : line $nbline, cannot process file ".$m[2]." (error code=".$preproc->errorCode."  line=".$preproc->errorLine.")\n";
            $hasError=true;
            break;
        }else{
          file_put_contents($destfile,$contents);
        }
      }else{
        if($options['verbose'])
          echo "copy  ".$sourcedir.$currentsrcdir.$m[2]."\tto\t".$destfile."\n";

        if(!copy($sourcedir.$currentsrcdir.$m[2], $destfile)){
            echo "$ficlist : cannot copy file ".$m[2].", line $nbline \n";
            $hasError=true;
            break;
        }
      }

    }

  }elseif(preg_match("!^\s*(\#.*)?$!",$line)){
    // commentaire, on ignore
  }else{
    echo "$ficlist : syntax error on line $nbline \n";
    $hasError=true;
    break;
  }
}

if($hasError)
  exit(1);
else
  exit(0);
?>