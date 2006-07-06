<?php
/**
* @package     jelix
* @subpackage  jtpl
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor Mathaud Loic (version standalone)
* @copyright   2005-2006 Jouanneau laurent
* @copyright   2006 Mathaud Loic
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jTplCompiler
#ifndef JTPL_STANDALONE
    implements jISimpleCompiler {
#else
    {

    private $_locales;
#endif
    private $_literals;

    private  $_vartype = array(T_CHARACTER, T_CONSTANT_ENCAPSED_STRING, T_DNUMBER,
    T_ENCAPSED_AND_WHITESPACE, T_LNUMBER, T_OBJECT_OPERATOR, T_STRING, T_WHITESPACE,T_ARRAY);

    private  $_assignOp = array(T_AND_EQUAL, T_DIV_EQUAL, T_MINUS_EQUAL, T_MOD_EQUAL,
    T_MUL_EQUAL, T_OR_EQUAL, T_PLUS_EQUAL, T_PLUS_EQUAL, T_SL_EQUAL,
    T_SR_EQUAL, T_XOR_EQUAL);

    private  $_op = array(T_BOOLEAN_AND, T_BOOLEAN_OR, T_EMPTY, T_INC, T_ISSET,
    T_IS_EQUAL, T_IS_GREATER_OR_EQUAL, T_IS_IDENTICAL, T_IS_NOT_EQUAL,
    T_IS_NOT_IDENTICAL, T_IS_SMALLER_OR_EQUAL, T_LOGICAL_AND,
    T_LOGICAL_OR, T_LOGICAL_XOR, T_SR, T_SL, T_DOUBLE_ARROW);

    private $_allowedInVar;
    private $_allowedInExpr;
    private $_allowedAssign;

    private $_pluginPath=array();
    private $_metaBody = '';

    private $_modifier = array('upper'=>'strtoupper', 'lower'=>'strtolower',
        'escxml'=>'htmlspecialchars', 'strip_tags'=>'strip_tags', 'escurl'=>'rawurlencode',
        'capitalize'=>'ucwords'
    );

    private $_blockStack=array();

    private $_sourceFile;
    private $_currentTag;

    function __construct(){
        $this->_allowedInVar = array_merge($this->_vartype, $this->_op);
        $this->_allowedInExpr = array_merge($this->_vartype, $this->_op);
        $this->_allowedAssign = array_merge($this->_vartype, $this->_assignOp, $this->_op);
#ifdef JTPL_STANDALONE
        require_once(JTPL_LOCALES_PATH.$GLOBALS['jTplConfig']['lang'].'.php');
        $this->_locales = $GLOBALS['jTplConfig']['locales'];
#endif
    }


    /**
     * compilation d'un template en fichier php
     */
