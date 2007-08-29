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
 * @experimental
 */
abstract class jDatatype {

    protected $hasFacets= false;
    protected $facets = array();

    function __construct(){
    }

    /**
    * call it ot add restriction on possible values
    * @param string $type
    * @param string $value
    */
    public function addFacet($type,$value=null){

        if(in_array($type, $this->facets)){
            $this->hasFacets = true;
            $this->_addFacet($type,$value);
        }
    }

    protected function _addFacet($type,$value){
        $this->$type = $value;
    }

    /**
    * verify a value : it should correspond to the datatype
    * @param string   $value
    * @return boolean true if the value is ok
    */
    public function check($value){
        return true;
    }
}

/**
 * Datatype String
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeString extends jDatatype {
    protected $length=null;
    protected $minLength=null;
    protected $maxLength=null;
    protected $pattern=null;
    protected $facets = array('length','minLength','maxLength', 'pattern');

    public function check($value){
        if($this->hasFacets){
            $len = iconv_strlen($value, $GLOBALS['gJConfig']->charset);
            if($this->length !== null && $len != $this->length)
                return false;
            if($this->minLength !== null && $len < $this->minLength)
                return false;
            if($this->maxLength !== null && $len > $this->maxLength)
                return false;
            if($this->pattern !== null && !preg_match($this->pattern,$value))
                return false;
        }
        return true;
    }
}

/**
 * Datatype Booléen
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeBoolean extends jDatatype {
  public function check($value) { return jFilter::isBool($value); }
}

/**
 * Datatype Decimal
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeDecimal extends jDatatype {
    // xxxx.yyyyy
    protected $maxValue=null;
    protected $minValue=null;
    protected $facets = array('maxValue', 'minValue');
    public function check($value) { return jFilter::isFloat($value, $this->minValue, $this->maxValue); }
    protected function _addFacet($type,$value){
        if($type == 'maxValue' || $type == 'minValue'){
            $this->$type = floatval($value);
        }
    }
}

/**
 * Datatype Integer
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeInteger extends jDatatypeDecimal {
    public function check($value) { return jFilter::isInt($value, $this->minValue, $this->maxValue); }
    protected function _addFacet($type,$value){
        if($type == 'maxValue' || $type == 'minValue'){
            $this->$type = intval($value);
        }
    }
}


/**
 * Datatype Hexa
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeHexadecimal extends jDatatypeDecimal {
    public function check($value) {
        if(substr($value,0,2) != '0x') $value='0x'.$value;
        return jFilter::isHexInt($value, $this->minValue, $this->maxValue);
    }
    protected function _addFacet($type,$value){
        if($type == 'maxValue' || $type == 'minValue'){
            $this->$type = intval($value,16);
        }
    }
}


/**
 * Datatype datetime
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeDateTime extends jDatatype {
    protected $facets = array('maxValue', 'minValue');
    protected $maxValue;
    protected $minValue;
    private $dt;
    protected $format=21;
    public function check($value) {
        $this->dt = new jDateTime();
        if(!$this->dt->setFromString($value,$this->format)) return false;
        if($this->maxValue !== null){
            if($this->dt->compareTo($this->maxValue) != -1) return false;
        }
        if($this->minValue !== null){
            if($this->dt->compareTo($this->minValue) != 1) return false;
        }
        return true;
    }

    protected function _addFacet($type,$value){
        if($type == 'maxValue' || $type == 'minValue'){
            $this->$type = new jDateTime();
            $this->$type->setFromString($value,$this->format);
        }else{
            parent::_addFacet($type,$value);
        }
    }
}

/**
 * Datatype time
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeTime extends jDatatypeDateTime {
   protected $format=22;
}
/**
 * Datatype date
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeDate extends jDatatypeDateTime {
   protected $format=20;
}

/**
 * Datatype localedatetime
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeLocaleDateTime extends jDatatypeDateTime {
   protected $format=11;
}

/**
 * Datatype localedate
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeLocaleDate extends jDatatypeDateTime {
   protected $format=10;
}

/**
 * Datatype localetime
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeLocaleTime extends jDatatypeDateTime {
   protected $format=12;
}


/**
 * Datatype url
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeUrl extends jDatatype {
    protected $schemeRequired=null;
    protected $hostRequired=null;
    protected $pathRequired=null;
    protected $queryRequired=null;

    protected $facets = array('schemeRequired','hostRequired','pathRequired', 'queryRequired');

    public function check($value){
        return jFilter::isUrl($value, $this->schemeRequired, $this->hostRequired, $this->pathRequired, $this->queryRequired);
    }
}

/**
 * Datatype ipv4
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeIPv4 extends jDatatype {
    public function check($value){
        return jFilter::isIPv4($value);
    }
}

/**
 * Datatype ipv6
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeIPv6 extends jDatatype {
    public function check($value){
        return jFilter::isIPv6($value);
    }
}

/**
 * Datatype mail
 * @package     jelix
 * @subpackage  utils
 * @experimental
 */
class jDatatypeEmail extends jDatatype {
    public function check($value){
        return jFilter::isEmail($value);
    }
}


?>