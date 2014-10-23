<?php
/**
* @package     jBuildTools
* @author      Laurent Jouanneau
* @contributor Kévin Lepeltier
* @copyright   2006-2014 Laurent Jouanneau
* @copyright   2008 Kévin Lepeltier
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class jPhpCommentsRemover {
    
    static protected $indentation = 4;
    
    static public function stripComments($content, $indentation = 4){
        self::$indentation = $indentation;
        $tokens = token_get_all($content);
        $result = '';
        $firstcomment= true;
        $currentWhitespace ='';
        $firstPHPfound = false;
        $canRemoveNextSpaces = false;
        $operators = array(T_AND_EQUAL,T_BOOLEAN_AND,T_BOOLEAN_OR,T_CONCAT_EQUAL,T_DIV_EQUAL,T_DOUBLE_ARROW ,T_DOUBLE_COLON,
                            T_IS_EQUAL,T_IS_GREATER_OR_EQUAL,T_IS_IDENTICAL, T_IS_NOT_EQUAL,T_IS_NOT_IDENTICAL,
                            T_IS_SMALLER_OR_EQUAL,T_MINUS_EQUAL,T_MOD_EQUAL,T_MUL_EQUAL,T_OBJECT_OPERATOR,
                            T_OR_EQUAL,T_PAAMAYIM_NEKUDOTAYIM,T_PLUS_EQUAL,T_SL, T_SL_EQUAL,T_SR,T_SR_EQUAL, T_XOR_EQUAL);
        $signs = array('(',')','{','}','=',',', ';');
        foreach($tokens as $token) {
            if (is_string($token)) {
                $isSign = in_array($token, $signs);
                if($isSign && strpos($currentWhitespace, "\n") === false) {
                   $currentWhitespace='';
                }
                $result.= self::strip_ws($currentWhitespace, $canRemoveNextSpaces);
                $canRemoveNextSpaces = $isSign;
                $result.=$token;
            } else {
                switch ($token[0]) {
                    case T_OPEN_TAG:
                        $result.= self::strip_ws($currentWhitespace, $canRemoveNextSpaces);
                        $result.=$token[1];
                        if(!$firstPHPfound) {
                            $result.= "/* comments & extra-whitespaces have been removed by jBuildTools*/\n";
                            $firstPHPfound=true;
                        }
                        break;
                    case T_COMMENT:
                        $currentWhitespace.="\n";
                        break;
                    case T_DOC_COMMENT:
                        // wee keep the first doc comment
                        if($firstcomment){
                            $result.= self::strip_ws($currentWhitespace, $canRemoveNextSpaces);
                            $result.=$token[1];
                            $firstcomment = false;
                        }
                        break;
                    case T_WHITESPACE:
                        $currentWhitespace.=$token[1];
                        break;
                    default:
                        if (in_array($token[0], $operators)) {
                            if(strpos($currentWhitespace, "\n") === false) {
                                $currentWhitespace='';
                            }
                            $result.= self::strip_ws($currentWhitespace, $canRemoveNextSpaces);
                            $canRemoveNextSpaces = true;
                        }
                        else {
                            $result.= self::strip_ws($currentWhitespace, $canRemoveNextSpaces);
                        }
                        $result.=$token[1];
                        break;
                }
            }
        }
        return $result."\n";
    }

    static protected function strip_ws(& $s, &$canRemoveNextSpaces){

        if ($s == '') {
            $canRemoveNextSpaces = false;
            return $s;
        }

        $indent = str_repeat(" ", self::$indentation);
        $result = $s;
        $result = str_replace("\r\n","\n",$result); // removed \r
        $result = str_replace("\r","\n",$result); // removed standalone \r
        $result = preg_replace("(\n+)", "\n", $result);
        $result = str_replace("\t",$indent,$result);
        $result = str_replace($indent,"\t",$result);

        $result = preg_replace("/^([\n \t]+)\n([ \t]*)$/", "\n$2", $result);

        if (strpos($result, "\n") === false && $canRemoveNextSpaces) {
            $result = '';
        }
        else if (preg_match("/( +)$/", $result,$m)) {
            // if there are  still spaces at the end, we remove it or replace it by a
            // tab, depending of the len of this spaces.
            $s = $m[1];
            $l = strlen($s);
            if ($l < strlen($result)) {
                $result = substr($result, 0, -$l);
                if ($l > (self::$indentation/2))
                    $result .= "\t";
            }
            else if ($canRemoveNextSpaces)
                $result = '';
        }
        $s = '';
        $canRemoveNextSpaces = false;
        return $result;
    }
}