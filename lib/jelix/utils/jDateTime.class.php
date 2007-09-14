<?php
/**
* @package     jelix
* @subpackage  utils
* @author      Gérald Croes, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2001-2005 CopixTeam 2005-2006 Laurent Jouanneau
* @copyright   2007 Loic Mathaud
*
* This class was get originally from the Copix project (CopixDate.lib.php, Copix 2.3dev20050901, http://www.copix.org)
* Only few lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix classes are Gerald Croes and Laurent Jouanneau,
* and this class was adapted/improved for Jelix by Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

#if PHP50
if(!function_exists('strptime')){ // existe depuis php 5.1
    /**
     * @ignore
     */
    function strptime ( $strdate, $format ){
        // c'est pas une compatibilité 100% avec strptime de PHP 5.1 mais c'est suffisant pour nos besoins
        $plop = array( 's'=>'tm_sec', 'i'=>'tm_min', 'H'=>'tm_hour',
        'd'=>'tm_mday', 'm'=>'tm_mon', 'Y'=>'tm_year');


        $regexp = preg_quote($format, '/');
        $regexp = str_replace(
                array('%d','%m','%Y','%H','%i','%s'),
                array('(\d{2})','(\d{2})','(\d{4})','(\d{2})','(\d{2})','(\d{2})'),
                $regexp);
        if(preg_match('/^'.$regexp.'$/', $strdate,$m)){
            $result=array('tm_sec'=>0,'tm_min'=>0,'tm_hour'=>0,'tm_mday'=>0,'tm_mon'=>0,'tm_year'=>0,'tm_wday'=>0,'tm_yday'=>0,'unparsed'=>'');
            preg_match_all('/%(\w)/',$format,$patt);
            foreach($patt[1] as $k=>$v){
                if(!isset($plop[$v])) continue;
                $result[$plop[$v]] = intval($m[$k+1]);
                if($plop[$v] == 'tm_mon'){
                    $result[$plop[$v]] -= 1;
                }
            }
            $result['tm_year'] -= 1900;
            return $result;
        }else{
            return false;
        }
    }
}
#endif

/**
 * Utility to manipulate dates and convert date format
 * @package     jelix
 * @subpackage  utils
 */
class jDateTime{
    public $day;
    public $month;
    public $year;
    public $hour;
    public $minute;
    public $second;

    public $defaultFormat = 11;

    const LANG_DFORMAT=10;
    const LANG_DTFORMAT=11;
    const LANG_TFORMAT=12;
    const DB_DFORMAT=20;
    const DB_DTFORMAT=21;
    const DB_TFORMAT=22;
    const ISO8601_FORMAT=40;
    const TIMESTAMP_FORMAT=50;
    const RFC822_FORMAT=60;

    /**#@+
     * use DB_* consts instead
     * @deprecated
     */
    const BD_DFORMAT=20;
    const BD_DTFORMAT=21;
    const BD_TFORMAT=22;
    /**#@-*/

    function __construct($year=0, $month=0, $day=0, $hour=0, $minute=0, $second=0){
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->hour = $hour;
        $this->minute = $minute;
        $this->second = $second;

    }