#ifdef JTPL_STANDALONE
    public function compile($tplFile){
        $this->_sourceFile = $tplFile;
        $cachefile = JTPL_CACHE_PATH . basename($tplFile);

#else
    public function compile($selector){
        $this->_sourceFile = $selector->getPath();
        $cachefile = $selector->getCompiledFilePath();

        jContext::push($selector->module);
#endif

        if(!file_exists($this->_sourceFile)){
            $this->doError0('errors.tpl.not.found');
        }

        $tplcontent = file_get_contents ( $this->_sourceFile);

        preg_match_all("!{literal}(.*?){/literal}!s", $tplcontent, $_match);

        $this->_literals = $_match[1];

        $tplcontent = preg_replace("!{literal}(.*?){/literal}!s", '{literal}', $tplcontent);

        $result = preg_replace_callback("/{((.).*?)}/s", array($this,'_callback'), $tplcontent);

        $header ="<?php \n";
        foreach($this->_pluginPath as $path=>$ok){
            $header.=' require_once(\''.$path."');\n";
        }

#ifdef JTPL_STANDALONE
        $header.='function template_meta_'.md5($tplFile).'($t){';
#else
        $header.='function template_meta_'.md5($selector->module.'_'.$selector->resource).'($t){';
#endif
        $header .="\n".$this->_metaBody."\nreturn \$t->_meta;\n}\n";

#ifdef JTPL_STANDALONE
        $header.='function template_'.md5($tplFile).'($t){'."\n?>";
#else
        $header.='function template_'.md5($selector->module.'_'.$selector->resource).'($t){'."\n?>";
#endif
        $result = $header.$result."<?php \n}\n?>";

        $result = preg_replace('/\?>\n?<\?php/', '', $result);
        //$result = preg_replace('/<\?php\b+\? >/', '', $result);

#ifdef JTPL_STANDALONE
        $_dirname = dirname($cachefile);
        if (!@is_writable($_dirname)) {
            // cache_dir not writable, see if it exists
            if (!@is_dir($_dirname)) {
                trigger_error (sprintf($this->_locales['file.directory.notexists'], $_dirname), E_USER_ERROR);
                return false;
            }
            trigger_error (sprintf($this->_locales['file.directory.notwritable'], $cachefile, $_dirname), E_USER_ERROR);
            return false;
        }

        // write to tmp file, then rename it to avoid
        // file locking race condition
        $_tmp_file = tempnam($_dirname, 'wrt');

        if (!($fd = @fopen($_tmp_file, 'wb'))) {
            $_tmp_file = $_dirname . '/' . uniqid('wrt');
            if (!($fd = @fopen($_tmp_file, 'wb'))) {
                trigger_error(sprintf($this->_locales['file.write.error'], $cachefile, $_tmp_file), E_USER_ERROR);
                return false;
            }
        }

        fwrite($fd, $result);
        fclose($fd);

        // Delete the file if it allready exists (this is needed on Win,
        // because it cannot overwrite files with rename()
        if (preg_match("/^(\w+).*$/", PHP_OS, $m)) {
            $os=$m[1];
        } else {
            $os = PHP_OS;
        }
        $isWindows = (strpos(strtolower($os),'win')!== false);
        if ($isWindows && file_exists($cachefile)) {
            @unlink($cachefile);
        }

        @rename($_tmp_file, $cachefile);
        @chmod($cachefile, 0664);
#else
        $file = new jFile();
        $file->write($cachefile, $result);

        jContext::pop();
#endif
        return true;
    }

    /**
     * fonction appelée sur chaque balise de template {xxxx }
     */
    public function _callback($matches){
        list(,$tag, $firstcar) = $matches;

        // test du premier caractère
        if (!preg_match('/^\$|@|\*|[a-zA-Z\/]$/',$firstcar)) {
#ifdef JTPL_STANDALONE
            trigger_error(sprintf($this->_locales['errors.tpl.tag.syntax.invalid'], $tag, $this->_sourceFile),E_USER_ERROR);
#else
            trigger_error(jLocale::get('jelix~errors.tpl.tag.syntax.invalid',array($tag,$this->_sourceFile)),E_USER_ERROR);
#endif
            return '';
        }
        $this->_currentTag = $tag;
        if ($firstcar == '$' || $firstcar == '@') {
            return  '<?php echo '.$this->_parseVariable($tag).'; ?>';
        } elseif ($firstcar == '*') {
            return '';
        } else {
            if (!preg_match('/^(\/?[a-zA-Z0-9_]+)(?:(?:\s+(.*))|(?:\((.*)\)))?$/',$tag,$m)) {
#ifdef JTPL_STANDALONE
                trigger_error(sprintf($this->_locales['errors.tpl.tag.function.invalid'], $tag, $this->_sourceFile),E_USER_ERROR);
#else
                trigger_error(jLocale::get('jelix~errors.tpl.tag.function.invalid',array($tag,$this->_sourceFile)),E_USER_ERROR);
#endif
                return '';
            }
            if(count($m) == 4){
                $m[2] = $m[3];
            }
            if(!isset($m[2])) $m[2]='';

            return '<?php '.$this->_parseFunction($m[1],$m[2]).'?>';
        }
    }

    /**
    * analyse une balise qui commence par $ ou @, indiquant donc un affichage de variable
    *
    */
    private function _parseVariable($expr){
        $tok = explode('|',$expr);
        $res = $this->_parseFinal(array_shift($tok),$this->_allowedInVar);

        foreach($tok as $modifier){
            if(!preg_match('/^(\w+)(?:\:(.*))?$/',$modifier,$m)){
                $this->doError2('errors.tpl.tag.modifier.invalid',$this->_currentTag, $modifier);
                return '';
            }

            $targs=array($res);

            if( ! $path = $this->_getPlugin('modifier',$m[1])){
                if(isset($this->_modifier[$m[1]])){
                    $res = $this->_modifier[$m[1]].'('.$res.')';
                } else {
                    $this->doError2('errors.tpl.tag.modifier.unknow',$this->_currentTag, $m[1]);
                    return '';
                }
            } else {
                if(isset($m[2])){
                    $args = explode(':',$m[2]);

                    foreach($args as $arg){
                        $targs[] = $this->_parseFinal($arg,$this->_allowedInVar);
                    }
                }
                $res = 'jtpl_modifier_'.$m[1].'('.implode(',',$targs).')';
                $this->_pluginPath[$path] = true;
            }
        }
        return $res;
    }

    /**
     * analyse les balises ayant un nom "normal" (ne commençant pas par $ ou @)
     */
    private function _parseFunction($name,$args){
        $res='';
        switch($name) {
            case 'if':
                $res = 'if('.$this->_parseFinal($args,$this->_allowedInExpr).'):';
                array_push($this->_blockStack,'if');
                break;
            case 'else':
                if (end($this->_blockStack) !='if') {
                    $this->doError1('errors.tpl.tag.block.end.missing', end($this->_blockStack));
                }else
                    $res = 'else:';
                break;
            case 'elseif':
                if (end($this->_blockStack) !='if') {
                    $this->doError1('errors.tpl.tag.block.end.missing', end($this->_blockStack));
                }else
                    $res = 'elseif('.$this->_parseFinal($args,$this->_allowedInExpr).'):';
                break;
            case 'foreach':
                $res = 'foreach('.$this->_parseFinal($args,array(T_AS, T_DOUBLE_ARROW,T_STRING, T_OBJECT_OPERATOR), array(';','!')).'):';
                array_push($this->_blockStack,'foreach');
                break;
            case 'while':
                $res = 'while('.$this->_parseFinal($args,$this->_allowedInExpr).'):';
                array_push($this->_blockStack,'while');
                break;
            case 'for':
                $res = 'for('. $this->_parseFinal($args, $this->_allowedInExpr, array()) .'):';
                array_push($this->_blockStack,'for');
                break;

            case '/foreach':
            case '/for':
            case '/if':
            case '/while':
                $short = substr($name,1);
                if (end($this->_blockStack) !=$short) {
                    $this->doError1('errors.tpl.tag.block.end.missing', end($this->_blockStack));
                 }else{
                    array_pop($this->_blockStack);
                    $res='end'.$short.';';
                 }
                break;

            case 'assign':
                $res = $this->_parseFinal($args,$this->_allowedAssign);
                break;
            case 'ldelim': $res ='{'; break;
            case 'rdelim': $res ='}'; break;
            case 'literal':
                if (count($this->_literals)) {
                    $res = '?>'.array_shift($this->_literals).'<?php ';
                } else {
                    $this->doError1('errors.tpl.tag.block.end.missing','literal');
                }
                break;
            case '/literal':
                $this->doError1('errors.tpl.tag.block.begin.missing','literal');
                break;
            case 'meta':
                $this->_parseMeta($args);
                $res='';
                break;
            default:
                if(preg_match('!^/(\w+)$!',$name,$m)){
                    if (end($this->_blockStack) !=$m[1]) {
                        $this->doError1('errors.tpl.tag.block.end.missing',end($this->_blockStack));
                    }else{
                        array_pop($this->_blockStack);
                        $fct = 'jtpl_block_'.$m[1];
                        if(!function_exists($fct)){
                            $this->doError1('errors.tpl.tag.block.begin.missing',$m[1]);
                        }else
                            $res = $fct($this,false,null);
                    }
                }else if(preg_match('/^meta_(\w+)$/',$name,$m)){
                     if ( ! $path = $this->_getPlugin('meta',$m[1])) {
                        $this->doError1('errors.tpl.tag.meta.unknow',$m[1]);
                    }else{
                        $this->_parseMeta($args,$m[1]);
                        $this->_pluginPath[$path] = true;
                    }
                    $res='';

                }else if ( $path = $this->_getPlugin('block',$name)) {
                    require_once($path);
                    $argfct=$this->_parseFinal($args,$this->_allowedAssign, array(';'),true);
                    $fct = 'jtpl_block_'.$name;
                    $res = $fct($this,true,$argfct);
                    array_push($this->_blockStack,$name);

                }else if ( $path = $this->_getPlugin('function',$name)) {

                    $argfct=$this->_parseFinal($args,$this->_allowedAssign);
                    $res = 'jtpl_function_'.$name.'( $t'.(trim($argfct)!=''?','.$argfct:'').');';
                    $this->_pluginPath[$path] = true;

                } else {
                    $this->doError1('errors.tpl.tag.function.unknow',$name);
                }
        }
        return $res;
    }


    /**
     * analyse les arguments de balise (tout ce qui est aprés le nom d'une balise)
     * ou les arguments d'un modificateur, et le transforme en code php
     */
    private function _parseFinal($string, $allowed=array(), $exceptchar=array(';'), $splitArgIntoArray=false){
        $tokens = token_get_all('<?php '.$string.'?>');

        $results=array();
        $result ='';
        $first = true;
        $inLocale = false;
        $locale='';
        $bracketcount=$sqbracketcount=0;
        $firstok = array_shift($tokens);

        // il y a un bug, parfois le premier token n'est pas T_OPEN_TAG...
        if ($firstok== '<' && $tokens[0] == '?' && is_array($tokens[1])
            && $tokens[1][0] == T_STRING && $tokens[1][1] == 'php') {
            array_shift($tokens);
            array_shift($tokens);
        }

        foreach($tokens as $tok) {
            if (is_array($tok)) {
                list($type,$str)= $tok;
                $first=false;
                if ($type== T_CLOSE_TAG) {
                    continue;
                }

                if($type == T_STRING && $inLocale){
                    $locale.=$str;
                }elseif($type == T_VARIABLE && $inLocale){
                    $locale.='\'.$t->_vars[\''.substr($str,1).'\'].\'';
                }elseif($type == T_VARIABLE){
                    $result.='$t->_vars[\''.substr($str,1).'\']';
                }elseif($type == T_WHITESPACE || in_array($type, $allowed)){
                    $result.=$str;
                }else{
                    $this->doError2('errors.tpl.tag.phpsyntax.invalid', $this->_currentTag, $str);
                    return '';
                }
            } else {
                if ($tok == '@') {
                    if ($inLocale) {
                        $inLocale = false;
                        if ($locale=='') {
                            $this->doError1('errors.tpl.tag.locale.invalid', $this->_currentTag);
                            return '';
                        } else {
#ifdef JTPL_STANDALONE
                            $result.='${$GLOBALS[\'jTplConfig\'][\'localesGetter\']}(\''.$locale.'\')';
#else
                            $result.='jLocale::get(\''.$locale.'\')';
#endif
                            $locale='';
                        }
                    } else {
                        $inLocale=true;
                    }
                } elseif ($inLocale && ($tok=='.' || $tok =='~') ) {
                    $locale.=$tok;
                } elseif ($inLocale || in_array($tok,$exceptchar) || ($first && $tok !='!')) {
                    $this->doError2('errors.tpl.tag.character.invalid', $this->_currentTag, $tok);
                    return '';
                } elseif ($tok =='(') {
                    $bracketcount++;$result.=$tok;
                } elseif ($tok ==')') {
                    $bracketcount--;$result.=$tok;
                } elseif ($tok =='[') {
                    $sqbracketcount++;$result.=$tok;
                } elseif ($tok ==']') {
                    $sqbracketcount--;$result.=$tok;
                } elseif( $splitArgIntoArray && $tok ==',' && $bracketcount==0 && $sqbracketcount==0){
                   $results[]=$result;
                   $result='';
                } else {
                    $result.=$tok;
                }
                $first=false;
            }

        }

        if ($bracketcount != 0 || $sqbracketcount !=0) {
            $this->doError1('errors.tpl.tag.bracket.error', $this->_currentTag);
        }

        if( $splitArgIntoArray){
            $results[]=$result;
            return $results;
        }else{
            return $result;
        }
    }

    private function _parseMeta($args, $fct=''){
        if(preg_match("/^(\w+)\s+(.*)$/",$args,$m)){
            $argfct=$this->_parseFinal($m[2],$this->_allowedInExpr);
            if($fct!=''){
                $this->_metaBody.= 'jtpl_meta_'.$fct.'( $t,'."'".$m[1]."',".$argfct.");\n";
            }else{
                $this->_metaBody.= "\$t->_meta['".$m[1]."']=".$argfct.";\n";
            }
        }else{
            $this->doError1('errors.tpl.tag.meta.invalid', $this->_currentTag);
        }
    }

    /**
     * Récupère un plugin 
     */
    private function _getPlugin($type, $name){
#ifdef JTPL_STANDALONE
        $treq = 'html';
#else
        global $gJCoord, $gJConfig;
        $treq = $gJCoord->response->getFormatType();
#endif
        $foundPath='';

#ifdef JTPL_STANDALONE
        if(isset($GLOBALS['jTplConfig']['tplpluginsPathList'][$treq])){
            foreach($GLOBALS['jTplConfig']['tplpluginsPathList'][$treq] as $path){
#else
        if(isset($gJConfig->{'_tplpluginsPathList_'.$treq})){
            foreach($gJConfig->{'_tplpluginsPathList_'.$treq} as $path){
#endif
                $foundPath=$path.$type.'.'.$name.'.php';

                if(file_exists($foundPath)){
                    return $foundPath;
                }
            }
        }
#ifdef JTPL_STANDALONE
        if(isset($GLOBALS['jTplConfig']['tplpluginsPathList']['common'])){
            foreach($GLOBALS['jTplConfig']['tplpluginsPathList']['common'] as $path){
#else
        if(isset($gJConfig->_tplpluginsPathList_common)){
            foreach($gJConfig->_tplpluginsPathList_common as $path){
#endif
                $foundPath=$path.$type.'.'.$name.'.php';
                if(file_exists($foundPath)){
                    return $foundPath;
                }
            }
        }
        return '';
    }

    public function doError0($err){
#ifdef JTPL_STANDALONE
        trigger_error(sprintf($this->_locales[$err], $this->_sourceFile),E_USER_ERROR);
#else
        trigger_error(jLocale::get('jelix~'.$err,array($this->_sourceFile)),E_USER_ERROR);
#endif
    }

    public function doError1($err, $arg){
#ifdef JTPL_STANDALONE
        trigger_error(sprintf($this->_locales[$err], $arg, $this->_sourceFile),E_USER_ERROR);
#else
        trigger_error(jLocale::get('jelix~'.$err,array($arg, $this->_sourceFile)),E_USER_ERROR);
#endif
    }

    public function doError2($err, $arg1, $arg2){
#ifdef JTPL_STANDALONE
        trigger_error(sprintf($this->_locales[$err], $arg1, $arg2, $this->_sourceFile),E_USER_ERROR);
#else
        trigger_error(jLocale::get('jelix~'.$err,array($arg1, $arg2, $this->_sourceFile)),E_USER_ERROR);
#endif
    }

}



#ifdef DEBUGJTPL

function showtokens($arr){

echo '<table border="1" style="font-size:0.7em">';
foreach($arr as $tok){

   if(is_array($tok)){
      echo '<tr><td>',token_name($tok[0]), '</td><td>',htmlspecialchars($tok[1]),"</td></tr>\n";
   }else
      echo '<tr><td colspan="2">',$tok, "</td></tr>\n";

}
echo '</table><hr/>';

}

function showtoken($tok){

echo '<table border="1" style="font-size:0.7em">';
   if(is_array($tok)){
      echo '<tr><td>',token_name($tok[0]), '</td><td>',htmlspecialchars($tok[1]),"</td></tr>\n";
   }else
      echo '<tr><td colspan="2">',$tok, "</td></tr>\n";
echo '</table><hr/>';

}

#endif

?>
