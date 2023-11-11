<?php
/**
 * @package    jelix
 * @subpackage core_log
 *
 * @author     Laurent Jouanneau
 * @contributor F. Fernandez, Hadrien Lanneau
 *
 * @copyright  2006-2018 Laurent Jouanneau, 2007 F. Fernandez, 2011 Hadrien Lanneau
 *
 * @see       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Logger;

/**
 * utility class to log some message into a file into yourapp/var/log.
 *
 * @package    jelix
 * @subpackage core_log
 * @static
 */
class Log
{
    /**
     * @var OutputInterface[]
     */
    protected static $loggers = array();

    /**
     * all messages, when the memory logger is used.
     *
     * @var MessageInterface[]
     */
    protected static $allMessages = array();

    /**
     * messages count of each categories, for the memory logger.
     */
    protected static $messagesCount = array();

    /**
     * private constructor. static class.
     */
    private function __construct()
    {
    }

    /**
     * log a dump of a php value (object or else) into the given category.
     *
     * @param mixed  $obj      the value to dump
     * @param string $label    a label
     * @param string $category the message category
     */
    public static function dump($obj, $label = '', $category = 'default')
    {
        $message = new Message\Dump($obj, $label, $category);
        self::_dispatchLog($message);
    }

    /**
     * log a message into the given category.
     * Warning: since it is called by error handler, it should not trigger errors!
     * and should take care of case were an error could appear.
     *
     * @param mixed  $message
     * @param string $category the log type
     */
    public static function log($message, $category = 'default')
    {
        if (!is_object($message) || !$message instanceof MessageInterface) {
            $message = new Message\Text($message, $category);
        }
        self::_dispatchLog($message);
    }

    /**
     * log an exception into the given category.
     *
     * @param \Exception $exception
     * @param string     $category  the log type
     */
    public static function logEx($exception, $category = 'default')
    {
        $message = new Message\Error(
            $category,
            $exception->getCode(),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTrace()
        );
        self::_dispatchLog($message);
    }

    /**
     * @param MessageInterface $message
     */
    protected static function _dispatchLog($message)
    {
        $confLoggers = &\Jelix\Core\App::config()->logger;
        $category = $message->getCategory();
        if (!isset($confLoggers[$category])
            || strpos($category, 'option_') === 0) { // option_* are not some type of log messages
            $category = 'default';
        }

        $all = $confLoggers['_all'];
        $loggers = preg_split('/[\s,]+/', $confLoggers[$category]);

        if ($all != '') {
            $allLoggers = preg_split('/[\s,]+/', $all);
            self::_log($message, $allLoggers);
            $loggers = array_diff($loggers, $allLoggers);
        }

        self::_log($message, $loggers);
    }

    /**
     * @param MessageInterface  $message
     * @param string[] $loggers
     */
    protected static function _log($message, $loggers)
    {

        // let's inject the message in all loggers
        foreach ($loggers as $loggername) {
            if ($loggername == '') {
                continue;
            }
            if ($loggername == 'memory') {
                $confLog = &\Jelix\Core\App::config()->memorylogger;
                $cat = $message->getCategory();
                if (isset($confLog[$cat])) {
                    $max = intval($confLog[$cat]);
                } else {
                    $max = intval($confLog['default']);
                }
                if (!isset(self::$messagesCount[$cat])) {
                    self::$messagesCount[$cat] = 0;
                }
                if (++self::$messagesCount[$cat] > $max) {
                    continue;
                }
                self::$allMessages[] = $message;

                continue;
            }
            if (!isset(self::$loggers[$loggername])) {
                if ($loggername == 'file') {
                    self::$loggers[$loggername] = new Output\File();
                } elseif ($loggername == 'syslog') {
                    self::$loggers[$loggername] = new Output\Syslog();
                } elseif ($loggername == 'mail') {
                    self::$loggers[$loggername] = new Output\Mail();
                } elseif ($loggername == 'stderr') {
                    self::$loggers[$loggername] = new Output\Stderr();
                } elseif ($loggername == 'stdout') {
                    self::$loggers[$loggername] = new Output\Stdout();
                } else {
                    $l = \Jelix\Core\App::loadPlugin($loggername, 'logger', '.logger.php', $loggername.'Logger');
                    if (is_null($l)) {
                        continue;
                    } // yes, silent, because we could be inside an error handler
                    self::$loggers[$loggername] = $l;
                }
            }
            self::$loggers[$loggername]->logMessage($message);
        }
    }

    /**
     * returns messages stored in memory (if the memory logger is activated).
     *
     * @param array|string $filter if given, category or list of categories
     *                             of messages you want to retrieve
     *
     * @return MessageInterface[] the list of MessageInterface object
     */
    public static function getMessages($filter = false)
    {
        if ($filter === false || self::$allMessages === null) {
            return self::$allMessages;
        }
        if (is_string($filter)) {
            $filter = array($filter);
        }
        $list = array();
        foreach (self::$allMessages as $msg) {
            if (in_array($msg->getCategory(), $filter)) {
                $list[] = $msg;
            }
        }

        return $list;
    }

    /**
     * @param mixed $category
     *
     * @return int
     */
    public static function getMessagesCount($category)
    {
        if (isset(self::$messagesCount[$category])) {
            return self::$messagesCount[$category];
        }

        return 0;
    }

    /**
     * call each loggers so they have the possibility to inject data into the
     * given response.
     *
     * @param \Jelix\Routing\ServerResponse $response
     */
    public static function outputLog($response)
    {
        foreach (self::$loggers as $logger) {
            $logger->output($response);
        }
    }

    /**
     * indicate if, for the given category, the given logger is activated.
     *
     * @param string $logger   the logger name
     * @param string $category the category
     *
     * @return bool true if it is activated
     */
    public static function isPluginActivated($logger, $category)
    {
        $confLog = &\Jelix\Core\App::config()->logger;

        $loggers = preg_split('/[\s,]+/', $confLog['_all']);
        if (in_array($logger, $loggers)) {
            return true;
        }

        if (!isset($confLog[$category])) {
            return false;
        }

        $loggers = preg_split('/[\s,]+/', $confLog[$category]);

        return in_array($logger, $loggers);
    }
}
