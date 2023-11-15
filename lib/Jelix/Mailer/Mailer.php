<?php
/**
 * jMailer : based on PHPMailer - PHP email class
 * Class for sending email using either
 * sendmail, PHP mail(), SMTP, or files for tests.  Methods are
 * based upon the standard AspEmail(tm) classes.
 *
 * @author      Laurent Jouanneau
 * @contributor Kévin Lepeltier, GeekBay, Julien Issler
 *
 * @copyright   2006-2023 Laurent Jouanneau
 * @copyright   2008 Kévin Lepeltier, 2009 Geekbay
 * @copyright   2010-2015 Julien Issler
 *
 * @see        https://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Mailer;

use Jelix\Core\App;
use Jelix\Core\Profiles;
use Jelix\Logger\Log;
use Jelix\Template\Template;

/**
 * jMailer based on PHPMailer - PHP email transport class.
 *
 * @author Laurent Jouanneau
 * @contributor Kévin Lepeltier
 *
 * @copyright   2006-2022 Laurent Jouanneau
 * @copyright   2008 Kévin Lepeltier
 *
 * @see PHPMailer
 */
class Mailer extends \PHPMailer\PHPMailer\PHPMailer
{
    const DEBUG_RECEIVER_CONFIG = 1;
    const DEBUG_RECEIVER_USER = 2;

    /**
     * the selector of the template used for the mail.
     * Use the Tpl() method to change this property.
     *
     * @var string
     */
    protected $bodyTpl = '';

    protected $defaultLang;

    /**
     * the path of the directory where to store mails
     * if mailer is file.
     */
    public $filePath = '';

    /**
     * indicates if mails should be copied into files, so the developer can
     * verify that all mails are sent.
     */
    protected $copyToFiles = false;

    protected $htmlImageBaseDir = '';

    protected $html2textConverter = false;

    /**
     * Debug mode.
     *
     * @var bool
     */
    protected $debugModeEnabled = false;

    /**
     * Debug mode for receivers. If activated, debugReceivers should be filled.
     *
     * @var bool
     */
    protected $debugReceiversEnabled = false;

    /**
     * @var string replacement for the From header
     */
    protected $debugFrom = '';

    /**
     * @var string replacement for the From header
     */
    protected $debugFromName = '';

    /**
     * @var int combination of DEBUG_RECEIVER_*
     */
    protected $debugReceiversType = 1;

    /**
     * List of addresses to send all emails. Addresses in "To".
     *
     * @var array
     */
    protected $debugReceivers = array();

    /**
     * List of valid addresses.
     *
     * Receivers for 'To' having these emails will not be replaced by debugReceivers
     * Receivers for 'Cc' and 'Bcc' having these emails will not be removed
     *
     * @var array
     */
    protected $debugReceiversWhiteList = array();

    protected $debugSubjectPrefix = '[DEBUG MODE]';

    protected $debugBodyIntroduction = 'This is an example of a message that could be send with following parameters, in the normal mode:';

