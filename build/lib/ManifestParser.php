<?php



class ManifestParser {

    const COMMENT=1;
    const DIR=2;
    const FILE=3;
    const REMOVEDDIR=4;

    protected $file;

    protected $content;

    function __construct($file) {
        $this->file = $file;
    }

    function parse() {
        $script = file($this->file);
        $this->content = array();
        $currentDir = null;
        foreach($script as $nbline=>$line){
            if(preg_match(';^(cd|sd|dd|\*|!|\*!|c|\*c|cch|rmd)?(\s+)([a-zA-Z0-9\/.\-_]+)(\s*)(\([a-zA-Z0-9\%\/.\-_]*\))?\s*$;m', $line, $m)){
                if ($m[1] == 'cd' || $m[1] == 'sd') {
                    if ($currentDir) {
                        $this->content[] = $currentDir;
                    }
                    $currentDir = array(self::DIR, $line, trim($m[3],'/'), array());
                }
                else if ($m[1] == 'rmd') {
                    if ($currentDir) {
                        $this->content[] = $currentDir;
                    }
                    $currentDir = null;
                    $this->content[] = array(self::REMOVEDDIR, $line, $m[3]);
                }
                else {
                    $currentDir[3][] = array(self::FILE, $line, $m[3]);
                }
            }else {
                if ($currentDir) {
                    $currentDir[3][] = array(self::COMMENT, $line);
                }
                else {
                    $this->content[] = array(self::COMMENT, $line);
                }
            }
        }
        if ($currentDir) {
            $this->content[] = $currentDir;
        }
    }

    function save() {
        $content = '';
        foreach($this->content as $token) {
            $content.= $token[1];
            if ($token[0] == self::DIR){
                foreach($token[3] as $subtoken) {
                    $content.= $subtoken[1];
                }
            }
        }
        file_put_contents($this->file, $content);
    }
    
    function addFile($dir, $filename) {
        $dir = trim($dir,'/');
        $foundDir = false;
        foreach($this->content as $k=>$token) {
            if ($token[0] == self::DIR && $token[2] == $dir){
                $this->content[$k][3][] = array(self::FILE, '   '.$filename."\n", $filename);
                $foundDir = true;
                break;
            }
        }
        if (!$foundDir) {
            $currentDir = array(self::DIR, "cd $dir\n", $dir, array());
            $currentDir[3][] = array(self::FILE, '   '.$filename."\n", $filename);
            $this->content[] = $currentDir;
        }
    }

    function removeFile($dir, $filename) {
        $dir = trim($dir,'/');
        $foundDir = false;
        foreach($this->content as $k=>$token) {
            if ($token[0] == self::DIR && $token[2] == $dir){
                foreach($token[3] as $sk=>$subtoken) {
                    if ($subtoken[0] == self::FILE && $subtoken[2] == $filename) {
                        $this->content[$k][3][$sk] = array(self::COMMENT, '', '');
                        break;
                    }
                }
                break;
            }
        }
    }
}