<?php
/**
* @package     jBuildTools
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class jExceptionPreProc extends Exception {
    public $sourceFilename = '';
    public $sourceLine = 0;

    protected $errmessages = array(
        'unknown error',
        'syntax error',
        '#ifxx statement is missing',
        '#endif statement is missing',
        'Cannot include file %s',
        'Syntax error in the expression : %s',
        'Syntax error in an expression : "%s" is not allowed'
    );

    public function __construct($sourceFilename, $sourceLine, $code=0, $param=null) {
        $this->sourceFilename = $sourceFilename;
        $this->sourceLine = $sourceLine+1;
        if($code > count($this->errmessages)) $code = 0;
        if($param != null){
            $err = sprintf($this->errmessages[$code], $param);
        }else{
            $err = $this->errmessages[$code];
        }
        parent::__construct($err, $code);
    }

    public function __toString() {
        return 'Error '.$this->code.': '.$this->message .', file source='.$this->sourceFilename. ' line='.$this->sourceLine;
    }
}


class jPreProcessor{
    private $_variables =array();
    private $_savedVariables;
    private $_blockstack = array();
    private $_doSaveVariables=true;


    public $errorLine=0;
    public $errorCode=0;


    const BLOCK_NO = 1;
    const BLOCK_YES = 2;
    const BLOCK_YES_PREVIOUS = 4;

    const BLOCK_IF = 16;
    const BLOCK_ELSE = 32;

    const BLOCK_IF_YES =18;
    const BLOCK_IF_NO = 17;
    const BLOCK_ELSE_YES =34;
    const BLOCK_ELSE_NO =33;

    const ERR_SYNTAX     = 1;
    const ERR_IF_MISSING = 2;
    const ERR_ENDIF_MISSING = 3;
    const ERR_INVALID_FILENAME = 4;
    const ERR_EXPR_SYNTAX = 5;
    const ERR_EXPR_SYNTAX_TOK = 6;

    public function __construct(){
    }

    public function setVar($name, $value=''){
        $this->_variables[$name] = $value;
    }

    public function unsetVar($name){
        if(isset($this->_variables[$name]))
        unset($this->_variables[$name]);
    }

    public function setVars($arr){
        $this->_variables = $arr;
    }

    public function parseFile($filename){
        $this->errorCode=0;
        $this->errorLine=0;
        $this->_blockstack = array();

        // save the variables to get them back intact after the parsing
        // to be able to reexecute multiple times run on different content
        if($this->_doSaveVariables)
            $this->_savedVariables= $this->_variables;

        $source =explode("\n",file_get_contents($filename));

        $result='';
        $nb = -1;
        // go through each line of the source
        while(count($source)){
            $nb++;
            $sline = array_shift($source);
            $tline = $sline;
            $isOpen = !(end($this->_blockstack) & self::BLOCK_NO);

            if(preg_match('/^\#(ifdef|define|ifndef|elifdef|undef)\s+(\w+)\s*$/m',$sline,$m)){
                switch($m[1]){
                    case 'ifdef':
                        if( !$isOpen ){
                            array_push($this->_blockstack, self::BLOCK_IF_NO);
                        }else{
                            if(isset($this->_variables[$m[2]]) && $this->_variables[$m[2]] !==''){
                                array_push($this->_blockstack, self::BLOCK_IF_YES);
                            }else{
                                array_push($this->_blockstack, self::BLOCK_IF_NO);
                            }
                        }
                        $tline=false;
                        break;
                    case 'define': // define with only one argument
                        if($isOpen ){
                            $this->_variables[$m[2]] = true;
                        }
                        $tline=false;
                        break;
                    case 'undef':
                        if($isOpen ){
                            unset($this->_variables[$m[2]]);
                        }
                        $tline=false;
                        break;
                    case 'ifndef':
                        if(!$isOpen){
                            array_push($this->_blockstack, self::BLOCK_IF_NO);
                        }else{
                            if(isset($this->_variables[$m[2]]) && $this->_variables[$m[2]] !==''){
                                array_push($this->_blockstack, self::BLOCK_IF_NO);
                            }else{
                                array_push($this->_blockstack, self::BLOCK_IF_YES);
                            }
                        }
                        $tline=false;
                        break;
                    case 'elifdef':
                        $end = array_pop($this->_blockstack);
                        if(!($end & self::BLOCK_IF)){
                            throw new jExceptionPreProc($filename,$nb,self::ERR_IF_MISSING);
                        }
                        if(end($this->_blockstack) &  self::BLOCK_NO){
                            array_push($this->_blockstack, self::BLOCK_IF_NO);
                        }elseif(($end & self::BLOCK_YES) || ($end & self::BLOCK_YES_PREVIOUS)){
                            array_push($this->_blockstack, (self::BLOCK_IF_NO + self::BLOCK_YES_PREVIOUS));
                        }else{
                            if(isset($this->_variables[$m[2]]) && $this->_variables[$m[2]] !==''){
                                array_push($this->_blockstack, self::BLOCK_IF_YES);
                            }else{
                                array_push($this->_blockstack, self::BLOCK_IF_NO);
                            }
                        }
                        $tline=false;
                        break;
                }
                /*echo $m[1],':';
                var_dump($this->_blockstack);
                echo "\n";*/

            }elseif(preg_match('/^\#(define)\s+(\w+)\s+(.+)$/m',$sline,$m)){
                // define with two arguments
                if($isOpen){
                    $this->_variables[$m[2]] = trim($m[3]);
                }
                $tline=false;

            }elseif(preg_match('/^\#(expand)\s(.*)$/m',$sline,$m)){
                if($isOpen){
                    $tline=preg_replace('/\_\_(\w*)\_\_/e', '(isset($this->_variables["\\1"])&&$this->_variables["\\1"]!==\'\'?$this->_variables["\\1"]:"__\\1__")',$m[2]);
                }else{
                    $tline=false;
                }
            }elseif(preg_match('/^\#if\s(.*)$/m',$sline,$m)){
                if( !$isOpen ){
                    array_push($this->_blockstack, self::BLOCK_IF_NO);
                }else{
                    $val = $this->evalExpression($m[1], $filename,$nb);
                    if($val){
                        array_push($this->_blockstack, self::BLOCK_IF_YES);
                    }else{
                        array_push($this->_blockstack, self::BLOCK_IF_NO);
                    }
                }
                $tline=false;

            }elseif(preg_match('/^\#ifnot\s(.*)$/m',$sline,$m)){
                if( !$isOpen ){
                    array_push($this->_blockstack, self::BLOCK_IF_NO);
                }else{
                    $val = $this->evalExpression($m[1], $filename,$nb);
                    if($val){
                        array_push($this->_blockstack, self::BLOCK_IF_NO);
                    }else{
                        array_push($this->_blockstack, self::BLOCK_IF_YES);
                    }
                }
                $tline=false;
            }elseif(preg_match('/^\#elseif\s(.*)$/m',$sline,$m)){
                $end = array_pop($this->_blockstack);
                if(!($end & self::BLOCK_IF)){
                    throw new jExceptionPreProc($filename,$nb,self::ERR_IF_MISSING);
                }
                if(end($this->_blockstack) &  self::BLOCK_NO){
                    array_push($this->_blockstack, self::BLOCK_IF_NO);
                }elseif(($end & self::BLOCK_YES) || ($end & self::BLOCK_YES_PREVIOUS)){
                    array_push($this->_blockstack, (self::BLOCK_IF_NO + self::BLOCK_YES_PREVIOUS));
                }else{
                    $val = $this->evalExpression($m[1], $filename,$nb);
                    if($val){
                        array_push($this->_blockstack, self::BLOCK_IF_YES);
                    }else{
                        array_push($this->_blockstack, self::BLOCK_IF_NO);
                    }
                }
                $tline=false;
            }elseif(preg_match('/^\#(endif|else)\s*$/m',$sline,$m)){
                if($m[1] == 'endif'){
                    $end = array_pop($this->_blockstack);
                    if(!( $end & self::BLOCK_IF || $end & self::BLOCK_ELSE)){
                        throw new jExceptionPreProc($filename,$nb,self::ERR_IF_MISSING);
                    }
                    $tline=false;
                }elseif($m[1]=='else'){
                    $end = array_pop($this->_blockstack);
                    if($end === self::BLOCK_IF_YES){
                        array_push($this->_blockstack, self::BLOCK_ELSE_NO);

                    }elseif($end & self::BLOCK_IF_NO){
                        if((end($this->_blockstack) &  self::BLOCK_NO)
                            || ($end & self::BLOCK_YES_PREVIOUS)){
                            array_push($this->_blockstack, self::BLOCK_ELSE_NO);
                        }else{

                            array_push($this->_blockstack, self::BLOCK_ELSE_YES);
                        }

                    }else{
                        throw new jExceptionPreProc($filename,$nb,self::ERR_IF_MISSING);
                    }
                    $tline=false;
                }
            }elseif(preg_match('/^\#include(php|raw)?\s+([\w\/\.\:\-]+)((?:\s*\|\s*[\w\/\.\:\-]+)*)\s*$/m',$sline,$m)){
                if($isOpen){
                    $path = $m[2];
                    if (isset($m[3])) {
                        $options = preg_split('/\s*\|\s*/',$m[3]);
                    }
                    else $options = array();

                    if($m[1] == 'php'){
                        array_unshift($options,'rmphptag');
                    }

                    $tline = $this->readInclude($filename, $nb, $path, ($m[1]  == 'raw'), $options);

               }else{
                    $tline=false;
                }
            }elseif(preg_match('/^\#include(raw)?into\s+([\w]+)\s+([\w\/\.\:\-]+)((?:\s*\|\s*[\w\/\.\:\-]+)*)\s*$/m',$sline,$m)){
                if($isOpen){
                    $path = $m[3];
                    if (isset($m[4])) {
                        $options = preg_split('/\s*\|\s*/',$m[4]);
                    }
                    else $options = array();
                    
                    $this->_variables[$m[2]] = $this->readInclude($filename, $nb, $path, ($m[1]  == 'raw'), $options);
                }
                $tline=false;
            }elseif(strlen($sline) && $sline[0] == '#'){
                if(strlen($sline)>1 && $sline[1] == '#'){
                    if(!$isOpen){
                        $tline=false;
                    }else{
                        $tline = substr($sline,1);
                    }
                }else{
                    throw new jExceptionPreProc($filename,$nb,self::ERR_SYNTAX);
                }
            }else{
                if(!$isOpen){
                    $tline=false;
                }
            }
            if($tline!==false){
                if($result == '')
                    $result.=$tline;
                else
                    $result.="\n".$tline;
            }
        }

        if(count($this->_blockstack))
            throw new jExceptionPreProc($filename,$nb,self::ERR_ENDIF_MISSING);

        if($this->_doSaveVariables)
            $this->_variables = $this->_savedVariables;

        return $result;
    }

    protected $authorizedToken=array(T_DNUMBER, T_BOOLEAN_AND, T_BOOLEAN_OR, T_CHARACTER,
        T_IS_EQUAL,T_IS_GREATER_OR_EQUAL,T_IS_IDENTICAL,T_IS_NOT_EQUAL,T_IS_NOT_IDENTICAL,
        T_IS_SMALLER_OR_EQUAL, T_LOGICAL_AND, T_LOGICAL_OR, T_LOGICAL_XOR, T_LNUMBER, 
        T_CONSTANT_ENCAPSED_STRING, T_WHITESPACE);

    protected $authorizedChar = array('.', '+', '-', '/', '*','<','>', '!');

    protected function evalExpression($expression, $filename,$nb){

        $arr = token_get_all('<?php '.$expression.' ?>');
        $expr ='';
        foreach($arr as $k=>$c){
            if(is_array($c)){
                if($c[0] == T_OPEN_TAG){
                    if($k != 0) throw new jExceptionPreProc($filename,$nb,self::ERR_EXPR_SYNTAX_TOK, $c[1]);
                }elseif($c[0] == T_CLOSE_TAG){
                    if($k != count($arr) -1) throw new jExceptionPreProc($filename,$nb,self::ERR_EXPR_SYNTAX_TOK, $c[1]);
                }elseif($c[0] == T_STRING){
                    if(isset($this->_variables[$c[1]]))
                        $expr.='$this->_variables[\''.$c[1].'\']';
                    else
                        $expr.='""';
                }elseif(in_array($c[0], $this->authorizedToken)){
                    $expr .= $c[1];
                }else{
                    throw new jExceptionPreProc($filename,$nb,self::ERR_EXPR_SYNTAX_TOK, $c[1]);
                }
            }else{
                if(in_array($c, $this->authorizedChar)){
                    $expr .= $c;
                }else{
                    throw new jExceptionPreProc($filename,$nb,self::ERR_EXPR_SYNTAX_TOK, $c);
                }
            }
        }

        $val = null;

        if(false === @eval('$val='.$expr.';')){
            throw new jExceptionPreProc($filename,$nb,self::ERR_EXPR_SYNTAX, $expression);
        }else{
            return $val;
        }
    }

    protected function readInclude($currentFilename, $currentLine, $path, $raw, $options=array()) {
        if(!($path[0] == '/' || preg_match('/^\w\:\\.+$/',$path))){
            $path = realpath(dirname($currentFilename).'/'.$path);
            if($path == ''){
                throw new jExceptionPreProc($currentFilename, $currentLine, self::ERR_INVALID_FILENAME, $path);
            }
        }
        if(file_exists($path) && !is_dir($path)){
            if ($raw) {
                $tline = file_get_contents($path);  
            }
            else {
                $preproc = new jPreProcessor();
                $preproc->_doSaveVariables = false;
                $preproc->setVars($this->_variables);
                $tline = $preproc->parseFile($path);
                $this->_variables = $preproc->_variables;
                $preproc = null;
            }
        }else{
            throw new jExceptionPreProc($currentFilename, $currentLine, self::ERR_INVALID_FILENAME, $path);
        }
        
        if (count($options))
            $tline = $this->applyIncludeOptions($tline, $options);
        return $tline;
    }


    protected function applyIncludeOptions($content, $options) {
        foreach ($options as $option) {
            if (trim($option) == '')
                continue;
            switch($option) {
                case 'rmphptag':
                    if(preg_match('/^\s*\<\?(?:php)?(.*)/sm',$content,$ms)){
                        $content = $ms[1];
                    }
                    if(preg_match('/(.*)\?\>\s*$/sm',$content,$ms)){
                        $content = $ms[1];
                    }
                    break;
                case 'escquote':
                    $content = str_replace('\'', '\\\'', $content);
                    break;
                case 'escdblquote':
                    $content = str_replace('"', '\\"', $content);
                    break;
                case 'base64':
                    $content = base64_encode($content);
                    break;
                case 'jspacker':
                    $packer = new JavaScriptPacker($content, 0, true, false);
                    $content = $packer->pack();
                    break;
                case 'addquote':
                    $content = "'".$content."'";
                    break;
                case 'adddblquote':
                    $content = '"'.$content.'"';
                    break;
            }
        }
        return $content;
    }
}

