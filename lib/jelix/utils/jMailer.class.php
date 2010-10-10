<?php
/**
* jMailer : based on PHPMailer - PHP email class
* Class for sending email using either
* sendmail, PHP mail(), SMTP, or files for tests.  Methods are
* based upon the standard AspEmail(tm) classes.
*
* @package     jelix
* @subpackage  utils
* @author      Laurent Jouanneau
* @contributor Kévin Lepeltier, GeekBay
* @copyright   2006-2010 Laurent Jouanneau
* @copyright   2008 Kévin Lepeltier, 2009 Geekbay
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require(LIB_PATH.'phpMailer/class.phpmailer.php');


/**
 * jMailer based on PHPMailer - PHP email transport class
 * @package jelix
 * @subpackage  utils
 * @author Laurent Jouanneau
 * @contributor Kévin Lepeltier
 * @copyright   2006-2008 Laurent Jouanneau
 * @copyright   2008 Kévin Lepeltier
 * @since 1.0b1
 * @see PHPMailer
 */
class jMailer extends PHPMailer {

    /**
     * the selector of the template used for the mail.
     * Use the Tpl() method to change this property
     * @var string
     */
    protected $bodyTpl = '';

    protected $lang;

    /**
     * the path of the directory where to store mails
     * if mailer is file.
    */
    public $filePath = '';

    /**
     * initialize some member
     */
    function __construct(){
        global $gJConfig;
        $this->lang = $gJConfig->locale;
        $this->CharSet = $gJConfig->charset;
        $this->Mailer = $gJConfig->mailer['mailerType'];
        $this->Hostname = $gJConfig->mailer['hostname'];
        $this->Sendmail = $gJConfig->mailer['sendmailPath'];
        $this->Host = $gJConfig->mailer['smtpHost'];
        $this->Port = $gJConfig->mailer['smtpPort'];
        $this->Helo = $gJConfig->mailer['smtpHelo'];
        $this->SMTPAuth = $gJConfig->mailer['smtpAuth'];
        $this->SMTPSecure = $gJConfig->mailer['smtpSecure'];
        $this->Username = $gJConfig->mailer['smtpUsername'];
        $this->Password = $gJConfig->mailer['smtpPassword'];
        $this->Timeout = $gJConfig->mailer['smtpTimeout'];
        if($gJConfig->mailer['webmasterEmail'] != '') {
            $this->From = $gJConfig->mailer['webmasterEmail'];
        }
        $this->FromName = $gJConfig->mailer['webmasterName'];
        $this->filePath = JELIX_APP_VAR_PATH.$gJConfig->mailer['filesDir'];
        parent::_construct(true);
    }

    /**
     * Sets Mailer to store message into files instead of sending it
     * useful for tests.
     * @return void
     */
    public function IsFile() {
        $this->Mailer = 'file';
    }


    /**
     * Find the name and address in the form "name<address@hop.tld>"
     * @param string $address
     * @return array( $name, $address )
     */
    function getAddrName($address, $kind = false) {
        if (preg_match ('`^([^<]*)<([^>]*)>$`', $address, $tab )) {
            $name = $tab[1];
            $addr = $tab[2];
        }
        else {
            $name = '';
            $addr = $address;
        }
        if (!$kind) {
            return array($addr, $name);
        }
        $this->AddAnAddress($kind, $addr, $name);
    }

    protected $tpl = null;

    /**
     * Adds a Tpl référence.
     * @param string $selector
     * @param boolean $isHtml  true if the content of the template is html.
     *                 IsHTML() is called.
     * @return jTpl the template object.
     */
    function Tpl( $selector, $isHtml = false ) {
        $this->bodyTpl = $selector;
        $this->tpl = new jTpl();
        $this->IsHTML($isHtml);
        return $this->tpl;
    }

