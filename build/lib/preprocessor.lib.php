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


class jExceptionPreProc extends Exception {
    public $sourceFilename = '';
    public $sourceLine = 0;

    protected $errmessages = array(
        'unknow error',
        'syntax error',
        '#ifxx statement is missing',
        '#endif statement is missing',
        'cannot include file',
    );

    public function __construct($sourceFilename, $sourceLine, $code=0) {
        $this->sourceFilename = $sourceFilename;
        $this->sourceLine = $sourceLine+1;
        if($code > count($this->errmessages)) $code = 0;
        parent::__construct($this->errmessages[$code], $code);
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

        // on sauve les variables pour les retrouver intact aprés le parsing
        // de façon à pouvoir rééxecuter plusieurs fois run sur des contenus différents
        if($this->_doSaveVariables)
            $this->_savedVariables= $this->_variables;

        $source =explode("\n",file_get_contents($filename));

        $result='';
        // on parcours chaque ligne du source
        foreach($source as $nb=>$line){
            $isOpen = !(end($this->_blockstack) & self::BLOCK_NO);

            if(preg_match('/^\#(ifdef|define|ifndef|elifdef|undef)\s+(\w+)\s*$/m',$line,$m)){
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
                        $source[$nb]=false;
                        break;
                    case 'define': // define avec un seul argument.
                        if($isOpen ){
                            $this->_variables[$m[2]] = true;
                        }
                        $source[$nb]=false;
                        break;
                    case 'undef':
                        if($isOpen ){
                            unset($this->_variables[$m[2]]);
                        }
                        $source[$nb]=false;
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
                        $source[$nb]=false;
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
                        $source[$nb]=false;
                        break;
                }
                /*echo $m[1],':';
                var_dump($this->_blockstack);
                echo "\n";*/

            }elseif(preg_match('/^\#(define)\s+(\w+)\s+(.+)$/m',$line,$m)){
                // define avec deux arguments
                if($isOpen){
                    $this->_variables[$m[2]] = trim($m[3]);
                }
                $source[$nb]=false;

            }elseif(preg_match('/^\#(expand)\s(.*)$/m',$line,$m)){
                if($isOpen){
                    $source[$nb]=preg_replace('/\_\_(\w*)\_\_/e', '(isset($this->_variables["\\1"])&&$this->_variables["\\1"]!==\'\'?$this->_variables["\\1"]:"__\\1__")',$m[2]);
                }else{
                    $source[$nb]=false;
                }

            }elseif(preg_match('/^\#(endif|else)\s*$/m',$line,$m)){
                if($m[1] == 'endif'){
                    $end = array_pop($this->_blockstack);
                    if(!( $end & self::BLOCK_IF || $end & self::BLOCK_ELSE)){
                        throw new jExceptionPreProc($filename,$nb,self::ERR_IF_MISSING);
                    }
                    $source[$nb]=false;
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
                    $source[$nb]=false;
                }
            }elseif(preg_match('/^\#include(php)?\s+([\w\/\.\:]+)\s*$/m',$line,$m)){
                if($isOpen){
                    $path = $m[2];
                    if(!($path{0} == '/' || preg_match('/^\w\:\\.+$/',$path))){
                        $path = realpath(dirname($filename).'/'.$path);
                        if($path == ''){
                            throw new jExceptionPreProc($filename,$nb,self::ERR_INVALID_FILENAME);
                        }
                    }
                    if(file_exists($path) && !is_dir($path)){
                        $preproc = new jPreProcessor();
                        $preproc->_doSaveVariables = false;
                        $preproc->setVars($this->_variables);
                        $source[$nb] = $preproc->parseFile($path);
                        $this->_variables = $preproc->_variables;
                    }else{
                        throw new jExceptionPreProc($filename,$nb,self::ERR_INVALID_FILENAME);
                    }
                    if($m[1] == 'php'){
                        if(preg_match('/^\s*\<\?(?:php)?(.*)\?\>\s*$/sm',$source[$nb],$ms)){
                            $source[$nb] = $ms[1];
                        }
                    }
               }else{
                    $source[$nb]=false;
                }
            }elseif(strlen($line) && $line{0} == '#'){
                if(strlen($line)>1 && $line{1} == '#'){
                    if(!$isOpen){
                        $source[$nb]=false;
                    }else{
                        $source[$nb] = substr($line,1);
                    }
                }else{
                    throw new jExceptionPreProc($filename,$nb,self::ERR_SYNTAX);
                }
            }else{
                if(!$isOpen){
                    $source[$nb]=false;
                }
            }
            if($source[$nb]!==false){
                if($result == '')
                    $result.=$source[$nb];
                else
                    $result.="\n".$source[$nb];
            }
        }

        if(count($this->_blockstack))
            throw new jExceptionPreProc($filename,$nb,self::ERR_ENDIF_MISSING);

        if($this->_doSaveVariables)
            $this->_variables = $this->_savedVariables;

        return $result;
    }
}

?>