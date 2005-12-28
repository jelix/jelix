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


?>