    /**
     * initialize some member.
     *
     * @param mixed $exception
     */
    public function __construct($exception = true)
    {
        $config = App::config();
        $this->defaultLang = $config->locale;
        $this->CharSet = self::CHARSET_UTF8;
        if ($config->mailer['mailerType']) {
            $this->Mailer = $config->mailer['mailerType'];
        }
        $this->Hostname = $config->mailer['hostname'];
        $this->Sendmail = $config->mailer['sendmailPath'];
        $this->debugModeEnabled = $config->mailer['debugModeEnabled'];

        if (strtolower($this->Mailer) == 'smtp') {
            if ($this->debugModeEnabled) {
                $this->SMTPDebug = $config->mailer['debugSmtpLevel'];
            }
            if (isset($config->mailer['smtpProfile'])
                && $config->mailer['smtpProfile'] != ''
            ) {
                $smtp = Profiles::get('smtp', $config->mailer['smtpProfile']);
                $smtp = array_merge(array(
                    'host' => 'localhost',
                    'port' => 25,
                    'secure_protocol' => '', // or "unencrypted", "ssl", "tls". Empty means automatic TLS when it is possible
                    'helo' => '',
                    'auth_enabled' => false,
                    'username' => '',
                    'password' => '',
                    'timeout' => 10,
                ), $smtp);
                $this->Host = $smtp['host'];
                $this->Port = $smtp['port'];
                $this->Helo = $smtp['helo'];
                $this->SMTPAuth = $smtp['auth_enabled'];
                if ($smtp['secure_protocol'] == 'unencrypted') {
                    $this->SMTPSecure = '';
                    $this->SMTPAutoTLS = false;
                }
                else {
                    $this->SMTPSecure = $smtp['secure_protocol'];
                }
                $this->Username = $smtp['username'];
                $this->Password = $smtp['password'];
                $this->Timeout = $smtp['timeout'];
            } else {
                $this->Host = $config->mailer['smtpHost'];
                $this->Port = $config->mailer['smtpPort'];
                $this->Helo = $config->mailer['smtpHelo'];
                $this->SMTPAuth = $config->mailer['smtpAuth'];
                $this->SMTPSecure = $config->mailer['smtpSecure'];
                $this->Username = $config->mailer['smtpUsername'];
                $this->Password = $config->mailer['smtpPassword'];
                $this->Timeout = $config->mailer['smtpTimeout'];
            }
        }

        if ($config->mailer['webmasterEmail'] != '') {
            $this->From = $config->mailer['webmasterEmail'];
        }

        if ($config->mailer['returnPath'] != '') {
            $this->Sender = $config->mailer['returnPath'];
        }

        $this->FromName = $config->mailer['webmasterName'];
        $this->filePath = App::varPath($config->mailer['filesDir']);

        $this->copyToFiles = $config->mailer['copyToFiles'];

        if ($this->debugModeEnabled) {
            $this->debugReceivers = $config->mailer['debugReceivers'];
            if ($this->debugReceivers) {
                $this->debugReceiversEnabled = true;
                if (!is_array($this->debugReceivers)) {
                    $this->debugReceivers = array($this->debugReceivers);
                }
                if ($config->mailer['debugFrom']) {
                    $this->debugFrom = $config->mailer['debugFrom'];
                }
                if ($config->mailer['debugFromName']) {
                    $this->debugFromName = $config->mailer['debugFromName'];
                }
                if ($config->mailer['debugSubjectPrefix']) {
                    $this->debugSubjectPrefix = $config->mailer['debugSubjectPrefix'];
                }
                if ($config->mailer['debugBodyIntroduction']) {
                    $this->debugBodyIntroduction = $config->mailer['debugBodyIntroduction'];
                }
                $this->debugReceiversType = $config->mailer['debugReceiversType'];
                $this->debugReceiversWhiteList = $config->mailer['debugReceiversWhiteList'];
                if (!is_array($this->debugReceiversWhiteList)) {
                    $this->debugReceiversWhiteList = array($this->debugReceiversWhiteList);
                }
            }
        }

        parent::__construct(true);
        $this->Debugoutput = array($this, 'debugOutputCallback');
    }

    /**
     * Sets Mailer to store message into files instead of sending it
     * useful for tests.
     */
    public function IsFile()
    {
        $this->Mailer = 'file';
    }

    /**
     * Find the name and address in the form "name<address@hop.tld>".
     *
     * @param string $address
     * @param string $kind    One of 'to', 'cc', 'bcc', or 'ReplyTo'
     *
     * @return array contains $name, $address
     */
    public function getAddrName($address, $kind = false)
    {
        if (preg_match('`^([^<]*)<([^>]*)>$`', $address, $tab)) {
            $name = $tab[1];
            $addr = $tab[2];
        } else {
            $name = '';
            $addr = $address;
        }
        if ($kind) {
            $this->addOrEnqueueAnAddress($kind, $addr, $name);
        }

        return array($addr, $name);
    }

