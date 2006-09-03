<?php
/**
* @package     jelix
* @subpackage  utils
* @version     $Id:$
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
abstract class jDatatype {

  protected $length=null;
  protected $minLength=null;
  protected $maxLength=null;
  protected $pattern=null;
  protected $whitespace=null;
  protected $maxInclusive=null;
  protected $minInclusive=null;
  protected $maxExclusive=null;
  protected $minExclusive=null;
  protected $totalDigits=null;
  protected $fractionDigits=null;

  protected $hasFacets= false;

  protected $facets = array('length','minLength','maxLength', 'pattern', 'whitespace', 'maxInclusive',
            'minInclusive', 'maxExclusive', 'minExclusive', 'totalDigits', 'fractionDigits');

  function __construct(){
  }

  /**
   * permet d'indiquer des restrictions sur les valeurs
   * @param array/string $type
   */
  public function addFacets($type,$value=null){
     $this->hasFacets = true;
     if(is_array($type)){
       foreach($type as $t=>$v){
          if(in_array($t, $this->facets)){
             $this->_addFacet($t,$v);
          }
       }
     }else{
       if(in_array($type, $this->facets)){
           $this->_addFacet($type,$value);
       }
     }
  }

  protected function _addFacet($type,$value){
      $this->$type = $value;
  }

  /**
   * vérifie qu'une valeur correspond bien au datatype
   * @param string   $value
   */
  public function check($value){
    if($this->_checkType($value)){
      if($this->hasFacets)
        return $this->_checkFacets($value) && $this->_checkValueFacets($value);
      else
        return false;
    }else
      return false;
  }

  /**
   * verifie le type de la valeur
   */
  protected function _checkType($value) { return true; }

  /**
   * verifie les restrictions sur la chaine contenant la valeur
   */
  protected function _checkFacets($value){
    if($this->length !== null && strlen($value) > $this->length)
        return false;
    if($this->minLength !== null && strlen($value) < $this->minLength)
        return false;
    if($this->maxLength !== null && strlen($value) > $this->maxLength)
        return false;
    if($this->pattern !== null && !preg_match($this->pattern,$value))
        return false;
    return true;
  }
  /**
   * verifie les restrictions sur la valeur
   */
  protected function _checkValueFacets($value){
    if($this->maxInclusive !== null && $value > $this->maxInclusive)
        return false;
    if($this->minInclusive !== null && $value < $this->minInclusive)
        return false;
    if($this->maxExclusive !== null && $value >= $this->maxExclusive)
        return false;
    if($this->minExclusive !== null && $value <= $this->minExclusive)
        return false;
    return true;
  }
}

/**
 * Datatype String
 * @package     jelix
 * @subpackage  utils
 */
class jDatatypeString extends jDatatype {
}

/**
 * Datatype Booléen
 * @package     jelix
 * @subpackage  utils
 */
class jDatatypeBoolean extends jDatatype {
  protected function _checkType($value) { return ($value == 'true' || $value=='false'); }
  protected function _checkValueFacets($value){ return true; }
  protected function _checkFacets($value){ return true; }
}

/**
 * Datatype Decimal
 * @package     jelix
 * @subpackage  utils
 */
class jDatatypeDecimal extends jDatatype {
 // xxxx.yyyyy
  protected function _checkType($value) { return is_numeric($value); }
  protected function _checkValueFacets($value){ return parent::_checkValueFacets(floatval($value)); }
  protected function _addFacet($type,$value){
       if(in_array($type, array('maxInclusive', 'minInclusive', 'maxExclusive', 'minExclusive'))){
           $this->$type = floatval($value);
       }else{
          parent::_addFacet($type,$value);
       }
  }
}

/**
 * Datatype Integer
 * @package     jelix
 * @subpackage  utils
 */
class jDatatypeInteger extends jDatatype {
  protected function _checkType($value) {
      if(!is_numeric($value)) return false;
      return intval($value) == floatval($value);
  }
  protected function _checkValueFacets($value){ return parent::_checkValueFacets(intval($value)); }
  protected function _addFacet($type,$value){
       if(in_array($type, array('maxInclusive', 'minInclusive', 'maxExclusive', 'minExclusive'))){
           $this->$type = intval($value);
       }else{
          parent::_addFacet($type,$value);
       }
  }
}

/**
 * Datatype datetime
 * @package     jelix
 * @subpackage  utils
 */
class jDatatypeDateTime extends jDatatype {
  private $dt;
  protected $format=21;
  protected function _checkType($value) {
      $this->dt = new JDateTime();
      return $this->dt->setFromString($value,$this->format);
  }

  protected function _addFacet($type,$value){
      if(in_array($type, array('maxInclusive', 'minInclusive', 'maxExclusive', 'minExclusive'))){
         $this->$type = new JDateTime();
         $this->$type->setFromString($value,$this->format);
      }else{
         parent::_addFacet($type,$value);
      }
  }

  protected function _checkFacets($value){
    return true;
  }

  protected function _checkValueFacets($value){
    if($this->maxInclusive !== null){
        if($this->dt->compareTo($this->maxInclusive) == 1) return false;
    }
    if($this->minInclusive !== null){
        if($this->dt->compareTo($this->minInclusive) == -1) return false;
    }
    if($this->maxExclusive !== null){
        if($this->dt->compareTo($this->maxExclusive) != -1) return false;
    }
    if($this->minExclusive !== null){
        if($this->dt->compareTo($this->minExclusive) != 1) return false;
    }
    return true;
  }

}

/**
 * Datatype time
 * @package     jelix
 * @subpackage  utils
 */
class jDatatypeTime extends jDatatypeDateTime {
   protected $format=22;
}
/**
 * Datatype date
 * @package     jelix
 * @subpackage  utils
 */
class jDatatypeDate extends jDatatypeDateTime {
   protected $format=20;
}

/**
 * Datatype localedatetime
 * @package     jelix
 * @subpackage  utils
 */
class jDatatypeLocaleDateTime extends jDatatypeDateTime {
   protected $format=11;
}

/**
 * Datatype localedate
 * @package     jelix
 * @subpackage  utils
 */
class jDatatypeLocaleDate extends jDatatypeDateTime {
   protected $format=10;
}

/**
 * Datatype localetime
 * @package     jelix
 * @subpackage  utils
 */
class jDatatypeLocaleTime extends jDatatypeDateTime {
   protected $format=12;
}


/*class jDatatypeLong extends jDatatype {
}

class jDatatypeInt extends jDatatype {
}

class jDatatypeShort extends jDatatype {
}

class jDatatypeByte extends jDatatype {
}

class jDatatypeFloat extends jDatatype {
//m × 2^e, avec m < 2^24, et -149 <= e <= 104
//1E4, 1267.43233E12, 12.78e-2, 12 , -0, 0 INF
}

class jDatatypeDouble extends jDatatype {
//m × 2^e, avec m < 2^53, et -1075 <= e<= 970

}*/

?>