<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2005-2010 Laurent Jouanneau, 2008 Loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

/**
 * load a command object
 * @param string $cmdName the name of the command
 * @return JelixScriptCommand  the command
 */
function jxs_load_command($cmdName){
   $commandfile = JELIX_SCRIPT_PATH.'commands/'.$cmdName.'.cmd.php';

   if(!file_exists($commandfile)){
      die("Error: unknown command\n");

   }

   require_once($commandfile);

   $cmdName.='Command';

   if(!class_exists( $cmdName)){
      die("Error: can't find the command runtime\n");
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
         die("Error: unknown option '".$argv[0]."' \n");
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
      if($pname == '...'){
        $parameters['...']=array();
        foreach($argv as $arg){
            $parameters['...'][]=$arg;
        }
        $argv=array();
        break;
      }else{
         $parameters[$pname]=array_shift($argv);
      }
   }

   if(count($argv)){
      die("Error: too many parameters\n");
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

function jxs_getRelativePath($path, $targetPath, $intoString=false){
    $cut = (DIRECTORY_SEPARATOR == '/'? '!/!': "![/\\\\]!");
    $sep = DIRECTORY_SEPARATOR;
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
    if(count($path)){
        $relativePath=str_repeat('..'.$sep,count($path));
    }else{
        $relativePath='.'.$sep;
    }
    if(count($targetPath) && $dir != $targetdir){
        $relativePath.= $targetdir.$sep.implode($sep,$targetPath);
    }elseif(count($targetPath) ){
        $relativePath.= implode($sep,$targetPath);
    }
    if(substr($relativePath,-1) != $sep)
        $relativePath.=$sep;
    if($sep =='\\') {
        $relativePath = str_replace('\\','/', $relativePath);
    }
    return $relativePath;
}

function jxs_init_jelix_env(){
   global $gJConfig;
   global $entryPointName;

   if ($gJConfig) 
      return;
   
   $xml = simplexml_load_file(JELIX_APP_PATH.'project.xml');
   $configFile = '';

   foreach ($xml->entrypoints->entry as $entrypoint) {
      $file = (string)$entrypoint['file'];
      if ($file == $entryPointName) {
         $configFile = (string)$entrypoint['config'];
         break;
      }
   }

   if ($configFile == '')
      throw new Exception("Entry point is unknown");
   require_once(JELIX_LIB_PATH."core/jConfigCompiler.class.php");
   $gJConfig = jConfigCompiler::read($configFile, true, true, $entryPointName);
}



function jlx_error_handler($errno, $errmsg, $filename, $linenum, $errcontext){

   if (error_reporting() == 0)
      return;

   $codeString = array(
      E_ERROR         => 'error',
      E_RECOVERABLE_ERROR => 'error',
      E_WARNING       => 'warning',
      E_NOTICE        => 'notice',
      E_DEPRECATED    => 'deprecated',
      E_USER_ERROR    => 'error',
      E_USER_WARNING  => 'warning',
      E_USER_NOTICE   => 'notice',
      E_USER_DEPRECATED => 'deprecated',
      E_STRICT        => 'strict'
   );

   if(preg_match('/^\s*\((\d+)\)(.+)$/',$errmsg,$m)){
      $code = $m[1];
      $errmsg = $m[2];
   }else{
      $code=1;
   }

   if (isset ($codeString[$errno])){
      $codestr = $codeString[$errno];
   }else{
      $codestr = 'error';
   }
   $messageLog = strtr("[%typeerror%:%code%]\t%msg%\t%file%\t%line%\n", array(
      '%date%' => date("Y-m-d H:i:s"),
      '%typeerror%'=>$codestr,
      '%code%' => $code,
      '%msg%'  => $errmsg,
      '%file%' => $filename,
      '%line%' => $linenum,
   ));

   $traceLog = '';
   $messageLog.="\ttrace:";
   $trace = debug_backtrace();
   array_shift($trace);
   foreach($trace as $k=>$t){
       $traceLog.="\n\t$k\t".(isset($t['class'])?$t['class'].$t['type']:'').$t['function']."()\t";
       $traceLog.=(isset($t['file'])?$t['file']:'[php]').' : '.(isset($t['line'])?$t['line']:'');
   }
   $messageLog.=$traceLog."\n";
   
   echo $messageLog;

   if ($codestr == 'error')
      exit(1);
}
