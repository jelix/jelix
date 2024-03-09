<?php
/**
 * @package    jelix
 * @subpackage core_log
 *
 * @author     Laurent Jouanneau
 * @copyright  2006-2014 Laurent Jouanneau
 *
 * @see       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Logger\Output;

/**
 * logger storing message into a file.
 */
class File implements \Jelix\Logger\OutputInterface
{
    /**
     * @param \Jelix\Logger\MessageInterface $message the message to log
     */
    public function logMessage($message)
    {
        if (!is_writable(\Jelix\Core\App::logPath())) {
            return;
        }

        $type = $message->getCategory();
        $appConf = \Jelix\Core\App::config();

        if ($appConf) {
            $conf = &\Jelix\Core\App::config()->fileLogger;
            if (!isset($conf[$type])) {
                return;
            }
            $f = $conf[$type];
            $f = str_replace('%m%', date('m'), $f);
            $f = str_replace('%Y%', date('Y'), $f);
            $f = str_replace('%d%', date('d'), $f);
            $f = str_replace('%H%', date('H'), $f);
        } else {
            $f = 'errors.log';
        }

        $coord = \Jelix\Core\App::router();
        if ($coord && $coord->request) {
            $ip = $coord->request->getIP();
        } else {
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        }
        $f = str_replace('%ip%', $ip, $f);

        try {

            if (!preg_match('/^([\\w\\.\\/]+)$/', $f, $m)) {
                throw new \Exception("Invalid file name for file logger name {$f}");
            }
            $file = \Jelix\Core\App::logPath($f);
            if ($message instanceof \Jelix\Logger\Message\Error) {
                @error_log($message->getFormatedMessage()."\n", 3, $file);
            }
            else {
                @error_log(date('Y-m-d H:i:s')."\t".$ip."\t{$type}\t".$message->getFormatedMessage()."\n", 3, $file);
            }
            @chmod($file, \Jelix\Core\App::config()->chmodFile);
        } catch (\Exception $e) {
            $file = \Jelix\Core\App::logPath('errors.log');
            @error_log(date('Y-m-d H:i:s')."\t".$ip."\terror\t".$e->getMessage()."\n", 3, $file);
            @chmod($file, \Jelix\Core\App::config()->chmodFile);
        }
    }

    public function output($response)
    {
    }
}