    /**
     * Creates message and assigns Mailer. If the message is
     * not sent successfully then it returns false.  Use the ErrorInfo
     * variable to view description of the error.
     * @return bool
     */
    function Send() {
        $result = true;

        if (isset($this->bodyTpl) && $this->bodyTpl != "") {
            if ($this->tpl == null)
                $this->tpl = new jTpl();
            $mailtpl = $this->tpl;
            $metas = $mailtpl->meta( $this->bodyTpl , ($this->ContentType == 'text/html'?'html':'text') );

            if (isset($metas['Subject']))
                $this->Subject = $metas['Subject'];

            if (isset($metas['Priority']))
                $this->Priority = $metas['Priority'];
            $mailtpl->assign('Priority', $this->Priority );

            if (isset($metas['Sender']))
                $this->Sender = $metas['Sender'];
            $mailtpl->assign('Sender', $this->Sender );

            if (isset($metas['to']))
                foreach( $metas['to'] as $val )
                    $this->getAddrName( $val, 'to' );
            $mailtpl->assign('to', $this->to );

            if (isset($metas['cc']))
                foreach( $metas['cc'] as $val )
                    $this->getAddrName($val, 'cc');
            $mailtpl->assign('cc', $this->cc );

            if (isset($metas['bcc']))
                foreach( $metas['bcc'] as $val )
                    $this->getAddrName($val, 'bcc');
            $mailtpl->assign('bcc', $this->bcc);

            if (isset($metas['ReplyTo']))
                foreach( $metas['ReplyTo'] as $val )
                    $this->getAddrName($val, 'ReplyTo');
            $mailtpl->assign('ReplyTo', $this->ReplyTo );

            if (isset($metas['From'])) {
                $adr = $this->getAddrName($metas['From']);
                $this->SetFrom($adr[1], $adr[0]);
            }

            $mailtpl->assign('From', $this->From );
            $mailtpl->assign('FromName', $this->FromName );

            $this->Body = $mailtpl->fetch( $this->bodyTpl, ($this->ContentType == 'text/html'?'html':'text'));
        }

        // following lines are copied from the orginal file 
        
        if((count($this->to) + count($this->cc) + count($this->bcc)) < 1) {
          $this->SetError($this->Lang('provide_address'));
          return false;
        }
    
        /* Set whether the message is multipart/alternative */
        if(!empty($this->AltBody)) {
          $this->ContentType = 'multipart/alternative';
        }
    
        $this->error_count = 0; // reset errors
        $this->SetMessageType();
        if ($this->Mailer == 'file') {
            // to have all headers in the file, like cc, bcc...
            $this->Mailer = 'sendmail';
            $header = $this->CreateHeader();
            $this->Mailer = 'file';
        }
        else
            $header = $this->CreateHeader();
        $body = $this->CreateBody();
    
        if($body == '') {
            throw new phpmailerException($this->Lang('empty_message'), self::STOP_CRITICAL);
        }

        // digitally sign with DKIM if enabled
        if ($this->DKIM_domain && $this->DKIM_private) {
          $header_dkim = $this->DKIM_Add($header,$this->Subject,$body);
          $header = str_replace("\r\n","\n",$header_dkim) . $header;
        }

        /* Choose the mailer */
        switch($this->Mailer) {
          case 'sendmail':
            return $this->SendmailSend($header, $body);
            break;
          case 'smtp':
            return $this->SmtpSend($header, $body);
            break;
          case 'file':
            return $this->FileSend($header, $body);
            break;
          case 'mail':
          default:
            return $this->MailSend($header, $body);
            break;
        }
    
        return $result;
    }
    
    /**
     * store mail in file instead of sending it
     * @access public
     * @return bool
     */
    public function FileSend($header, $body) {
        return jFile::write ($this->getStorageFile(), $header.$body);
    }
    
    protected function getStorageFile() {
        return rtrim($this->filePath,'/').'/mail.'.$GLOBALS['gJCoord']->request->getIP().'-'.date('Ymd-His').'-'.uniqid(mt_rand(), true);
    }

    function SetLanguage($lang_type = 'en_EN', $lang_path = 'language/') {
        $this->lang = $lang_type;
    }

    protected function SetError($msg) {
        if (preg_match("/^([^#]*)#([^#]+)#(.*)$/", $msg, $m)) {
            $arg = null;
            if($m[1] != '')
                $arg = $m[1];
            if($m[3] != '')
                $arg = $m[3];
            if(strpos($m[2], 'WARNING:') !== false) {
                $locale = 'jelix~errors.mail.'.substr($m[2],8);
                if($arg !== null)
                    parent::SetError(jLocale::get($locale, $arg, $this->lang, $this->CharSet));
                else
                    parent::SetError(jLocale::get($locale, array(), $this->lang, $this->CharSet));
                return;
            }
            $locale = 'jelix~errors.mail.'.$m[2];
            if ($arg !== null) {
                throw new jException($locale, $arg, 1, $this->lang, $this->CharSet);
            }
            else
                throw new jException($locale, array(), 1, $this->lang, $this->CharSet);
        }
        else {
            throw new Exception($msg);
        }
    }

    /**
    * @return string
    */
    protected function Lang($key) {
        if($key == 'tls' || $key == 'authenticate')
            $key = 'WARNING:'.$key;
        return '#'.$key.'#';
    }
}


