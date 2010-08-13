<?php
/**
* @package    jelix
* @subpackage utils
* @author     Laurent Jouanneau
* @contributor F. Fernandez
* @copyright  2006-2010 Laurent Jouanneau, 2007 F. Fernandez
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * utility class to log some message into a file into yourapp/var/log
 * @package    jelix
 * @subpackage utils
 * @static
 */
class jLog {

    private function __construct(){}

    /**
    * log a dump of a php value (object or else)
    * @param mixed $obj the value to dump
    * @param string $label a label
    * @param string $type the log type
    */
    public static function dump($obj, $label='', $type='default'){
        if($label!=''){
            $message = $label.': '.var_export($obj,true);
        }else{
            $message = var_export($obj,true);
        }
        self::log($message, $type);
    }

    /**
    * log a message
    * @param mixed $message
    * @param string $type the log type
    */
    public static function log($message, $type='default'){
        global $gJCoord;
        if (!isset($GLOBALS['gJConfig']->logfiles[$type])) {
            $gJCoord->addLogMsg($message, $type);
            return;
        }
        $f = $GLOBALS['gJConfig']->logfiles[$type];
        if ($f[0] == '!') {
            $gJCoord->addLogMsg($message, substr($f, 1));
        }
        else {
            $ip = $gJCoord->request->getIP();
            $f = str_replace('%ip%', $ip , $f);
            $f = str_replace('%m%', date("m"), $f);
            $f = str_replace('%Y%', date("Y"), $f);
            $f = str_replace('%d%', date("d"), $f);
            $f = str_replace('%H%', date("H"), $f);
            $sel = new jSelectorLog($f);

            error_log(date ("Y-m-d H:i:s")."\t".$ip."\t$type\t$message\n", 3, $sel->getPath());
        }
    }
}
