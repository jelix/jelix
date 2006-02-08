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

if($_SERVER['argc'] < 4){
   exit(1);
}
array_shift($_SERVER['argv']); // shift the script name

$options = array('verbose'=>false);

if(substr($_SERVER['argv'][0],0,1) == '-'){
  $sw = substr(array_shift($_SERVER['argv']),1);
  $options['verbose'] = (strpos('v', $sw) !== false);

}

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
foreach($script as $nbline=>$line){
  $nbline++;
  if(preg_match('!^(cd|sd|dd|\*)?\s+([a-zA-Z0-9\/.\-_]+)\s*(?:\(([a-zA-Z0-9\/.\-_]*)\))?\s*$!m', $line, $m)){
    if($m[1] == 'dd'){
      $currentdestdir = normalizeDir($m[2]);
    }elseif($m[1] == 'sd'){
      $currentsrcdir = normalizeDir($m[2]);
    }elseif($m[1] == 'cd'){
      $currentsrcdir = normalizeDir($m[2]);
      $currentdestdir = normalizeDir($m[2]);
    }else{
      if($m[2] == ''){
        echo "$ficlist : file required on line $nbline \n";
        $hasError=true;
        break;
      }
      if($m[3]=='') $m[3]=$m[2];

      $destfile = $distdir.$currentdestdir.$m[3];
      createDir(dirname($destfile));

      if($m[1]=='*'){
        if($options['verbose'])
          echo "process  ".$sourcedir.$currentsrcdir.$m[2]."\tto\t".$destfile."\n";

        if(!copy($sourcedir.$currentsrcdir.$m[2], $destfile)){
            echo "$ficlist : cannot process file ".$m[2].", line $nbline \n";
            $hasError=true;
            break;
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