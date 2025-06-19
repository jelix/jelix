<?php
/**
 * @package    jelix
 * @subpackage core_log
 *
 * @author     Laurent Jouanneau
 * @copyright  2006-2016 Laurent Jouanneau
 *
 * @see       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Logger\Output;

/**
 * logger storing message into syslog.
 */
class Syslog implements \Jelix\Logger\OutputInterface
{
    protected $catSyslog = array();

    protected $config;

    public function __construct()
    {
        $this->config = \Jelix\Core\App::config()->syslogLogger;
        $this->catSyslog = array(
            'error' => LOG_ERR,
            'warning' => LOG_WARNING,
            'notice' => LOG_NOTICE,
            'deprecated' => LOG_NOTICE,
            'strict' => LOG_NOTICE,
            'debug' => LOG_DEBUG,
        );
        $ident = strtr($this->config['ident'], array(
            '%sapi%' => php_sapi_name(),
            '%domain%' => \Jelix\Core\App::config()->domainName,
            '%pid%' => getmypid(), ));
        openlog(
            $ident,
            LOG_ODELAY | LOG_PERROR,
            $this->config['facility']
        );
    }

    /**
     * @param \Jelix\Logger\MessageInterface $message the message to log
     */
    public function logMessage($message)
    {
        $type = $message->getCategory();

        if (isset($this->catSyslog[$type])) {
            $priority = $this->catSyslog[$type];
        } else {
            $priority = LOG_INFO;
        }
        syslog($priority, $message->getFormatedMessage());
    }

    public function output($response)
    {
    }
}