    protected $tpl;

    /**
     * Adds a Tpl référence.
     *
     * @param string $selector
     * @param bool   $isHtml   true if the content of the template is html.
     *                         IsHTML() is called.
     * @param callable|false  an html2text converter when the content is html.
     * By default, it uses the converter of jMailer, html2textKeepLinkSafe(). (since 1.6.17)
     * @param string $basedir            Absolute path to a base directory to prepend to relative paths to images (since 1.6.17)
     * @param mixed  $html2textConverter
     * @param mixed  $htmlImageBaseDir
     *
     * @return Template the template object
     */
    public function Tpl($selector, $isHtml = false, $html2textConverter = false, $htmlImageBaseDir = '')
    {
        $this->bodyTpl = $selector;
        $this->tpl = new Template();
        $this->isHTML($isHtml);
        $this->html2textConverter = $html2textConverter;
        $this->htmlImageBaseDir = $htmlImageBaseDir;

        return $this->tpl;
    }

    /**
     * Creates message and assigns Mailer. If the message is
     * not sent successfully then it returns false.  Use the ErrorInfo
     * variable to view description of the error.
     *
     * @return bool
     */
    public function send()
    {
        if (isset($this->bodyTpl) && $this->bodyTpl != '') {
            if ($this->tpl == null) {
                $this->tpl = new Template();
            }

            $mailtpl = $this->tpl;
            $metas = $mailtpl->meta($this->bodyTpl, ($this->ContentType == 'text/html' ? 'html' : 'text'));

            if (isset($metas['Subject']) && is_string($metas['Subject'])) {
                $this->Subject = $metas['Subject'];
            }

            if (isset($metas['Priority']) && is_numeric($metas['Priority'])) {
                $this->Priority = $metas['Priority'];
            }
            $mailtpl->assign('Priority', $this->Priority);

            if (isset($metas['Sender']) && is_string($metas['Sender'])) {
                $this->Sender = $metas['Sender'];
            }
            $mailtpl->assign('Sender', $this->Sender);

            foreach (array('to' => 'to',
                'cc' => 'cc',
                'bcc' => 'bcc',
                'ReplyTo' => 'Reply-To', ) as $prop => $propName) {
                if (isset($metas[$prop])) {
                    if (is_array($metas[$prop])) {
                        foreach ($metas[$prop] as $val) {
                            $this->getAddrName($val, $propName);
                        }
                    } elseif (is_string($metas[$prop])) {
                        $this->getAddrName($metas[$prop], $propName);
                    }
                }
                $mailtpl->assign($prop, $this->{$prop});
            }

            if (isset($metas['From'])) {
                $adr = $this->getAddrName($metas['From']);
                $this->setFrom($adr[0], $adr[1]);
            }

            $config = App::config();
            if (count($this->ReplyToQueue) == 0
                && count($this->ReplyTo) == 0
                && $config->mailer['replyTo']
            ) {   // Set default Reply-To header
                $this->addOrEnqueueAnAddress('Reply-To', $config->mailer['replyTo'], '');
                $mailtpl->assign('ReplyTo', $config->mailer['replyTo']);
            }

            $mailtpl->assign('From', $this->From);
            $mailtpl->assign('FromName', $this->FromName);

            if ($this->ContentType == 'text/html') {
                $converter = $this->html2textConverter ? $this->html2textConverter : array($this, 'html2textKeepLinkSafe');
                $this->msgHTML($mailtpl->fetch($this->bodyTpl, 'html'), $this->htmlImageBaseDir, $converter);
            } else {
                $this->Body = $mailtpl->fetch($this->bodyTpl, 'text');
            }
        }
        else {
            $config = App::config();
            if (count($this->ReplyToQueue) == 0
                && count($this->ReplyTo) == 0
                && $config->mailer['replyTo'])
            {   // Set default Reply-To header
                $this->addOrEnqueueAnAddress('Reply-To', $config->mailer['replyTo'], '');
            }
        }

        if ($this->debugReceiversEnabled) {
            $this->debugOverrideReceivers();
        }

        $result = parent::Send();

        if ($this->debugReceiversEnabled) {
            foreach ($this->debugOriginalValues as $f => $val) {
                $this->{$f} = $val;
            }
        }

        return $result;
    }

