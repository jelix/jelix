<?php
/**
* @package    jelix
* @subpackage utils
* @author     Laurent Jouanneau
* @contributor
* @copyright  2008-2010 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
* utility class to modify an ini file by preserving comments, whitespace..
* It follows same behaviors of parse_ini_file, except when there are quotes
* inside values. it doesn't support quotes inside values, because parse_ini_file
* is totally bugged, depending cases.
* @package    jelix
* @subpackage utils
* @since 1.1
*/
class jIniFileModifier {

    /**
     * @const integer token type for whitespaces
     */
    const TK_WS = 0;
    /**
     * @const integer token type for a comment
     */
    const TK_COMMENT = 1;
    /**
     * @const integer token type for a section header
     */
    const TK_SECTION = 2;
    /**
     * @const integer token type for a simple value
     */
    const TK_VALUE = 3;
    /**
     * @const integer token type for a value of an array item 
     */
    const TK_ARR_VALUE = 4;

    /**
     * each item of this array contains data for a section. the key of the item
     * is the section name. There is a section with the key "0", and which contains
     * data for options which are not in a section.
     * each value of the items is an array of tokens. A token is an array with
     * some values. first value is the token type (see TK_* constants), and other
     * values depends of the token type:
     * - TK_WS: content of whitespaces
     * - TK_COMMENT: the comment
     * - TK_SECTION: the section name
     * - TK_VALUE: the name, and the value
     * - TK_ARRAY_VALUE: the name, the value, and the key
     * @var array
     */
    protected $content = array();

    /**
     * @var string the filename of the ini file
     */
    protected $filename = '';
    
    /**
     * @var boolean true if the content has been modified
     */
    protected $modified = false;

    /**
     * load the given ini file
     * @param string $filename the file to load
     */
    function __construct($filename) {
        if(!file_exists($filename) || !is_file($filename))
            throw new jException ('jelix~errors.file.notexists', $filename);
        $this->filename = $filename;
        $this->parse(preg_split("/(\r\n|\n|\r)/", file_get_contents($filename)));
    }
    
    /**
     * @return string the file name
     * @since 1.2
     */
    function getFileName() {
        return $this->filename;
    }

