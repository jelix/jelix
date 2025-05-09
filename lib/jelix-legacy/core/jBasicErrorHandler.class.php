<?php
/**
 * @package    jelix
 * @subpackage core
 *
 * @author     Laurent Jouanneau
 * @copyright  2012 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * Error handlers for the framework.
 * Replace the default PHP error handler.
 *
 * @param int    $errno    error code
 * @param string $errmsg   error message
 * @param string $filename filename where the error appears
 * @param int    $linenum  line number where the error appears
 */
class jBasicErrorHandler
{
    public static $errorCode = array(
        E_ERROR => 'error',
        E_RECOVERABLE_ERROR => 'error',
        E_WARNING => 'warning',
        E_NOTICE => 'notice',
        E_DEPRECATED => 'deprecated',
        E_USER_ERROR => 'error',
        E_USER_WARNING => 'warning',
        E_USER_NOTICE => 'notice',
        E_USER_DEPRECATED => 'deprecated',
    );

    public static function register()
    {
        set_error_handler(array('jBasicErrorHandler', 'errorHandler'));
        set_exception_handler(array('jBasicErrorHandler', 'exceptionHandler'));
    }

    /**
     * Error handler showing a simple error page
     * Replace the default PHP error handler.
     *
     * @param int    $errno    error code
     * @param string $errmsg   error message
     * @param string $filename filename where the error appears
     * @param int    $linenum  line number where the error appears
     */
    public static function errorHandler($errno, $errmsg, $filename, $linenum)
    {
        if (error_reporting() == 0) {
            return;
        }

        if (preg_match('/^\s*\((\d+)\)(.+)$/', $errmsg, $m)) {
            $code = $m[1];
            $errmsg = $m[2];
        } else {
            $code = 1;
        }

        if (version_compare(phpversion(), '8.4.0', '<')) {
            self::$errorCode[E_STRICT] = 'strict';
        }

        if (!isset(self::$errorCode[$errno])) {
            $errno = E_ERROR;
        }
        $codestr = self::$errorCode[$errno];

        $trace = debug_backtrace();
        array_shift($trace);
        self::handleError($codestr, $errno, $errmsg, $filename, $linenum, $trace);
    }

    /**
     * Exception handler showing a simple error page
     * Replace the default PHP Exception handler.
     *
     * @param Exception $e the exception object
     */
    public static function exceptionHandler($e)
    {
        self::handleError(
            'error',
            $e->getCode(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTrace()
        );
    }

    public static $initErrorMessages = array();

    public static function handleError($type, $code, $message, $file, $line, $trace)
    {
        $errorLog = new jLogErrorMessage($type, $code, $message, $file, $line, $trace);

        // for non fatal error appeared during init, let's just store it for loggers later
        if ($type != 'error') {
            self::$initErrorMessages[] = $errorLog;

            return;
        }
        if (jServer::isCLI()) {
            // fatal error appeared during init, in a CLI context

            while (ob_get_level() && @ob_end_clean());

            // log into file and output message in the console
            echo 'Error during initialization: \n';
            foreach (self::$initErrorMessages as $err) {
                @error_log($err->getFormatedMessage()."\n", 3, jApp::logPath('errors.log'));
                echo '* '.$err->getMessage().' ('.$err->getFile().' '.$err->getLine().")\n";
            }

            @error_log($errorLog->getFormatedMessage()."\n", 3, jApp::logPath('errors.log'));
            echo '* '.$message.' ('.$file.' '.$line.")\n";
        } else {
            // fatal error appeared during init, let's display an HTML page
            // since we don't know the request, we cannot return a response
            // corresponding to the expected protocol

            while (ob_get_level() && @ob_end_clean());

            // log into file
            foreach (self::$initErrorMessages as $err) {
                @error_log($err->getFormatedMessage()."\n", 3, jApp::logPath('errors.log'));
            }
            @error_log($errorLog->getFormatedMessage()."\n", 3, jApp::logPath('errors.log'));

            $msg = $errorLog->getMessage();
            if (!ini_get('display_errors') && strpos($msg, '--') !== false) {
                list($msg, $bin) = explode('--', $msg, 2); // remove confidential data
            }

            // if accept text/html
            if (isset($_SERVER['HTTP_ACCEPT']) && strstr($_SERVER['HTTP_ACCEPT'], 'text/html')) {
                if (file_exists(jApp::appPath('app/responses/error.en_US.php'))) {
                    $file = jApp::appPath('app/responses/error.en_US.php');
                } else {
                    $file = JELIX_LIB_CORE_PATH.'response/error.en_US.php';
                }
                $HEADTOP = '';
                $HEADBOTTOM = '';
                $BODYTOP = '';
                $BODYBOTTOM = htmlspecialchars($msg);
                $BASEPATH = jApp::urlBasePath();
                if ($BASEPATH == '') {
                    $BASEPATH = '/';
                }
                header('HTTP/1.1 500 Internal jelix error');
                header('Content-type: text/html');

                include $file;
            } else {
                // output text response
                header('HTTP/1.1 500 Internal jelix error');
                header('Content-type: text/plain');
                echo 'Error during initialization. '.$msg;
            }
        }

        exit(1);
    }
}
