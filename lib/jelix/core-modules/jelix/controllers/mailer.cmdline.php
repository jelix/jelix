<?php
/**
 * @author       Laurent Jouanneau <laurent@xulfr.org>
 * @contributor
 *
 * @copyright    2007-2019 Laurent Jouanneau
 *
 * @link         http://jelix.org
 * @licence      http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */

/**
 * controller for the password reset process, initiated by an admin
 */
class mailerCtrl extends \jControllerCmdLine
{

    /**
     * Options to the command line
     *  'method_name' => array('-option_name' => true/false)
     * true means that a value should be provided for the option on the command line.
     */
    protected $allowed_options = array(
    );

    /**
     * Parameters for the command line
     * 'method_name' => array('parameter_name' => true/false)
     * false means that the parameter is optional. All parameters which follow an optional parameter
     * is optional.
     */
    protected $allowed_parameters = array(
        'test' => array(
            'email' => true,
            'appname' => false
        ),
    );

    public function test()
    {
        $rep = $this->getResponse('cmdline');
        $email = $this->param('email');

        $mail = new \jMailer();
        $mail->From = \jApp::config()->mailer['webmasterEmail'];
        $mail->FromName = \jApp::config()->mailer['webmasterName'];
        $mail->Sender = \jApp::config()->mailer['webmasterEmail'];
        $mail->Subject = 'Email test';
        $mail->AddAddress($email);
        $mail->isHtml(true);

        $domain = $this->param('appname');
        if ($domain == '') {
            $domain = $this->request->getDomainName();
            if ($domain == '') {
                $domain = gethostname();
                if ($domain == '') {
                    $domain = 'Unknown app';
                }
            }
        }

        $tpl = new \jTpl();
        $tpl->assign('domain_name', $domain);
        $body = $tpl->fetch('jelix~email_test');
        $mail->msgHTML($body, '', array($mail, 'html2textKeepLinkSafe'));
        if (!$mail->Send()) {
            $rep->addContent("It seems something goes wrong during the message sending.\n");
        }
        else {
            $rep->addContent("Message has been sent.\n");
        }


        return $rep;
    }
}
