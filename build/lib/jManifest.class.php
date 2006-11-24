<?php
/**
* @package     jBuildTools
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

require_once(dirname(__FILE__).'/preprocessor.lib.php');
require_once(dirname(__FILE__).'/jBuildUtils.lib.php');

class jManifest {

    /**
     * @param string $ficlist manifest file name
     * @param string $sourcepath directory where it reads files
     * @param string $distpath directory were files are copied
     */
    static public function process($ficlist, $sourcepath, $distpath, $preprocvars, $stripcomment=false, $verbose=false){

        $sourcedir = jBuildUtils::normalizeDir($sourcepath);
        $distdir =  jBuildUtils::normalizeDir($distpath);

        $script = file($ficlist);

        $currentdestdir = '';
        $currentsrcdir = '';
        $preproc = new jPreProcessor();

        foreach($script as $nbline=>$line){
            $nbline++;
            if(preg_match(';^(cd|sd|dd|\*|!|\*!)?\s+([a-zA-Z0-9\/.\-_]+)\s*(?:\(([a-zA-Z0-9\/.\-_]*)\))?\s*$;m', $line, $m)){
                if($m[1] == 'dd'){
                    $currentdestdir = jBuildUtils::normalizeDir($m[2]);
                    jBuildUtils::createDir($distdir.$currentdestdir);
                }elseif($m[1] == 'sd'){
                    $currentsrcdir = jBuildUtils::normalizeDir($m[2]);
                }elseif($m[1] == 'cd'){
                    $currentsrcdir = jBuildUtils::normalizeDir($m[2]);
                    $currentdestdir = jBuildUtils::normalizeDir($m[2]);
                    jBuildUtils::createDir($distdir.$currentdestdir);
                }else{
                    if($m[2] == ''){
                        throw new Exception ( "$ficlist : file required on line $nbline \n");
                    }
                    if(!isset($m[3]) || $m[3]=='')
                        $m[3]=$m[2];

                    $destfile = $distdir.$currentdestdir.$m[3];
                    $sourcefile = $sourcedir.$currentsrcdir.$m[2];

                    if($m[1]=='*' || $m[1]=='*!'){
                        if($verbose){
                            echo "process  $sourcefile \tto\t$destfile \n";
                        }
                        $preproc->setVars($preprocvars);
                        try{
                            $contents = $preproc->parseFile($sourcefile);
                        }catch(Exception $e){
                            throw new Exception ( "$ficlist : line $nbline, cannot process file ".$m[2]." (". $e->getMessage() .")\n");
                        }
                        if($m[1]=='*' && $stripcomment && preg_match("/\.php$/",$destfile)){
                            $contents = self::stripPhpComments($contents);
                        }
                        file_put_contents($destfile,$contents);

                    }elseif($m[1]!='!'&& $stripcomment && preg_match("/\.php$/",$destfile)){
                        if($verbose)
                            echo "strip comment in  $sourcefile\tto\t".$destfile."\n";
                        $src = file_get_contents($sourcefile);
                        file_put_contents($destfile,self::stripPhpComments($src));

                    }else{
                        if($verbose)
                            echo "copy  ".$sourcedir.$currentsrcdir.$m[2]."\tto\t".$destfile."\n";

                        if(!copy($sourcefile, $destfile)){
                            throw new Exception ( "$ficlist : cannot copy file ".$m[2].", line $nbline \n");
                        }
                    }
                }
            }elseif(preg_match("!^\s*(\#.*)?$!",$line)){
                // commentaire, on ignore
            }else{
                throw new Exception ( "$ficlist : syntax error on line $nbline \n");
            }
        }
    }

    static protected function stripPhpComments($content){

        $tokens = token_get_all($content);
        $result = '';
        $firstcomment= true;
        foreach ($tokens as $token) {
            if (is_string($token)) {
                $result.=$token;
            } else {
                switch ($token[0]) {
                    case T_COMMENT:
                        break;
                    case T_DOC_COMMENT:
                        // on garde le premier commentaire documentaire
                        if($firstcomment){
                            $result.=$token[1];
                            $firstcomment = false;
                        }
                        break;
                    default:
                        $result.=$token[1];
                        break;
                }
            }
        }
        return $result;

    }
}
?>