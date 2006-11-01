<?php
/**
* @package     jelix
* @subpackage  utils
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 *
 * @package     jelix
 * @subpackage  utils
 */
class jFilter {

    private function _construct() {}

    static public function usePhpFilter(){
#ifdef PHP52
        return true;
#else
        return false;
#endif
    }

    /**
     * check if the given value is an integer
     * @param string $val the value
     * @param int $min minimum value (optional), null if no minimum
     * @param int $max maximum value (optional), null if no maximum
     * @return boolean true if it is valid
     */
    static public function isInt ($val, $min=null, $max=null){
#ifdef PHP52
        // @FIXME pas de doc sur la façon d'utiliser les min/max sur les filters
        if(!filter_var($var, FILTER_VALIDATE_INT)) return false;
#else
        // @FIXME : trouver une solution plus performante ?
        if(!preg_match("/^\\-?\d+$/", $val)) return false;
#endif
        if($min !== null && intval($val) < $min) return false;
        if($max !== null && intval($val) > $max) return false;
        return true;
    }

    /**
     * check if the given value is an hexadecimal integer
     * @param string $val the value
     * @param int $min minimum value (optional), null if no minimum
     * @param int $max maximum value (optional), null if no maximum
     * @return boolean true if it is valid
     */
    static public function isHexInt ($val, $min=null, $max=null){
#ifdef PHP52
        // @FIXME pas de doc sur la façon d'utiliser les min/max sur les filters
        if(!filter_var($var, FILTER_VALIDATE_INT, FILTER_FLAG_ALLOW_HEX)) return false;
#else
        // @FIXME : trouver une solution plus performante ?
        if(!preg_match("/^0x[a-f0-9A-F]+$/", $val)) return false;
#endif
        if($min !== null && intval($val,16) < $min) return false;
        if($max !== null && intval($val,16) > $max) return false;
        return true;
    }


     /**
     * check if the given value is a boolean
     * @param string $val the value
     * @return boolean true if it is valid
     */
    static public function isBool ($val){
#ifdef PHP52
        return filter_var($var, FILTER_VALIDATE_BOOLEAN);
#else
        return in_array($val, array('true','false','1','0','TRUE', 'FALSE','on','off'));
#endif
    }


    /**
     * check if the given value is a float
     * @param string $val the value
     * @param int $min minimum value (optional), null if no minimum
     * @param int $max maximum value (optional), null if no maximum
     * @return boolean true if it is valid
     */
    static public function isFloat ($val, $min=null, $max=null){
#ifdef PHP52
        // @FIXME pas de doc sur la façon d'utiliser les min/max sur les filters
        if(!filter_var($var, FILTER_VALIDATE_FLOAT)) return false;
#else
        if(!is_numeric($val)) return false;
#endif
        if($min !== null && floatval($val) < $min) return false;
        if($max !== null && floatval($val) > $max) return false;
        return true;
    }

    /**
     * check if the given value is
     * @param string $val the value
     * @return boolean true if it is valid
     */

    static public function isUrl ($val, $schemeRequired=true ){
#ifdef PHP52
        return filter_var($var, FILTER_VALIDATE_);
#else
        return false;
#endif
    }


    /**
     * check if the given value is
     * @param string $val the value
     * @return boolean true if it is valid
     */
     /*
    static public function is ($val){
#ifdef PHP52
        return filter_var($var, FILTER_VALIDATE_);
#else
        return false;
#endif
    }*/




}

?>