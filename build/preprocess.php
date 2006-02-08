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

$options = array('verbose'=>false);

if(substr($_SERVER['argv'][0],0,1) == '-'){
  $sw = substr(array_shift($_SERVER['argv']),1);
  $options['verbose'] = (strpos('v', $sw) !== false);

}

list($sourcefile, $distfile) = $_SERVER['argv'];


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
$proc->setVars($_ENV);
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