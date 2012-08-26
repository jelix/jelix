<?php

/**
* @package     jBuildTools
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

require_once(__DIR__.'/lib/jCmdUtils.class.php');

// arguments :  directory1 [directory2]
// directory1 : directory path from which we want the list
// directory2 : if we want a different base path than directory1 in the output file
//               indicate it here

try{
    $sws = array('-e'=>false);
    $params = array('dirpath'=>true, 'basepath'=>false);
    
    list($switches, $parameters) = jCmdUtils::getOptionsAndParams($_SERVER['argv'], $sws, $params);

}catch(Exception $e){
    echo "\nmkmanifest error : " , $e->getMessage(),"\n";
    echo "  options : [-e] dirpath [basepath]
      -e  : include empty directory\n";
    exit(1);
}

$dirpath = $parameters['dirpath'];
$basepath = $parameters['basepath'];

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
   global $switches;
   $output='';
   if ($dh = opendir($dir)) {
      $dirlist=array();
      $cdok=false;
      while (($file = readdir($dh)) !== false) {
         if($file == '.svn')
            continue;
         $type= filetype($dir.'/'.$file);
         if($type == 'dir'){
            if($file != '.' && $file !='..' ){
               $dirlist[]=$file;
            }
         }else{
            if(!$cdok){
               $output.="cd ".$basepath."\n";
               $cdok=true;
            }
            $output.="  ".$file."\n";
         }
      }
      closedir($dh);

      if(!$cdok && count($dirlist) == 0 && isset($switches['-e'])){
          $output.="cd ".$basepath."\n";
      }

      foreach($dirlist as $d){
         $output.=mkpath( $dir.'/'.$d, $basepath.'/'.$d);
      }
   }else{
      echo "error opening directory $dir\n";
       exit(1);
   }
   return $output;
}



$output='';
if (is_dir($dirpath)) {
   $output=mkpath($dirpath,$basepath);
}else{
     echo "wrong directory $dirpath\n";
    exit(1);
}

echo $output;
exit(0);
?>
