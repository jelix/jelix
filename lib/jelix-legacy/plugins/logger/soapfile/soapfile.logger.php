<?php
/**
 * @package    jelix
 * @subpackage core_log_plugin
 *
 * @author     Laurent Jouanneau
 * @copyright  2017-2023 Laurent Jouanneau
 *
 * @see       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * logger storing soap message into several xml files.
 */
class soapfileLogger implements jILogger
{
    /**
     * @param jLogSoapMessage $message the message to log
     */
    public function logMessage($message)
    {
        $logPath = \Jelix\Core\App::logPath();
        if (!is_writable($logPath)) {
            return;
        }

        $type = $message->getCategory();
        if ($type != 'soap') {
            return;
        }
        $appConf = jApp::config();
        if (!$appConf) {
            return;
        }

        if (isset($appConf->soapfileLoggerMethods)) {
            $conf = &$appConf->soapfileLoggerMethods;
            if (isset($conf[$message->getFunctionName()])
                && !$conf[$message->getFunctionName()]
            ) {
                return;
            }
        }

        $date = new DateTime();
        $subPath = $logPath.'soap/'.$date->format('Ym').'/'.$date->format('dH').'/'.
            $date->format('His').'_'.$message->getFunctionName().'_';
        $chmod = $appConf->chmodFile;
        try {
            $file = $subPath.'headers.log';
            jFile::createDir(dirname($file), $chmod);

            file_put_contents($file, $message->getHeaders());
            @chmod($file, $chmod);

            $file = $subPath.'request.xml';
            file_put_contents($file, $message->getRequest());
            @chmod($file, $chmod);

            $file = $subPath.'response.xml';
            file_put_contents($file, $message->getResponse());
            @chmod($file, $chmod);
        } catch (Exception $e) {
            $file = jApp::logPath('errors.log');
            @error_log(date('Y-m-d H:i:s')."\t\tsoap error\t".$e->getMessage()."\n", 3, $file);
            @chmod($file, $chmod);
        }
    }

    public function output($response)
    {
    }
}
