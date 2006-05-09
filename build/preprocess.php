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

require_once(dirname(__FILE__).'/preprocessor.lib.php');

// arguments :  chemin_source chemin_dist

if($_SERVER['argc'] < 3){
   exit(1);
}
array_shift($_SERVER['argv']); // shift the script name
$restrictedDirectory = '';
$options = array('verbose'=>false);

if(substr($_SERVER['argv'][0],0,1) == '-'){
  $sw = substr(array_shift($_SERVER['argv']),1);
  $options['verbose'] = (strpos($sw, 'v') !== false);
  if(strpos($sw,'d') !== false){
     $restrictedDirectory = array_shift($_SERVER['argv']);
  }

}

list($sourcefile, $distfile) = $_SERVER['argv'];

if($restrictedDirectory !=''){
   $s = realpath($sourcefile);
   if(strpos($s, $restrictedDirectory) !==0){
      exit(1);
   }
}

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


$source = file_get_contents($sourcefile);

$proc = new jPreProcessor();
$proc->setVars($_SERVER);
$dist = $proc->run($source);


if($dist === false){
  echo 'Error : code='.$proc->errorCode.' line='.$proc->errorLine."\n";
  exit(1);
}else{
  createDir(dirname($distfile));
  file_put_contents($distfile, $dist);
  exit(0);
}
?>