    /**
     * parsed the lines of the ini file
     */
    protected function parse($lines) {
        $this->content = array(0=>array());
        $currentSection=0;
        $multiline = false;
        $currentValue= null;
        
        $arrayContents = array();
        
        foreach ($lines as $num => $line) {
            if($multiline) {
                if(preg_match('/^(.*)"\s*$/', $line, $m)) {
                    $currentValue[2].=$m[1];
                    $multiline=false;
                    $this->content[$currentSection][]=$currentValue;
                } else {
                    $currentValue[2].=$m[1]."\n";
                }
            } else if(preg_match('/^\s*([a-z0-9_.-]+)(\[\])?\s*=\s*(")?([^"]*)(")?(\s*)/i', $line, $m)) {
                list($all, $name, $foundkey, $firstquote, $value ,$secondquote,$lastspace) = $m;

                if ($foundkey !='') {
                    if (isset($arrayContents[$currentSection][$name]))
                        $key = count($arrayContents[$currentSection][$name]);
                    else
                        $key = 0;
                    $currentValue = array(self::TK_ARR_VALUE, $name, $value, $key);
                    $arrayContents[$currentSection][$name][$key] = $value;
                }
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

            }else if(preg_match('/^(\s*\[([a-z0-9_.-@:]+)\]\s*)/i', $line, $m)) {
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
        $lastKey = 0; // last key in an array value
        if (isset($this->content[$section])) {
            // boolean to erase array values if the new value is not a new item for the array
            $deleteMode = false;
            foreach ($this->content[$section] as $k =>$item) {
                if ($deleteMode) {
                    if ($item[0] == self::TK_ARR_VALUE && $item[1] == $name)
                        $this->content[$section][$k] = array(self::TK_WS, '');
                    continue;
                }
                
                // if the item is not a value or an array value, or not the same name
                if (($item[0] != self::TK_VALUE && $item[0] != self::TK_ARR_VALUE)
                    || $item[1] != $name)
                    continue;
                // if it is an array value, and if the key doesn't correspond
                if ($item[0] == self::TK_ARR_VALUE && $key !== null) {
                    if($item[3] != $key) {
                        $lastKey = $item[3];
                        continue;
                    }
                }
                if ($key !== null) {
                    // we add the value as an array value
                    $this->content[$section][$k] = array(self::TK_ARR_VALUE,$name,$value, $key);
                } else {
                    // we store the value
                    $this->content[$section][$k] = array(self::TK_VALUE,$name,$value);
                    if ($item[0] == self::TK_ARR_VALUE) {
                        // the previous value was an array value, so we erase other array values
                        $deleteMode = true;
                        $foundValue = true;
                        continue;
                    }
                }
                $foundValue=true;
                break;
            }
        }
        else {
            $this->content[$section] = array(array(self::TK_SECTION, '['.$section.']'));
        }
        if (!$foundValue) {
            if($key === null) {
                $this->content[$section][]= array(self::TK_VALUE, $name, $value);
            } else {
                $this->content[$section][]= array(self::TK_ARR_VALUE, $name, $value, $lastKey);
            }
        }

        $this->modified = true;
    }



    /**
     * remove an option in the ini file. It can remove an entire section if you give
     * an empty value as $name, and a $section name
     * @param string $name    the name of the option to remove, or null to remove an entire section
     * @param string $section the section where to remove the value, or the section to remove
     * @param string $key     for option which is an item of array, the key in the array
     * @since 1.2
     */
    public function removeValue($name, $section=0, $key=null) {
        $foundValue=false;

        if ($section === 0 && $name == '')
            return;

        if ($name == '') {

            if ($section === 0 || !isset($this->content[$section]))
                return;
            unset($this->content[$section]);
            $this->modified = true;
            return;
        }
        
        if (isset($this->content[$section])) {
            // boolean to erase array values if the option to remove is an array
            $deleteMode = false;
            foreach ($this->content[$section] as $k =>$item) {
                if ($deleteMode) {
                    if ($item[0] == self::TK_ARR_VALUE && $item[1] == $name)
                        $this->content[$section][$k] = array(self::TK_WS, '');
                    continue;
                }
                
                // if the item is not a value or an array value, or not the same name
                if (($item[0] != self::TK_VALUE && $item[0] != self::TK_ARR_VALUE)
                    || $item[1] != $name)
                    continue;
                // if it is an array value, and if the key doesn't correspond
                if ($item[0] == self::TK_ARR_VALUE && $key !== null) {
                    if($item[3] != $key)
                        continue;
                }
                if ($key !== null) {
                    // we remove the value from the array
                    $this->content[$section][$k] = array(self::TK_WS, '');
                } else {
                    // we remove the value
                    $this->content[$section][$k] = array(self::TK_WS, '');
                    if ($item[0] == self::TK_ARR_VALUE) {
                        // the previous value was an array value, so we erase other array values
                        $deleteMode = true;
                        $foundValue = true;
                        continue;
                    }
                }
                $foundValue=true;
                break;
            }
        }

        $this->modified = true;
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
            if (($item[0] != self::TK_VALUE && $item[0] != self::TK_ARR_VALUE)
                || $item[1] != $name)
                continue;
            if ($item[0] == self::TK_ARR_VALUE && $key !== null){
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
        if ($this->modified) {
            file_put_contents($this->filename, $this->generateIni());
            $this->modified = false;
        }
    }

    /**
     * save the content in an new ini file
     * @param string $filename the name of the file
     */
    public function saveAs($filename) {
        file_put_contents($filename, $this->generateIni());
    }

    /**
     * says if the ini content has been modified
     * @return boolean
     * @since 1.2
     */
    public function isModified() {
        return $this->modified;
    }

    /**
     * return the list of section names
     * @return array
     * @since 1.2
     */
    public function getSectionList() {
        $list = array_keys($this->content);
        array_shift($list); // remove the global section
        return $list;
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
                        $content.=$item[1].'[]='.$this->getIniValue($item[2])."\n";
                    break;
                }
            }
        }
        return $content;
    }

    protected function getIniValue($value) {
        if ($value === '' || is_numeric(trim($value)) || (preg_match("/^[\w-.]*$/", $value) && strpos("\n",$value) === false) ) {
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