    /**
     * convert the date to a string format
     * @param int $format one of the class constant, or -1 if it is a default format
     * @return string the string date
     */
    function toString($format=-1){
        if($format==-1)
            $format = $this->defaultFormat;

        $str='';
        switch($format){
           case self::LANG_DFORMAT:
               $t = mktime ( $this->hour, $this->minute,$this->second , $this->month, $this->day, $this->year );
               $lf = jLocale::get('jelix~format.date');
               $str = date($lf, $t);
               break;
           case self::LANG_DTFORMAT:
               $t = mktime ( $this->hour, $this->minute,$this->second , $this->month, $this->day, $this->year );
               $lf = jLocale::get('jelix~format.datetime');
               $str = date($lf, $t);
               break;
           case self::LANG_TFORMAT:
               $t = mktime ( $this->hour, $this->minute,$this->second , 0 , 0, 0 );
               $lf = jLocale::get('jelix~format.time');
               $str = date($lf, $t);
               break;
           case self::DB_DFORMAT:
           case self::BD_DFORMAT:
               $str = sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day);
               break;
           case self::DB_DTFORMAT:
           case self::BD_DTFORMAT:
               $str = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second);
               break;
           case self::DB_TFORMAT:
           case self::BD_TFORMAT:
               $str = sprintf('%02d:%02d:%02d', $this->hour, $this->minute, $this->second);
               break;
           case self::ISO8601_FORMAT:
               $str = sprintf('%04d-%02d-%02dT%02d:%02d:%02dZ', $this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second);
               break;
           case self::TIMESTAMP_FORMAT:
               $str =(string) mktime ( $this->hour, $this->minute,$this->second , $this->month, $this->day, $this->year );
               break;
           case self::RFC822_FORMAT:
               $str = date('r', mktime ( $this->hour, $this->minute,$this->second , $this->month, $this->day, $this->year ));
               break;
        }
       return $str;
    }

    /**
     * read a string to extract date values
     * @param string $str the string date
     * @param int $format one of the class constant, or -1 if it is a default format
     */
    function setFromString($str,$format=-1){
        if($format==-1)
            $format = $this->defaultFormat;
        $this->year = 0;
        $this->month = 0;
        $this->day = 0;
        $this->hour = 0;
        $this->minute = 0;
        $this->second = 0;
        $ok=false;
        switch($format){
           case self::LANG_DFORMAT:
               $lf = jLocale::get('jelix~format.date_st');
               if($res = strptime ( $str, $lf )){
                   $ok=true;
                   $this->year = $res['tm_year']+1900;
                   $this->month = $res['tm_mon'] +1;
                   $this->day = $res['tm_mday'];
               }
               break;
           case self::LANG_DTFORMAT:
               $lf = jLocale::get('jelix~format.datetime_st');
               if($res = strptime ( $str, $lf )){
                   $ok=true;
                   $this->year = $res['tm_year']+1900;
                   $this->month = $res['tm_mon'] +1;
                   $this->day = $res['tm_mday'];
                   $this->hour = $res['tm_hour'];
                   $this->minute = $res['tm_min'];
                   $this->second = $res['tm_sec'];
               }
               break;
           case self::LANG_TFORMAT:
               $lf = jLocale::get('jelix~format.time_st');
               if($res = strptime ( $str, $lf )){
                   $ok=true;
                   $this->hour = $res['tm_hour'];
                   $this->minute = $res['tm_min'];
                   $this->second = $res['tm_sec'];
               }
               break;
           case self::DB_DFORMAT:
           case self::BD_DFORMAT:
               if($ok=preg_match('/^(\d{4})\-(\d{2})\-(\d{2})$/', $str, $match)){
                    $this->year = $match[1];
                    $this->month = $match[2];
                    $this->day = $match[3];
               }
               break;
           case self::DB_DTFORMAT:
           case self::BD_DTFORMAT:
               if($ok=preg_match('/^(\d{4})\-(\d{2})\-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/', $str, $match)){
                    $this->year = $match[1];
                    $this->month = $match[2];
                    $this->day = $match[3];
                    $this->hour = $match[4];
                    $this->minute = $match[5];
                    $this->second = $match[6];
               }
               break;
           case self::DB_TFORMAT:
           case self::BD_TFORMAT:
               if($ok=preg_match('/^(\d{2}):(\d{2}):(\d{2})$/', $str, $match)){
                    $this->hour = $match[1];
                    $this->minute = $match[2];
                    $this->second = $match[3];
               }
               break;
           case self::ISO8601_FORMAT:
               if($ok=preg_match('/^(\d{4})(?:\-(\d{2})(?:\-(\d{2})(?:T(\d{2}):(\d{2})(?::(\d{2})(?:\.(\d{2}))?)?(Z|[+\-]\d{2}:\d{2}))?)?)?$/', $str, $match)){
                    $c = count($match)-1;
                    $this->year = $match[1];
                    if($c<2) break;
                    $this->month = $match[2];
                    if($c<3) break;
                    $this->day = $match[3];
                    if($c<4) break;
                    $this->hour = $match[4];
                    $this->minute = $match[5];
                    if($match[6] != '') $this->second = $match[6];
                    if($match[8] != 'Z'){
                        $d = new jDateTime(0,0,0,$match[10],$match[11]);
                        if($match[9] == '+')
                            $this->add($d);
                        else
                            $this->sub($d);
                    }
               }
               break;
           case self::TIMESTAMP_FORMAT:
               $ok=true;
               $t = getdate ( intval($str) );
               $this->year = $t['year'];
               $this->month = $t['mon'];
               $this->day = $t['mday'];
               $this->hour = $t['hours'];
               $this->minute = $t['minutes'];
               $this->second = $t['seconds'];
               break;
           case self::RFC822_FORMAT:
               throw new Exception ('jDatetime::setFromString : RFC822_FORMAT not implemented');
        }
        return $ok;
    }

    /**
     * add a duration to the date
     * @param jDateTime/int $year the duration value or a year with 4 digits
     * @param int $month month with 2 digits
     * @param int $day day with 2 digits
     * @param int $hour hour with 2 digits
     * @param int $minute minute with 2 digits
     * @param int $second second with 2 digits
     */
    public function add($year, $month=0, $day=0, $hour=0, $minute=0, $second=0) {
        if ($year instanceof jDateTime) {
            $dt = $year;
        } else {
            $dt = new jDateTime($year, $month, $day, $hour, $minute, $second);
        }
        $t = mktime ( $this->hour +  $dt->hour, $this->minute + $dt->minute, $this->second + $dt->second ,
             $this->month + $dt->month, $this->day + $dt->day, $this->year + $dt->year);

        $t = getdate ($t);
        $this->year = $t['year'];
        $this->month = $t['mon'];
        $this->day = $t['mday'];
        $this->hour = $t['hours'];
        $this->minute = $t['minutes'];
        $this->second = $t['seconds'];
    }

    /**
     * substract a duration to the date
     * @param jDateTime/int $year the duration value or a year with 4 digits
     * @param int $month month with 2 digits
     * @param int $day day with 2 digits
     * @param int $hour hour with 2 digits
     * @param int $minute minute with 2 digits
     * @param int $second second with 2 digits
     */
    public function sub($year, $month=0, $day=0, $hour=0, $minute=0, $second=0) {
        if ($year instanceof jDateTime) {
            $dt = $year;
        } else {
            $dt = new jDateTime($year, $month, $day, $hour, $minute, $second);
        }
        $t = mktime ( $this->hour -  $dt->hour, $this->minute - $dt->minute, $this->second - $dt->second ,
             $this->month - $dt->month, $this->day - $dt->day, $this->year - $dt->year);

        $t = getdate ($t);
        $this->year = $t['year'];
        $this->month = $t['mon'];
        $this->day = $t['mday'];
        $this->hour = $t['hours'];
        $this->minute = $t['minutes'];
        $this->second = $t['seconds'];
    }

    /**
     * to know the duration between two dates
     */
    public function durationTo($dt){
       $t = mktime ( $dt->hour, $dt->minute,$dt->second , $dt->month, $dt->day, $dt->year )
         - mktime ( $this->hour, $this->minute,$this->second , $this->month, $this->day, $this->year );
       $t = getdate ($t);
       return new jDateTime( $t['year']-1970,$t['mon']-1, $t['mday']-1, $t['hours']-1, $t['minutes'], $t['seconds']);
    }

    /**
     * compare two date
     * @param jDateTime $dt the date to compare
     * @return integer -1 si $dt >, 0 si =, 1 si $dt <
     */
    public function compareTo($dt){
      $fields=array('year','month','day','hour','minute','second');
      foreach($fields as $field){
         if($dt->$field > $this->$field)
            return -1;
         else if($dt->$field < $this->$field)
            return 1;
      }
      return 0;
    }

    /**
    * set date to current datetime
    */
    public function now() {
        $this->year = date('Y');
        $this->month = date('m');
        $this->day = date('d');
        $this->hour = date('H');
        $this->minute = date('i');
        $this->second = date('s');
    }
}

?>
