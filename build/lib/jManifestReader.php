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

class jManifestReader {
    protected $ficlist;
    protected $fs;
    
    protected $preproc;
    protected $preprocvars;

    protected $verbose = false;
    protected $stripComment = false;
    protected $sourceCharset = '';
    protected $targetCharset = '';
    protected $indentation = 4;

    protected $sourceDir;
    protected $distdir;

    function __construct($ficlist, $sourcepath, $distpath) {
        $this->ficlist = $ficlist;
        $this->fs = jManifest::getFileSystem($distpath);
        $this->preproc = new jPreProcessor();

        $this->sourcedir = jBuildUtils::normalizeDir($sourcepath);
        $this->distdir =  jBuildUtils::normalizeDir($distpath);

    }

    public function setVerbose($verbose) {
        $this->verbose = $verbose;
    }

    public function setStripComment($stripcomment) {
        $this->stripComment = $stripcomment;
    }

    public function setSourceCharset($charset) {
        $this->sourceCharset = $charset;
    }

    public function setTargetCharset($charset) {
        $this->targetCharset = $charset;
    }

    public function setIndentation($indent) {
        $this->indentation = $indent;
    }

    public function process($preprocvars, $preprocmanifest=false) {
        $this->preprocvars = $preprocvars;
        if ($preprocmanifest) {
            $this->preproc->setVars($preprocvars);
            try{
                $content = $this->preproc->parseFile($this->ficlist);
            }
            catch(Exception $e){
                throw new Exception ( "cannot preprocess the manifest file ".$this->ficlist." (". $e .")\n");
            }
            $script = explode("\n", $content);
        }
        else {
            $script = file($this->ficlist);
        }

        $currentdestdir = '';
        $currentsrcdir = '';

        foreach($script as $nbline=>$line){
            $nbline++;
            if(preg_match(';^(cd|sd|dd|\*|!|\*!|c|\*c|cch)?\s+([a-zA-Z0-9\/.\-_]+)\s*(?:\(([a-zA-Z0-9\%\/.\-_]*)\))?\s*$;m', $line, $m)){
                if($m[1] == 'dd'){
                    // set destination dir
                    $currentdestdir = jBuildUtils::normalizeDir($m[2]);
                    $this->fs->createDir($currentdestdir);
                }elseif($m[1] == 'sd'){
                    // set source dir
                    $currentsrcdir = jBuildUtils::normalizeDir($m[2]);
                }elseif($m[1] == 'cd'){
                    // set source dir and destination dir (same sub path)
                    $currentsrcdir = jBuildUtils::normalizeDir($m[2]);
                    $currentdestdir = jBuildUtils::normalizeDir($m[2]);
                    $this->fs->createDir($currentdestdir);
                }else{
                    // copy a file

                    // should we do processing on the file?
                    $doPreprocessing = (strpos($m[1],'*') !== false);
                    // should we compress files or generate encoded files?
                    $doCompression = (strpos($m[1],'c') !== false && $m[1] != 'cch') || ($this->stripComment && (strpos($m[1],'!') === false));

                    if ($m[2] == '') {
                        throw new Exception ( $this->ficlist.": file required on line $nbline \n");
                    }
                    if (!isset($m[3]) || $m[3]=='') {
                        $m[3]=$m[2];
                    }

                    if ($m[2] == '__ALL__') {
                        $dir = new DirectoryIterator($this->sourcedir.$currentsrcdir);
                        foreach ($dir as $dirContent) {
                            if (!$dirContent->isFile()) {
                                continue;
                            }
                            $m[2] = $m[3] = $dirContent->getFileName();
                            $destfile = $currentdestdir.$m[3];
                            $sourcefile = $this->sourcedir.$currentsrcdir.$m[2];
        
                            $this->processFile($sourcefile, $destfile, $nbline, $m, $doPreprocessing, $doCompression);
                        }
                    }
                    else {
                        $destfile = $currentdestdir.$m[3];
                        $sourcefile = $this->sourcedir.$currentsrcdir.$m[2];
    
                        $this->processFile($sourcefile, $destfile, $nbline, $m, $doPreprocessing, $doCompression);
                    }
                }
            }
            elseif(preg_match("!^\s*(\#.*)?$!",$line)){
                // we ignore comments
            }
            else{
                throw new Exception ( $this->ficlist.": syntax error on line $nbline \n");
            }
        }
    }


    protected function processFile($sourcefile, $destfile, $nbline, $m, $doPreprocessing, $doCompression) {

        if ($doPreprocessing) {
            if ($this->verbose){
                echo "process  $sourcefile \tto\t".$this->distdir.$destfile." \n";
            }

            $this->preproc->setVars($this->preprocvars);

            try {
                $contents = $this->preproc->parseFile($sourcefile);
            }
            catch(Exception $e) {
                throw new Exception ( $this->ficlist.": line $nbline, cannot process file ".$m[2]." (". $e .")\n");
            }

            if ($doCompression) {
                if (preg_match("/\.php$/", $destfile)) {
                    if ($this->verbose) {
                        echo "     strip php comment ";
                    }
                    $contents = jPhpCommentsRemover::stripComments($contents, $this->indentation);
                    if ($this->verbose) {
                        echo "OK\n";
                    }
                }
                else if (preg_match("/\.js$/", $destfile)) {
                    if ($this->verbose) {
                        echo "compress javascript file \n";
                    }
                    $packer = new JavaScriptPacker($contents, 0, true, false);
                    $contents = $packer->pack();
                }
            }
            $this->fs->setFileContent($destfile, $contents);

        }
        elseif ($doCompression && preg_match("/\.php$/",$destfile)) {
            if ($this->verbose) {
                echo "strip comment in  $sourcefile\tto\t".$this->distdir.$destfile."\n";
            }
            $src = file_get_contents($sourcefile);
            $this->fs->setFileContent($destfile, jPhpCommentsRemover::stripComments($src, $this->indentation));

        }
        elseif ($doCompression && preg_match("/\.js$/",$destfile)) {
            if ($this->verbose) {
                echo "compress javascript file ".$destfile."\n";
            }

            $script = file_get_contents($sourcefile);
            $packer = new JavaScriptPacker($script, 0, true, false);
            $this->fs->setFileContent($destfile, $packer->pack());

        }
        elseif($m[1] == 'cch') {

            if (strpos($m[3], '%charset%') === false) {
                throw new Exception ( $this->ficlist.": line $nbline, dest file ".$m[3]." doesn't contains %charset% pattern.\n");
            }

            if ($this->verbose) {
                echo "convert charset\tsources\t".$sourcefile."   ".$m[3]."\n";
            }

            $encoding = preg_split('/[\s,]+/', $this->targetCharset);

            $content = file_get_contents( $sourcefile );
            if ($this->sourceCharset != '') {
                $encode = $this->sourceCharset;
            }
            else {
                $encode = mb_detect_encoding($content);
            }

            foreach ( $encoding as $val ) {
                $encodefile = str_replace('%charset%', $val, $destfile);
                if ($this->verbose) {
                    echo "\tencode into ".$encodefile."\n";
                }
                $this->fs->setFileContent($encodefile,  mb_convert_encoding($content, $val, $encode));
            }
        }else{
            if ($this->verbose) {
                echo "copy  ".$sourcefile."\tto\t".$this->distdir.$destfile."\n";
            }

            if (!$this->fs->copyFile($sourcefile, $destfile)) {
                throw new Exception ( $this->ficlist.": cannot copy file ".$m[2].", line $nbline \n");
            }
        }
    }
}