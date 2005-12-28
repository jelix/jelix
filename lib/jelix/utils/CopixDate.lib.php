<?php
/**
* @package     jelix
* @subpackage  utils
* @version     $Id:$
* @author      Croes Gérald, Jouanneau Laurent
* @contributor Laurent Jouanneau
* @copyright   2001-2005 CopixTeam 2005-2006 Laurent Jouanneau
* @link        http://copix.org
* @link        http://ljouanneau.com/softs/jelix
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
* adaptation et amélioration pour jelix par Laurent Jouanneau
*/


/**
 * Calcul le laps de temps écoulé entre deux dates.
 * @param   string  $DteMin     la date a soustraire de DteMax Chaine au format Fr jj/mm/aaaa.
 * @param   string  $DteMax     la date d'ou soustraire DteMin Chaine au format Fr jj/mm/aaaa.
 * @param   string  $SplitChar  le caractere séparateur utilisé dans les dates (par defaut : /)
 * @return integer  Positif Max > Min, Negatif Max < Min, 0 Max = Min.
 */
function timeBetween ($DteMin, $DteMax, $SplitChar='/'){
   $MinTable = explode ($SplitChar, $DteMin);
   $MaxTable = explode ($SplitChar, $DteMax);
   $Between = mktime (0,0,0,$MaxTable[1], $MaxTable[0], $MaxTable[2]) - mktime (0,0,0,$MinTable[1], $MinTable[0], $MinTable[2]);
   return $Between;
}

/**
 * Ajoute un nombre de jours/mois/années à une date et retourne la nouvelle date obtenue.
 * @param  string    $ToDate    La date que l'on va incrémenter. Format Fr.
 * @param  integer   $Day       le nombre de jours à ajouter.
 * @param  integer   $Month     le nombre de mois a ajouter.
 * @param  integer   $year      le nombre d'années à ajouter.
 * @param   string  $SplitChar  le caractere séparateur utilisé dans les dates (par defaut : /)
 * @return string   La date modifiée. Format fr jj-mm-aaaa.
 */
function addToDate ($ToDate, $Day, $Month=0, $Year=0, $SplitChar='/') {
   $TblToDate = explode ($SplitChar, $ToDate);//Tableau avec les valeurs actuelles.
   $BeforeTime = mktime (0, 0, 0, $TblToDate[1], $TblToDate[0], $TblToDate[2]);//Création d'une marque temps avec l'ancienne date.
   $NewValue = $BeforeTime + mktime (0, 0, 0, $Month, $Day, $Year);
   return date('d'.$SplitChar.'m'.$SplitChar.'Y', $NewValue);//Reconversion de la valeur en format date.
}

class jDateTime{
    public $day;
    public $month;
    public $year;
    public $hour;
    public $minute;
    public $second;

    public $defaultFormat = 10;

    const LANG_DFORMAT=10;
    const LANG_DTFORMAT=11;
    const LANG_TFORMAT=12;
    const BD_DFORMAT=20;
    const BD_DTFORMAT=21;
    const BD_TFORMAT=22;
    const ISO8601_FORMAT=40;


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
           case self::BD_DFORMAT:
               $str = sprintf('%04d-%02d-%04d', $this->year, $this->month, $this->day);
               break;
           case self::BD_DTFORMAT:
               $str = sprintf('%04d-%02d-%04d %02d:%02d:%02d', $this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second);
               break;
           case self::ISO8601_FORMAT:
               $str = sprintf('%04d%02d%02dT%02d:%02d:%02d', $this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second);
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
               if($ok=preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $str, $match)){
                    $this->year = $match[3];
                    $this->month = $match[2];
                    $this->day = $match[1];
               }
               break;
           case self::LANG_DTFORMAT:
               if($ok=preg_match('/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2})h(\d{2})m(\d{2})s$/', $str, $match)){
                    $this->year = $match[3];
                    $this->month = $match[2];
                    $this->day = $match[1];
                    $this->hour = $match[4];
                    $this->minute = $match[5];
                    $this->second = $match[6];
               }
               break;
           case self::BD_DFORMAT:
               if($ok=preg_match('/^(\d{4})\-(\d{2})\-(\d{2})$/', $str, $match)){
                    $this->year = $match[1];
                    $this->month = $match[2];
                    $this->day = $match[3];
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
        }
        return $ok;
    }

}



?>