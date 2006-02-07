<?php

class jPreProcessor{
  private $_sourcefile;
  private $_destfile;
  private $_variables =array();
  private $_blockstack = array();
  public $error;
  const BLOCK_IF_YES =1;
  const BLOCK_IF_NO = 2;
  const BLOCK_ELSE_YES =4;
  const BLOCK_ELSE_NO =5;
  
  const ERR_SYNTAX     = 1;
  const ERR_IF_MISSING = 2;
  
  
  public function __construct($sourcefile, $destfile){
    $this->_sourcefile = $sourcefile;
    $this->_destfile = $destfile;
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
  
  public function run(){

   $source = file($this->_sourcefile);
   foreach($source as $nb=>$line){
     if(preg_match('/^\#(ifdef|expand|define|ifndef|elseifdef)\s+([^\s]*)\s*$/m',$line,$m)){
       switch($m[1]){
           case 'ifdef':
              if(end($this->_blockstack, self::BLOCK_IF_
              if(isset($this->_variables[$m[2]])){
                  array_push($this->_blockstack, self::BLOCK_IF_YES);
              }else{
                  array_push($this->_blockstack, self::BLOCK_IF_NO);
              }
              $source[$nb]='';
              break;
           case 'expand': 
              $source[$nb]=preg_replace('/\_\_(\w*)\_\_/e', '(isset($this->_variables["\\1"])?$this->_variables["\\1"]:"__\\1__")',$m[2]);
              break;
           case 'define':
              if(preg_match('/^(\w+)(?:\s+(\w+))?$/m',$m[2],$m2)){
              
              }else{
              
              
              }
              $source[$nb]='';
              break;
           case 'ifndef':
              if(isset($this->_variables[$m[2]])){
                  array_push($this->_blockstack, self::BLOCK_IF_NO);
              }else{
                  array_push($this->_blockstack, self::BLOCK_IF_YES);
              }    
              $source[$nb]='';
              break;
           case 'elseifdef':
              $end = array_pop($this->_blockstack);
              if($end != self::BLOCK_IF_YES && $end != self::BLOCK_IF_NO){
                $this->error = $nb+1;
                return  self::ERR_IF_MISSING;
              }
              if(isset($this->_variables[$m[2]])){
                  array_push($this->_blockstack, self::BLOCK_IF_YES);
              }else{
                  array_push($this->_blockstack, self::BLOCK_IF_NO);
              }
              $source[$nb]='';
              break;
       }
     }elseif(preg_match('/^\#(endif|else)\s*$/m',$line,$m)){
        if($m[1] == 'endif'){
            $end = array_pop($this->_blockstack);
            if( $end < self::BLOCK_IF_YES || $end > self::BLOCK_ELSE_NO){               
              $this->error = $nb+1;
              return  self::ERR_IF_MISSING;
            }
        }elseif($m[1]=='else'){
            $end = array_pop($this->_blockstack);
            if($end == self::BLOCK_IF){
              array_push($this->_blockstack, self::BLOCK_ELSE);
            }else{
              $this->error = $nb+1;
              return  self::ERR_IF_MISSING;
            }
        }
     }else{
       
     }
     
        
   }
   
   
   $content = implode("",$source);
   file_put_contents($this->_destfile, $content);
   return false;
 }

}

?>