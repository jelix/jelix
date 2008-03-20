<?php
/**
* @package    jelix
* @subpackage utils
* @author     Laurent Jouanneau
* @contributor
* @copyright  2008 Laurent Jouanneau
* @link       http://www.jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
* utility class to modify an ini file by preserving comments, whitespace..
* @package    jelix
* @subpackage utils
* @since 1.1
*/
class jIniFileModifier {

    const TK_WS = 0;
    const TK_COMMENT = 1;
    const TK_SECTION = 2;
    const TK_VALUE = 3;
    const TK_ARR_VALUE = 4;

    protected $content = array();

    protected $filename = '';

    /**
     * load the given ini file
     * @param string $filename the file to load
     */
    function __construct($filename) {
        if(!file_exists($filename))
            throw new jException ('jelix~errors.file.notexists', $filename);
        $this->filename = $filename;
        $this->parse(file ($filename));
    }

    /**
     * parsed the lines of the ini file
     */
    protected function parse($lines) {
        $this->content = array(0=>array());
        $currentSection=0;
        $multiline = false;
        $currentValue= null;
        foreach ($lines as $num => $line) {
            if($multiline) {
                if(preg_match('/^(.*)"\s*$/', $line, $m)) {
                    $currentValue[2].=$m[1];
                    $multiline=false;
                    $this->content[$currentSection][]=$currentValue;
                } else {
                    $currentValue[2].=$m[1]."\n";
                }
            } else if(preg_match('/^\s*([a-z0-9_.-]+)(\[([a-z0-9_.-]*)\])?\s*=\s*(")?([^"]*)(")?(\s*)/i', $line, $m)) {
                list($all, $name, $foundkey, $key, $firstquote, $value ,$secondquote,$lastspace) = $m;

                if($foundkey !='') 
                    $currentValue = array(self::TK_ARR_VALUE, $name, $value, $key);
                else
                    $currentValue = array(self::TK_VALUE, $name, $value);

                if($firstquote == '"' && $secondquote == '') {
                    $multiline = true;
                    $currentValue[2].="\n";
                } else {
                    $this->content[$currentSection][]=$currentValue;
                }

            }else if(preg_match('/^(\s*;.*)$/',$line, $m)){
                $this->content[$currentSection][]=array(self::TK_COMMENT, $m[1]);

            }else if(preg_match('/^(\s*\[([a-z0-9_.-]+)\]\s*)/i', $line, $m)) {
                $currentSection = $m[2];
                $this->content[$currentSection]=array(
                    array(self::TK_SECTION, $m[1]),
                );

            }else  {
                $this->content[$currentSection][]=array(self::TK_WS, $line);
            }
        }
    }

    /**
     * modify an option in the ini file. If the option doesn't exist,
     * it is created.
     * @param string $name    the name of the option to modify
     * @param string $value   the new value
     * @param string $section the section where to set the item. 0 is the global section
     * @param string $key     for option which is an item of array, the key in the array
     */
    public function setValue($name, $value, $section=0, $key=null) {
        $foundValue=false;
        if(isset($this->content[$section])) {
            $deleteMode = false;
            foreach ($this->content[$section] as $k =>$item) {
                if($deleteMode) {
                    if( $item[0] == self::TK_ARR_VALUE && $item[1] == $name )
                        $this->content[$section][$k] = array(self::TK_WS,'');
                    continue;
                }
                if( ($item[0] != self::TK_VALUE && $item[0] != self::TK_ARR_VALUE)
                    || $item[1] != $name)
                    continue;
                if($item[0] == self::TK_ARR_VALUE && $key !== null){
                    if($item[3] != $key)
                        continue;
                }
                if($key !== null) {
                    $this->content[$section][$k]=array(self::TK_ARR_VALUE,$name,$value, $key);
                } else {
                    $this->content[$section][$k]=array(self::TK_VALUE,$name,$value);
                    if($item[0] == self::TK_ARR_VALUE) {
                        $deleteMode = true;
                        $foundValue = true;
                        continue;
                    }
                }
                $foundValue=true;
                break;
            }
        }else{
            $this->content[$section] = array(array(self::TK_SECTION, '['.$section.']'));
        }
        if(!$foundValue) {
            if($key === null) {
                $this->content[$section][]= array(self::TK_VALUE, $name, $value);
            } else {
                $this->content[$section][]= array(self::TK_ARR_VALUE, $name, $value, $key);
            }
        }
    }

    /**
     * return the value of an option in the ini file. If the option doesn't exist,
     * it returns null.
     * @param string $name    the name of the option to retrieve
     * @param string $section the section where the option is. 0 is the global section
     * @param string $key     for option which is an item of array, the key in the array
     * @return mixed the value
     */
    public function getValue($name, $section=0, $key=null) {
        if(!isset($this->content[$section])) {
            return null;
        }
        foreach ($this->content[$section] as $k =>$item) {
            if( ($item[0] != self::TK_VALUE && $item[0] != self::TK_ARR_VALUE)
                || $item[1] != $name)
                continue;
            if($item[0] == self::TK_ARR_VALUE && $key !== null){
                if($item[3] != $key)
                    continue;
            }

            if (preg_match('/^-?[0-9]$/', $item[2])) { 
                return intval($item[2]);
            }
            else if (preg_match('/^-?[0-9\.]$/', $item[2])) { 
                return floatval($item[2]);
            }
            else if (strtolower($item[2]) === 'true' || strtolower($item[2]) === 'on') {
                return true;
            }
            else if (strtolower($item[2]) === 'false' || strtolower($item[2]) === 'off') {
                return false;
            }
            return $item[2];
        }
        return null;
    }

    /**
     * save the ini file
     */
    public function save() {
        file_put_contents($this->filename, $this->generateIni());
    }

    /**
     * save the content in an new ini file
     * @param string $filename the name of the file
     */
    public function saveAs($filename) {
        file_put_contents($filename, $this->generateIni());
    }

    protected function generateIni() {
        $content = '';
        foreach($this->content as $sectionname=>$section) {
            foreach($section as $item) {
                switch($item[0]) {
                  case self::TK_SECTION:
                    if($item[1] != '0')
                        $content.=$item[1]."\n";
                    break;
                  case self::TK_COMMENT:
                  case self::TK_WS:
                    $content.=$item[1]."\n";
                    break;
                  case self::TK_VALUE:
                        $content.=$item[1].'='.$this->getIniValue($item[2])."\n";
                    break;
                  case self::TK_ARR_VALUE:
                        $content.=$item[1].'['.$item[3].']='.$this->getIniValue($item[2])."\n";
                    break;
                }
            }
        }
        return $content;
    }

    protected function getIniValue($value) {
        if ($value === '' || is_numeric($value) || preg_match("/^[\w]*$/", $value) || strpos("\n",$value) === false ) {
            return $value;
        }else if($value === false) {
            $value="0";
        }else if($value === true) {
            $value="1";
        }else {
            $value='"'.$value.'"';
        }
        return $value;
    }
}

?>