    protected $debugOriginalValues = array();

    protected function debugOverrideReceivers()
    {
        $this->debugOriginalValues = array();
        foreach (array('to', 'cc', 'bcc', 'all_recipients', 'RecipientsQueue',
            'ReplyTo', 'ReplyToQueue', 'Subject', 'Body', 'AltBody',
            'From', 'Sender', 'FromName', ) as $f) {
            $this->debugOriginalValues[$f] = $this->{$f};
        }

        if ($this->debugFrom) {
            $this->From = $this->debugFrom;
            $this->FromName = $this->debugFromName;
            $this->Sender = $this->debugFrom;
        }

        $this->clearAllRecipients();
        $this->clearReplyTos();

        if (count($this->debugReceiversWhiteList)) {
            // if some to/cc/bcc are in the white list, keep them
            foreach (array('to', 'cc', 'bcc') as $recipientType) {
                foreach ($this->debugOriginalValues[$recipientType] as $email) {
                    if (in_array($email[0], $this->debugReceiversWhiteList)) {
                        if (empty($email[1])) {
                            $this->addOrEnqueueAnAddress($recipientType, $email[0], '');
                        } else {
                            $this->addOrEnqueueAnAddress($recipientType, $email[0], $email[1]);
                        }
                    }
                }
            }
        }

        if (!count($this->to)) {
            // we replace the "to" field only if it is empty (original not in white list)
            $who = $this->debugReceiversType;
            if ($who & self::DEBUG_RECEIVER_USER) {
                if (class_exists('jAuth', false)
                    && \jAuth::isConnected()
                    && \jAuth::getUserSession()
                    && !empty(\jAuth::getUserSession()->login)
                ) {
                    $this->getAddrName(\jAuth::getUserSession()->login, 'to');
                } else {
                    $who = self::DEBUG_RECEIVER_CONFIG;
                }
            }

            if ($who & self::DEBUG_RECEIVER_CONFIG) {
                foreach ($this->debugReceivers as $email) {
                    $this->getAddrName($email, 'to');
                }
            }
        }

        $this->Subject = $this->debugSubjectPrefix.$this->Subject;

        $intro = $this->debugBodyIntroduction."\r\n\r\n";
        $introHtml = '<p>'.$this->debugBodyIntroduction."</p>\r\n<ul>\r\n";
        $intro .= ' - From: '.$this->debugOriginalValues['FromName'].' <'.$this->debugOriginalValues['From'].">\r\n";
        $introHtml .= '<li>From: '.$this->debugOriginalValues['FromName'].' &lt;'.$this->debugOriginalValues['From']."&gt;</li>\r\n";
        foreach (array('to', 'cc', 'bcc', 'ReplyTo') as $f) {
            $val = $this->debugOriginalValues[$f];
            if (!is_array($val)) {
                $val = array($val);
            }
            foreach ($val as $v) {
                if ($v[1]) {
                    $intro .= ' - '.$f.': '.$v[1].' <'.$v[0].">\r\n";
                    $introHtml .= '<li>'.$f.': '.$v[1].' &lt;'.$v[0]."&gt;</li>\r\n";
                } else {
                    $intro .= ' - '.$f.': '.$v[0]."\r\n";
                    $introHtml .= '<li>'.$f.': '.$v[0]."</li>\r\n";
                }
            }
        }
        $intro .= "\r\n-----------------------------------------------------------\r\n";
        $introHtml .= "</ul>\r\n<hr />\r\n";

        if ($this->ContentType == 'text/html') {
            $this->Body = $introHtml.$this->Body;
            $this->AltBody = $intro.$this->AltBody;
        } else {
            $this->Body = $intro.$this->Body;
        }
    }

