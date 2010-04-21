<?php
/**
* @package     jBuildTools
* @author      Laurent Jouanneau
* @contributor Kévin Lepeltier
* @copyright   2006-2009 Laurent Jouanneau
* @copyright   2008 Kévin Lepeltier
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

require_once(dirname(__FILE__).'/preprocessor.lib.php');
require_once(dirname(__FILE__).'/jBuildUtils.lib.php');
require_once(dirname(__FILE__).'/class.JavaScriptPacker.php');

/**
 * jManifest copy files indicated in a 'manifest' file, to a specific directory
 * in order to generate a set of PHP files ready to be executed. It can do
 * pre-processing on these files during the copying, strip comments and
 * compress whitespaces, so sources will take less disk spaces and it will
 * improve performances a bit.
 *
 * jManifest supports also VCS like Subversion or Mercurial, so when it detect
 * that new files are added, it will call the VCS to add these files in the repository.
 */
class jManifest {

    /**
     * @var boolean true if you want to strip comment and compress whitespaces
     */
    static public $stripComment = false;
    
    /**
     * @var boolean true if you want more messages during the copy
    */
    static public $verbose = false;

    /**
     * @var string  the name of the vcs to use. 'svn' for Subversion, 'hg' for mercurial. '' for no support.
     */
    static public $usedVcs = ''; 

    static public $sourcePropertiesFilesDefaultCharset = 'utf-8';
    
    static public $targetPropertiesFilesCharset = 'utf-8';
    
    /**
     * when compressing whitespaces, jManifest will replace indentation made with spaces
     * by a tab character.
     * @var integer  the number of spaces for indentation used in your sources
     */
    static public $indentation = 4;

    /**
     * read the given manifest file and copy files
     * @param string $ficlist manifest file name
     * @param string $sourcepath main directory where it reads files
     * @param string $distpath main directory were files are copied
     */
    static public function process($ficlist, $sourcepath, $distpath, $preprocvars){

        $stripcomment = self::$stripComment;
        $verbose = self::$verbose;

        $sourcedir = jBuildUtils::normalizeDir($sourcepath);
        $distdir =  jBuildUtils::normalizeDir($distpath);

        $script = file($ficlist);

        $currentdestdir = '';
        $currentsrcdir = '';
        $preproc = new jPreProcessor();

        foreach($script as $nbline=>$line){
            $nbline++;
            if(preg_match(';^(cd|sd|dd|\*|!|\*!|c|\*c|cch)?\s+([a-zA-Z0-9\/.\-_]+)\s*(?:\(([a-zA-Z0-9\%\/.\-_]*)\))?\s*$;m', $line, $m)){
                if($m[1] == 'dd'){
                    $currentdestdir = jBuildUtils::normalizeDir($m[2]);
                    jBuildUtils::createDir($distdir.$currentdestdir, self::$usedVcs);
                }elseif($m[1] == 'sd'){
                    $currentsrcdir = jBuildUtils::normalizeDir($m[2]);
                }elseif($m[1] == 'cd'){
                    $currentsrcdir = jBuildUtils::normalizeDir($m[2]);
                    $currentdestdir = jBuildUtils::normalizeDir($m[2]);
                    jBuildUtils::createDir($distdir.$currentdestdir, self::$usedVcs);
                }else{
                    $doPreprocessing = (strpos($m[1],'*') !== false);
                    $doCompression = (strpos($m[1],'c') !== false && $m[1] != 'cch') || ($stripcomment && (strpos($m[1],'!') === false));

                    if($m[2] == ''){
                        throw new Exception ( "$ficlist : file required on line $nbline \n");
                    }
                    if(!isset($m[3]) || $m[3]=='')
                        $m[3]=$m[2];

                    $destfile = $distdir.$currentdestdir.$m[3];
                    $sourcefile = $sourcedir.$currentsrcdir.$m[2];

                    $addIntoRepo = !file_exists($destfile);

                    if($doPreprocessing){
                        if($verbose){
                            echo "process  $sourcefile \tto\t$destfile \n";
                        }
                        $preproc->setVars($preprocvars);
                        try{
                            $contents = $preproc->parseFile($sourcefile);
                        }catch(Exception $e){
                            throw new Exception ( "$ficlist : line $nbline, cannot process file ".$m[2]." (". $e .")\n");
                        }
                        if($doCompression) {
                            if( preg_match("/\.php$/",$destfile)) {
                                if($verbose) echo "     strip php comment ";
                                $contents = self::stripPhpComments($contents);
                                if($verbose) echo "OK\n";
                            }
                            else if(preg_match("/\.js$/",$destfile)) {
                                if($verbose) echo "compress javascript file \n";
                                $packer = new JavaScriptPacker($contents, 0, true, false);
                                $contents = $packer->pack();
                            }
                        }
                        file_put_contents($destfile,$contents);

                    }elseif($doCompression && preg_match("/\.php$/",$destfile)){
                        if($verbose)
                            echo "strip comment in  $sourcefile\tto\t".$destfile."\n";
                        $src = file_get_contents($sourcefile);
                        file_put_contents($destfile,self::stripPhpComments($src));

                    }elseif($doCompression && preg_match("/\.js$/",$destfile)) {
                        if($verbose)
                            echo "compress javascript file ".$destfile."\n";

                        $script = file_get_contents($sourcefile);
                        $packer = new JavaScriptPacker($script, 0, true, false);
                        file_put_contents($destfile, $packer->pack());

                    }elseif($m[1] == 'cch') {

                        if(strpos($m[3], '%charset%') === false) {
                            throw new Exception ( "$ficlist : line $nbline, dest file ".$m[3]." doesn't contains %charset% pattern.\n");
                        }

                        if($verbose)
                            echo "convert charset\tsources\t".$sourcedir.$currentsrcdir.$m[2]."   ".$m[3]."\n";

                        $encoding = preg_split('/[\s,]+/', self::$targetPropertiesFilesCharset);

                        $content = file_get_contents( $sourcefile );
                        if (self::$sourcePropertiesFilesDefaultCharset != '')
                            $encode = self::$sourcePropertiesFilesDefaultCharset;
                        else
                            $encode = mb_detect_encoding($content);

                        foreach ( $encoding as $val ) {
                            $encodefile = str_replace('%charset%', $val, $destfile);
                            if($verbose)
                                echo "\tencode into ".$encodefile."\n";
                            $addIntoRepo = !file_exists($encodefile);
                            $file = fopen($encodefile, "w");
                            fwrite($file, mb_convert_encoding($content, $val, $encode));
                            fclose($file);
                            if ($addIntoRepo) {
                                $d = getcwd();
                                chdir(dirname($encodefile));
                                if (self::$usedVcs == 'svn') {
                                    exec("svn add $encodefile");
                                }
                                else if (self::$usedVcs  == 'hg') {
                                    exec("hg add $encodefile");
                                }
                                chdir($d);
                            }
                        }
                        $addIntoRepo = false;
                    }else{
                        if($verbose)
                            echo "copy  ".$sourcedir.$currentsrcdir.$m[2]."\tto\t".$destfile."\n";

                        if(!copy($sourcefile, $destfile)){
                            throw new Exception ( "$ficlist : cannot copy file ".$m[2].", line $nbline \n");
                        }
                    }

                    if ($addIntoRepo) {
                        $d = getcwd();
                        chdir(dirname($destfile));

                        if (self::$usedVcs == 'svn') {
                            exec("svn add $destfile");
                        }
                        else if (self::$usedVcs  == 'hg') {
                            exec("hg add $destfile");
                        }
                        chdir($d);
                    }
                }
            }elseif(preg_match("!^\s*(\#.*)?$!",$line)){
                // we ignore comments
            }else{
                throw new Exception ( "$ficlist : syntax error on line $nbline \n");
            }
        }
    }

