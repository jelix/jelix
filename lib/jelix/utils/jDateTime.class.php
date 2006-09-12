<?php
/**
* @package     jelix
* @subpackage  utils
* @version     $Id:$
* @author      Croes Gérald, Jouanneau Laurent
* @contributor Laurent Jouanneau
* @copyright   2001-2005 CopixTeam 2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file

* Une petite partie du code est issue du fichier CopixDate.lib.php
* du framework Copix 2.3dev20050901. http://www.copix.org
* et est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/




if(!function_exists('strptime')){
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
                if(isset($plop[$v]))
                    $result[$plop[$v]]= intval($m[$k+1]);
            }
            $result['tm_year'] -= 1900;
            return $result;
        }else{
            return false;
        }
    }
}


/**
 *
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
    const BD_DFORMAT=20;
    const BD_DTFORMAT=21;
    const BD_TFORMAT=22;
    const ISO8601_FORMAT=40;
    const TIMESTAMP_FORMAT=50;


    function __construct($year=0, $month=0, $day=0, $hour=0, $minute=0, $second=0){
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->hour = $hour;
        $this->minute = $minute;
        $this->second = $second;

    }


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
           case self::BD_DFORMAT:
               $str = sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day);
               break;
           case self::BD_DTFORMAT:
               $str = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second);
               break;
           case self::BD_TFORMAT:
               $str = sprintf('%02d:%02d:%02d', $this->hour, $this->minute, $this->second);
               break;
           case self::ISO8601_FORMAT:
               $str = sprintf('%04d%02d%02dT%02d:%02d:%02d', $this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second);
               break;
           case self::TIMESTAMP_FORMAT:
               $str =(string) mktime ( $this->hour, $this->minute,$this->second , $this->month, $this->day, $this->year );
               break;
        }
       return $str;
    }

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
               $lf = jLocale::get('jelix~format.date');
               if($res = strptime ( $str, $lf )){
                   $ok=true;
                   $this->year = $res['tm_year']+1900;
                   $this->month = $res['tm_mon'];
                   $this->day = $res['tm_mday'];
                   $this->hour = 0;
                   $this->minute =0;
                   $this->second = 0;
               }
               break;
           case self::LANG_DTFORMAT:
               $lf = jLocale::get('jelix~format.datetime');
               if($res = strptime ( $str, $lf )){
                   $ok=true;
                   $this->year = $res['tm_year']+1900;
                   $this->month = $res['tm_mon'];
                   $this->day = $res['tm_mday'];
                   $this->hour = $res['tm_hour'];
                   $this->minute = $res['tm_min'];
                   $this->second = $res['tm_sec'];
               }
               break;
           case self::LANG_TFORMAT:
               $lf = jLocale::get('jelix~format.time');
               if($res = strptime ( $str, $lf )){
                   $ok=true;
                   $this->year = 0;
                   $this->month = 0;
                   $this->day = 0;
                   $this->hour = $res['tm_hour'];
                   $this->minute = $res['tm_min'];
                   $this->second = $res['tm_sec'];
               }
               break;
           case self::BD_DFORMAT:
               if($ok=preg_match('/^(\d{4})\-(\d{2})\-(\d{2})$/', $str, $match)){
                    $this->year = $match[1];
                    $this->month = $match[2];
                    $this->day = $match[3];
                    $this->hour = 0;
                    $this->minute = 0;
                    $this->second = 0;
               }
               break;
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
           case self::BD_TFORMAT:
               if($ok=preg_match('/^(\d{2}):(\d{2}):(\d{2})$/', $str, $match)){
                    $this->year = 0;
                    $this->month = 0;
                    $this->day = 0;
                    $this->hour = $match[1];
                    $this->minute = $match[2];
                    $this->second = $match[3];
               }
               break;
           case self::ISO8601_FORMAT:
               if($ok=preg_match('/^(\d{4})(\d{2})(\d{2})T(\d{2}):(\d{2}):(\d{2})$/', $str, $match)){
                    $this->year = $match[1];
                    $this->month = $match[2];
                    $this->day = $match[3];
                    $this->hour = $match[4];
                    $this->minute = $match[5];
                    $this->second = $match[6];
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
        }
        return $ok;
    }

    /**
     * @param jDateTime $dt la durée à ajouter
     */
    public function add($dt){

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
     * @param jDateTime $dt la durée à enlever
     */
    public function sub($dt){
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
     * pour connaître la durée entre deux dates.
     */
    public function durationTo($dt){
       $t = mktime ( $dt->hour, $dt->minute,$dt->second , $dt->month, $dt->day, $dt->year )
         - mktime ( $this->hour, $this->minute,$this->second , $this->month, $this->day, $this->year );
       $t = getdate ($t);
       return new jDateTime( $t['year']-1970,$t['mon']-1, $t['mday']-1, $t['hours']-1, $t['minutes'], $t['seconds']);
    }

    /**
     * compare la date avec une autre
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

}

?>