    public function createHeader()
    {
        if ($this->Mailer == 'file') {
            // to have all headers in the file, like cc, bcc...
            $this->Mailer = 'sendmail';
            $headers = parent::CreateHeader();
            $this->Mailer = 'file';

            return $headers;
        }

        return parent::CreateHeader();
    }

    /**
     * store mail in file instead of sending it.
     *
     * @param mixed $header
     * @param mixed $body
     *
     * @return bool
     */
    protected function FileSend($header, $body)
    {
        return \jFile::write($this->getStorageFile(), $header.$body);
    }

    protected function getStorageFile()
    {
        return rtrim($this->filePath, '/').'/mail.'.$this->getUserIp().'-'.date('Ymd-His').'-'.uniqid(mt_rand(), true);
    }

    protected function getUserIp()
    {
        $coord = App::router();

        if ($coord && $coord->request) {
            return $coord->request->getIP();
        }
        if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return 'no-ip';
    }

    public function setLanguage($langcode = 'en', $lang_path = 'language/')
    {
        $lang = explode('_', $langcode);

        return parent::SetLanguage($lang[0], $lang_path);
    }

    protected function lang($key)
    {
        if (count($this->language) < 1) {
            $this->SetLanguage($this->defaultLang); // set the default language
        }

        return parent::lang($key);
    }

    protected function sendmailSend($header, $body)
    {
        if ($this->copyToFiles) {
            $this->copyMail($header, $body);
        }

        return parent::sendmailSend($header, $body);
    }

    protected function mailSend($header, $body)
    {
        if ($this->copyToFiles) {
            $this->copyMail($header, $body);
        }

        return parent::mailSend($header, $body);
    }

    protected function smtpSend($header, $body)
    {
        if ($this->copyToFiles) {
            $this->copyMail($header, $body);
        }

        return parent::smtpSend($header, $body);
    }

    protected function copyMail($header, $body)
    {
        $dir = rtrim($this->filePath, '/').'/copy-'.date('Ymd').'/';
        $filename = $dir.'mail-'.$this->getUserIp().'-'.date('Ymd-His').'-'.uniqid(mt_rand(), true);
        \jFile::write($filename, $header.$body);
    }

    /**
     * Convert HTML content to Text.
     *
     * Basically, it removes all tags (strip_tags). For <a> tags, it puts the
     * link in parenthesis, except <a> elements having the "notexpandlink".
     * class.
     *
     * @param string $html
     *
     * @return string
     *
     * @since 1.6.17
     */
    public function html2textKeepLinkSafe($html)
    {
        $regexp = "/<a\\s[^>]*href\\s*=\\s*([\"\\']??)([^\" >]*?)\\1([^>]*)>(.*)<\\/a>/siU";
        if (preg_match_all($regexp, $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (strpos($match[3], 'notexpandlink') !== false) {
                    continue;
                }
                // keep space inside parenthesis, because some email client my
                // take parenthesis as part of the link
                $html = str_replace($match[0], $match[4].' ( '.$match[2].' )', $html);
            }
        }
        $html = preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/si', '', $html);

        return html_entity_decode(
            trim(strip_tags($html)),
            ENT_QUOTES,
            $this->CharSet
        );
    }

    protected function setError($msg)
    {
        parent::setError($msg);
        Log::log("jMailer error:\n".$this->ErrorInfo, 'error');
    }

    public function debugOutputCallback($msg, $smtpDebugLevel)
    {
        Log::log("jMailer debug:\n".$msg);
    }
}
