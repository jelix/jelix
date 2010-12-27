<?php
/**
* @package    jelix
* @subpackage core
* @author     Laurent Jouanneau
* @contributor F. Fernandez
* @copyright  2006-2010 Laurent Jouanneau, 2007 F. Fernandez
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * interface for log message. A component which want to log
 * a message can use an object implementing this interface.
 * Classes that implements it are responsible to format
 * the message. Formatting a message depends on its type.
 */
interface jILoggerMessage {
    public function getCategory();
    public function getFormatedMessage();
}

#if ENABLE_OPTIMIZED_SOURCE
#includephp log/jLogMessage.class.php
#includephp log/jLogErrorMessage.class.php
#else
require(JELIX_LIB_CORE_PATH.'log/jLogMessage.class.php');
require(JELIX_LIB_CORE_PATH.'log/jLogErrorMessage.class.php');
#endif


/**
 * interface for loggers
 */
interface jILogger {
    /**
     * @param jILoggerMessage $message the message to log
     */
    function logMessage($message);

    /**
     * output messages to the given response
     * @param jResponse $response
     */
    function output($response);
}

#if ENABLE_OPTIMIZED_SOURCE
#includephp log/jFileLogger.class.php
#else
require(JELIX_LIB_CORE_PATH.'log/jFileLogger.class.php');
#endif

/**
 * utility class to log some message into a file into yourapp/var/log
 * @package    jelix
 * @subpackage utils
 * @static
 */
class jLog {

    protected static $loggers = array();

    protected function __construct(){
        
    }

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
    * Warning: since it is called by error handler, it should not trigger errors!
    * and should take care of case were an error could appear
    * @param mixed $message
    * @param string $type the log type
    */
    public static function log ($message, $type='default') {
        global $gJCoord, $gJConfig;
        if (!is_object($message) || ! $message instanceof jILoggerMessage)
            $message = new jLogMessage($message, $type);

        if (!isset($gJConfig->logger[$type])) {
            $type='default';
        }
        $loggers = preg_split('/[\s,]+/', $gJConfig->logger[$type]);
        // let's inject the message in all loggers
        foreach($loggers as $loggername) {
            if ($loggername == '')
                continue;
            if(!isset(self::$loggers[$loggername])) {
                if ($loggername == 'file')
                    self::$loggers[$loggername] = new jFileLogger();
                elseif ($loggername == 'syslog') {
                    require(JELIX_LIB_CORE_PATH.'log/jSyslogLogger.class.php');
                    self::$loggers[$loggername] = new jSyslogLogger();
                }
                elseif ($loggername == 'mail') {
                    require(JELIX_LIB_CORE_PATH.'log/jMailLogger.class.php');
                    self::$loggers[$loggername] = new jMailLogger();
                }
                else {
                    $l = $gJCoord->loadPlugin($loggername, 'logger', '.logger.php', $loggername.'Logger');
                    if (is_null($l))
                        continue; // yes, silent, because we could be inside an error handler
                    self::$loggers[$loggername] = $l;
                }
            }
            self::$loggers[$loggername]->logMessage($message);
        }
    }

    /**
     * call each loggers so they have the possibility to inject data into the
     * given response
     * @param jResponse $response
     */
    public static function outputLog($response) {
        foreach(self::$loggers as $logger) {
            $logger->output($response);
        }
    }
}
