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

class jPreProcessor{
  private $_variables =array();
  private $_savedVariables;
  private $_blockstack = array();
  public $errorLine=0;
  public $errorCode=0;

  const BLOCK_IF_YES =1;
  const BLOCK_IF_NO = 5;
  const BLOCK_ELSE_YES =2;
  const BLOCK_ELSE_NO =6;

  const BLOCK_NO = 4;

  const ERR_SYNTAX     = 1;
  const ERR_IF_MISSING = 2;


  public function __construct(){
  }

  public function setVar($name, $value){
     $this->_variables[$name] = $value;
  }

  public function unsetVar($name){
    if(isset($this->_variables[$name]))
       unset($this->_variables[$name]);
  }

  public function setVars($arr){
     $this->_variables = $arr;
  }

  public function run($sourceContent){

   $this->_savedVariables= $this->_variable;

   $source = explode("\n",$sourceContent);
   foreach($source as $nb=>$line){
     if(preg_match('/^\#(ifdef|expand|define|ifndef|elseifdef)\s+([^\s]*)\s*$/m',$line,$m)){
       switch($m[1]){
           case 'ifdef':
              if(end($this->_blockstack) &  self::BLOCK_NO){
                   array_push($this->_blockstack, self::BLOCK_IF_NO);
              }else{
                  if(isset($this->_variables[$m[2]])){
                        array_push($this->_blockstack, self::BLOCK_IF_YES);
                  }else{
                        array_push($this->_blockstack, self::BLOCK_IF_NO);
                  }
              }
              $source[$nb]='';
              break;
           case 'expand':
              if(! (end($this->_blockstack) &  self::BLOCK_NO)){
                 $source[$nb]=preg_replace('/\_\_(\w*)\_\_/e', '(isset($this->_variables["\\1"])?$this->_variables["\\1"]:"__\\1__")',$m[2]);
              }else{
                 $source[$nb]='';
              }
              break;
           case 'define':
              /*if(preg_match('/^(\w+)(?:\s+(\w+))?$/m',$m[2],$m2)){

              }else{


              }*/
              $source[$nb]='';
              break;
           case 'ifndef':
              if(end($this->_blockstack) &  self::BLOCK_NO){
                   array_push($this->_blockstack, self::BLOCK_IF_NO);
              }else{
                  if(isset($this->_variables[$m[2]])){
                     array_push($this->_blockstack, self::BLOCK_IF_NO);
                  }else{
                     array_push($this->_blockstack, self::BLOCK_IF_YES);
                  }
              }
              $source[$nb]='';
              break;
           case 'elseifdef':
              $end = array_pop($this->_blockstack);
              if($end != self::BLOCK_IF_YES && $end != self::BLOCK_IF_NO){
                 return $this->doError($nb,self::ERR_IF_MISSING);
              }
              if(end($this->_blockstack) &  self::BLOCK_NO){
                   array_push($this->_blockstack, self::BLOCK_IF_NO);
              }else{
                  if(isset($this->_variables[$m[2]])){
                        array_push($this->_blockstack, self::BLOCK_IF_YES);
                  }else{
                        array_push($this->_blockstack, self::BLOCK_IF_NO);
                  }
              }
              $source[$nb]='';
              break;
       }
     }elseif(preg_match('/^\#(endif|else)\s*$/m',$line,$m)){
        if($m[1] == 'endif'){
            $end = array_pop($this->_blockstack);
            if( $end < self::BLOCK_IF_YES || $end > self::BLOCK_ELSE_NO){
               return $this->doError($nb,self::ERR_IF_MISSING);
            }
        }elseif($m[1]=='else'){
            $end = array_pop($this->_blockstack);
            if($end === self::BLOCK_IF_YES){
               array_push($this->_blockstack, self::BLOCK_ELSE_NO);
            }elseif($end === self::BLOCK_IF_NO){
              if(end($this->_blockstack) &  self::BLOCK_NO){
                  array_push($this->_blockstack, self::BLOCK_ELSE_NO);
               }else{
                  array_push($this->_blockstack, self::BLOCK_ELSE_YES);
               }
            }else{
               return $this->doError($nb,self::ERR_IF_MISSING);
            }
        }
     }else{
         if(end($this->_blockstack) &  self::BLOCK_NO)){
            $source[$nb]='';
         }
     }
   }

   $this->_variable = $this->_savedVariables;
   return implode("\n",$source);
 }

 protected function doError($line,$code){
   $this->_variable = $this->_savedVariables;
   $this->errorLine = $line+1;
   $this->errorCode = $code;

   return false;
 }

}

?>