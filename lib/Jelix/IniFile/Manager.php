<?php
/**
* @author     Loic Mathaud
* @contributor Laurent Jouanneau
* @copyright  2006 Loic Mathaud, 2008-2014 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
namespace Jelix\IniFile;

/**
* utility class to read and write an ini file
*/
class Manager {

    /**
     * read an ini file
     * @param string $filename the path and the name of the file to read
     * @param boolean $asObject true if the content should be returned as an object
     * @return array the content of the file or false
     */
    public static function read($filename, $asObject = false) {
        if (file_exists ($filename)) {
            if ($asObject) {
                return (object) parse_ini_file($filename, true);
            }
            else {
                return parse_ini_file($filename, true);
            }
        }
        else {
            return false;
        }
    }

    /**
     * read an ini file and merge its parameters to the given object.
     * Useful to merge to config files.
     * Parameters whose name starts with a '_' are not merged.
     * @param string $filename the path and the name of the file to read
     * @param object $content
     * @return array the content of the file or false
     * @since 2.0
     */
    public static function readAndMergeObject($filename, $content) {
        if (!file_exists ($filename)) {
            return false;
        }

        $newContent = @parse_ini_file($filename, true);
        if ($newContent === false)
            return false;

        return self::mergeIniObjectContents($content, $newContent);
    }

    /**
     * merge two simple StdClass object
     * @param StdClass $baseContent  the object which receives new properties
     * @param StdClass $contentToImport  the object providing new properties
     */
    public static function mergeIniObjectContents($baseContent, $contentToImport) {
        $contentToImport = (array) $contentToImport;

        foreach ($contentToImport as $k=>$v) {
            if (!isset($baseContent->$k)) {
                $baseContent->$k = $v;
                continue;
            }

            if ($k[1] == '_')
                continue;
            if (is_array($v)) {
                $baseContent->$k = array_merge($baseContent->$k, $v);
            }
            else {
                $baseContent->$k = $v;
            }
        }
        return $baseContent;
    }
    
    
    /**
     * write some data in an ini file
     * the data array should follow the same structure returned by
     * the read method (or parse_ini_file)
     * @param array $array the content of an ini file
     * @param string $filename the path and the name of the file use to store the content
     * @param string $header   some content to insert at the begining of the file
     * @param integer $chmod   
     */
    public static function write($array, $filename, $header='', $chmod=null) {
        $result='';
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $result.='['.$k."]\n";
                foreach($v as $k2 => $v2){
                    $result .= self::_iniValue($k2,$v2);
                }
            } else {
                // we put simple values at the beginning of the file.
                $result = self::_iniValue($k,$v).$result;
            }
        }

        if ($f = @fopen($filename, 'wb')) {
            fwrite($f, $header.$result);
            fclose($f);
            if ($chmod) {
                chmod($f, $chmod);
            }
        }
        else {
            throw new \Exception('Error while writing ini file '.$filename, 24);
        }
    }

    /**
     * format a value to store in a ini file
     * @param string $value the value
     * @return string the formated value
     */
    static private function _iniValue($key, $value){
        if (is_array($value)) {
            $res = '';
            foreach($value as $v)
                $res.=self::_iniValue($key.'[]', $v);
            return $res;
        } else if ($value == ''
                  || is_numeric($value)
                  || (preg_match("/^[\w-.]*$/", $value) && strpos("\n",$value) === false)) {
            return $key.'='.$value."\n";
        } else if ($value === false) {
            return $key."=0\n";
        } else if ($value === true) {
            return $key."=1\n";
        } else {
            return $key.'="'.$value."\"\n";
        }
    }
}
