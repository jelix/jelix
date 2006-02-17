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

// arguments :  repertoire1 [repertoire2] fichier.mn
// repertoire1 : le chemin du repertoire duquel on veut la liste
// repertoire2 : si on veut une base de chemin différente de repertoire1 dans le fichier de sortie
//               on l'indique ici

if($_SERVER['argc'] < 2){
   exit(1);
}
array_shift($_SERVER['argv']); // shift the script name
$nbarg=$_SERVER['argc'] -1;

$options = array('verbose'=>false);

if(substr($_SERVER['argv'][0],0,1) == '-'){
  $nbarg--;
  $sw = substr(array_shift($_SERVER['argv']),1);
  $options['verbose'] = (strpos('v', $sw) !== false);
}

if($nbarg == 1){
   list($dirpath) = $_SERVER['argv'];
   $basepath='';
}elseif($nbarg == 2){
   list($dirpath, $basepath) = $_SERVER['argv'];
}else{
   echo "too few arguments\n";
   exit(1);
}

$hasError =false;

if(substr($dirpath,-1) == '/'){
   $dirpath=substr($dirpath,0,-1);
}

if($basepath == ''){
   $basepath = $dirpath;
}else{
   if(substr($basepath,-1) == '/'){
      $basepath=substr($basepath,0,-1);
   }
}

function mkpath($dir, $basepath){
   $output='';
   if ($dh = opendir($dir)) {
      $dirlist=array();
      $cdok=false;
      while (($file = readdir($dh)) !== false) {
         if($file == '.svn')
            continue;
         $type= filetype($dir.'/'.$file);
         if($type == 'dir'){
            if($file != '.' && $file !='..' )
               $dirlist[]=$file;
         }else{
            if(!$cdok){
               $output.="cd ".$basepath."\n";
               $cdok=true;
            }
            $output.="  ".$file."\n";
         }
      }
      closedir($dh);

      foreach($dirlist as $d){
         $output.=mkpath( $dir.'/'.$d, $basepath.'/'.$d);
      }
   }else{
      //echo "erreur ouverture repertoire $dir\n";
   }
   return $output;
}



$output='';
if (is_dir($dirpath)) {
   $output=mkpath($dirpath,$basepath);
}else{
     //echo "mauvais repertoire $dir\n";
}

echo $output;

if($hasError)
  exit(1);
else
  exit(0);
?>