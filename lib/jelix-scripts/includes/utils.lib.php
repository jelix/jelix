<?php
/**
* @package     jelix-scripts
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


function jxs_load_command($cmdName){
   $commandfile = JELIX_SCRIPT_PATH.'commands/'.$cmdName.'.cmd.php';

   if(!file_exists($commandfile)){
      die("Error: unknow command\n");

   }

   require($commandfile);

   $cmdName.='Command';

   if(!class_exists( $cmdName)){
      die("Error: don't find the command runtime\n");
   }

   $command = new $cmdName;
   return $command;
}


function jxs_getOptionsAndParams($argv, $sws,$params){

   $switches = array();
   $parameters = array();

   //---------- get the switches

   while(count($argv) && $argv[0]{0} == '-'){
      if(isset($sws[$argv[0]])){

         if($sws[$argv[0]]){
            if(isset($argv[1]) && $argv[1]{0} != '-'){
               $sw = array_shift($argv);
               $switches[$sw]=array_shift($argv);
            }else{
               die("Error: parameter missing for the '".$argv[0]."' option\n");
            }
         }else{
            $sw = array_shift($argv);
            $switches[$sw]=true;
         }
      }else{
         die("Error: unknow option '".$argv[0]."' \n");
      }
   }

   //---------- get the parameters

   foreach( $params as $pname => $needed){
      if(count($argv)==0){
         if($needed){
            die("Error: '".$pname."' parameter missing\n");
         }else{
            break;
         }
      }
      $parameters[$pname]=array_shift($argv);
   }

   if(count($argv)){
      die("Error: two many parameters\n");
   }

   return array($switches , $parameters);
}

function jxs_commandlist(){

   $list=array();
   $dir = JELIX_SCRIPT_PATH.'commands/';
   if ($dh = opendir($dir)) {
       while (($file = readdir($dh)) !== false) {
           if(is_file($dir . $file) && strpos($file,'.cmd.php') !==false){
              $list[]=substr($file,0, -8);
           }
       }
       closedir($dh);
   }
   return $list;
}


function jxs_getPathSeparator(){
    if(preg_match("/^(\w+).*$/", PHP_OS, $m)){
        $os=$m[1];
    }else{
        $os=PHP_OS;
    }
    if(strtolower($os) == 'win')
        return array("\\","![/\\]!");
    else
       return array('/','!/!');
}


function jxs_getRelativePath($path, $targetPath){
    list($sep, $cut) = jxs_getPathSeparator();

    $path = preg_split($cut,$path);
    $targetPath = preg_split($cut,$targetPath);

    $dir='';
    $targetdir='';

    while(count($path)){
        $dir=array_shift($path);
        $targetdir=array_shift($targetPath);
        if($dir != $targetdir)
            break;
    }
    $relativePath=str_repeat('..'.$sep,count($path));
    if($targetdir != '' && $dir != $targetdir)
        $relativePath.= $targetdir.$sep.implode($sep,$targetPath);
    else
        $relativePath.='.';
    return $relativePath;
}

function jxs_init_jelix_env(){
   global $gJConfig;

   $gJConfig = jConfig::load(JELIXS_APP_CONFIG_FILE);


}


?>