<?php
/**
* @package    jelix
* @subpackage utils
* @version    $Id:$
* @author     Loic Mathaud
* @contributor
* @copyright  2006 Loic Mathaud
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
*
* @package    jelix
* @subpackage utils
*/
class jIniFile {

    public static function read($filename) {
        if ( file_exists ($filename) ) {
            return parse_ini_file($filename, true);
        } else {
            return false;
        }
    }
    
    public static function write($array, $filename) {
        $result='';
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $result.='['.$k."]\n";
                foreach($v as $k2 => $v2){
                    $result .= $k2.'='.self::_iniValue($v2)."\n";
                }
            } else {
                // on met les valeurs simples en debut de fichier
                $result = $k.'='.self::_iniValue($v)."\n".$result;
            }
        }

        if ($f = @fopen($filename, 'wb')) {
            fwrite($f, $result);
            fclose($f);
        } else {
            throw new jException('jelix~errors.inifile.write.error', array ($filename));
        }
    }
    
    /**
     * format a value to store in a ini file
     * @param string $value the value
     * @return string the formated value
     */
    static private function _iniValue($value){
        if ($value == '' || is_numeric($value) || preg_match("/^[\w]*$/", $value)) {
            return $value;
        } else {
            return '"'.$value.'"';
        }
    }
}

?>