    static protected function stripPhpComments($content){

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
                            $result.=self::strip_ws($currentWhitespace, $canRemoveNextSpaces);
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
    
    /**
     * delete files indicated in the given manifest file, from the indicated target
     * directory.
     * @param string $ficlist manifest file name
     * @param string $distpath directory were files are copied
     */
    static public function removeFiles($ficlist, $distpath) {

        $distdir =  jBuildUtils::normalizeDir($distpath);

        $script = file($ficlist);

        $currentdestdir = '';
        $preproc = new jPreProcessor();

        foreach($script as $nbline=>$line){
            $nbline++;
            if (preg_match(';^(cd|rmd)?\s+([a-zA-Z0-9\/.\-_]+)\s*$;m', $line, $m)) {
                if($m[1] == 'rmd'){
                    $currentdestdir = jBuildUtils::normalizeDir($m[2]);
                    jBuildUtils::removeDir($distdir.$currentdestdir, self::$usedVcs);
                }
                elseif($m[1] == 'cd') {
                    $currentdestdir = jBuildUtils::normalizeDir($m[2]);
                }
                else {

                    if($m[2] == ''){
                        throw new Exception ( "$ficlist : file required on line $nbline \n");
                    }

                    $destfile = $distdir.$currentdestdir.$m[2];
                    if (!file_exists($destfile)) {
                        if (self::$verbose)
                            echo "cannot remove $destfile. It doesn't exist.\n";
                        continue;
                    }
                    if(self::$verbose)
                        echo "remove  ".$destfile."\n";
                    switch(self::$usedVcs) {
                        case '':
                        case 'rm':
                            if (!unlink($destfile))
                                throw new Exception ( " $ficlist: cannot remove file ".$m[2].", line $nbline \n");
                            break;
                        case 'svn':
                            $d = getcwd();
                            chdir(dirname($destfile));
                            exec("svn remove $destfile");
                            chdir($d);
                            break;
                        case 'hg':
                            $d = getcwd();
                            chdir(dirname($destfile));
                            exec("hg remove $destfile");
                            chdir($d);
                            break;
                    }
                }
            }elseif(preg_match("!^\s*(\#.*)?$!",$line)){
                // we ignore comments
            }else{
                throw new Exception ( "$ficlist : syntax error on line $nbline \n");
            }
        }
    }
}
