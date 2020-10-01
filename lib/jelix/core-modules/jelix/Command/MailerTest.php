<?php
/**
 * @package    jelix-modules
 * @subpackage jelix-module
 * @author       Laurent Jouanneau
 *
 * @copyright    2020 Laurent Jouanneau
 *
 * @link         https://jelix.org
 * @licence      http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */
namespace Jelix\JelixModule\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * controller to test email configuration
 */
class MailerTest extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('mailer:test')
            ->setDescription('test email configuration')
            ->setHelp('')
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Email recipient where to send the email for tests'
            )
            ->addArgument(
                'appname',
                InputArgument::OPTIONAL,
                'An application name to use into the email. By default: the domaine name of the application'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');

        $mail = new \jMailer();
        $mail->From = \jApp::config()->mailer['webmasterEmail'];
        $mail->FromName = \jApp::config()->mailer['webmasterName'];
        $mail->Sender = \jApp::config()->mailer['webmasterEmail'];
        $mail->Subject = 'Email test';
        $mail->AddAddress($email);
        $mail->isHtml(true);

        $domain = $input->getArgument('appname');
        if ($domain == '') {
            if (\jApp::config()->domainName != '') {
                $domain = \jApp::config()->domainName;
            }
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
            $output->writeln("It seems something goes wrong during the message sending.");
            return 1;
        }

        $output->writeln("Message has been sent.");
        return 0;
    }
}
