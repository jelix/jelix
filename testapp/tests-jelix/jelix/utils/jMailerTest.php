<?php
/**
* @package     testapp
* @subpackage  testsjelix
* @author      Laurent Jouanneau
* @contributor
* @copyright   2008-2012 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.1
*/

class testJMailer extends jMailer {
    protected function getStorageFile() {
        return rtrim($this->filePath,'/').'/mail.txt';
    }

     function getStorageFile2() {
        return $this->getStorageFile();
    }
}


class jMailerTest extends jUnitTestCase {

    public function setUp () {
        self::initJelixConfig();
    }

    public function testFileMail() {

        if (file_exists(jApp::varPath().'mails/mail.txt')) {
            unlink(jApp::varPath() . 'mails/mail.txt');
        }
        $mail = new testJMailer();
   
        $mail->From = 'toto@truc.local';
        $mail->FromName = 'Super Me';
        $mail->Sender = 'toto@truc.com';
        $mail->Subject = 'Email test';

        $mail->Body = 'This is a test mail';
        $mail->addAddress('titi@machin.local');
        $mail->addAddress('toto@machin.local');
        $mail->IsFile();
        $mail->Send();

        $this->assertEquals(jApp::varPath('mails/'), $mail->filePath);
        $this->assertEquals(jApp::varPath('mails/mail.txt'), $mail->getStorageFile2());

        $this->assertTrue(file_exists(jApp::varPath('mails/mail.txt')));
        $content = file_get_contents(jApp::varPath('mails/mail.txt'));

        $this->assertTrue(strpos($content, 'To: titi@machin.local, toto@machin.local') !== false);
        $this->assertTrue(strpos($content, 'From: Super Me <toto@truc.local>') !== false);
        $this->assertTrue(strpos($content, 'Subject: Email test') !== false);
        $this->assertTrue(strpos($content, 'Content-Type: text/plain; charset=UTF-8') !== false);
        $this->assertTrue(strpos($content, 'This is a test mail') !== false);
    }


    public function testDebugMail() {

        if (file_exists(jApp::varPath().'mails/mail.txt')) {
            unlink(jApp::varPath() . 'mails/mail.txt');
        }

        $config = jApp::config();
        $config->mailer['debugModeEnabled'] = true;
        $config->mailer['debugReceivers'] = array('debug1@machin.local', 'debug2@machin.local') ;
        $config->mailer['debugSubjectPrefix'] = '[MyDEBUG] ' ;
        $config->mailer['debugBodyIntroduction'] = 'Hello this is debug' ;

        $mail = new testJMailer();

        $mail->From = 'toto@truc.local';
        $mail->FromName = 'Super Me';
        $mail->Sender = 'toto@truc.com';
        $mail->Subject = 'Email test';

        $mail->Body = 'This is a test mail';
        $mail->addAddress('titi@machin.local');
        $mail->addAddress('toto@machin1.local');
        $mail->addCC('robert@machin2.local');
        $mail->isFile();
        $mail->send();
        $config->mailer['debugModeEnabled'] = false;

        $this->assertEquals(jApp::varPath().'mails/', $mail->filePath);
        $this->assertEquals(jApp::varPath().'mails/mail.txt', $mail->getStorageFile2());

        $this->assertTrue(file_exists(jApp::varPath().'mails/mail.txt'));
        $content = file_get_contents(jApp::varPath().'mails/mail.txt');

        $this->assertTrue(strpos($content, 'To: debug1@machin.local, debug2@machin.local') !== false);
        $this->assertTrue(strpos($content, 'From: Super Me <toto@truc.local>') !== false);
        $this->assertTrue(strpos($content, 'Subject: [MyDEBUG] Email test') !== false);
        $this->assertTrue(strpos($content, 'Content-Type: text/plain; charset=UTF-8') !== false);
        $this->assertTrue(strpos($content, "Hello this is debug

 - From: Super Me <toto@truc.local>
 - to: titi@machin.local
 - to: toto@machin1.local
 - cc: robert@machin2.local

-----------------------------------------------------------
This is a test mail") !== false);

    }

    public function testDebugHtmlMail() {

        if (file_exists(jApp::varPath().'mails/mail.txt')) {
            unlink(jApp::varPath() . 'mails/mail.txt');
        }

        $config = jApp::config();
        $config->mailer['debugModeEnabled'] = true;
        $config->mailer['debugReceivers'] = array('debug1@machin.local', 'debug2@machin.local') ;
        $config->mailer['debugSubjectPrefix'] = '[MyDEBUG] ' ;
        //$config->mailer['debugBodyIntroduction'] = 'Hello this is debug' ;

        $mail = new testJMailer();

        $mail->From = 'toto@truc.local';
        $mail->FromName = 'Super Me';
        $mail->Sender = 'toto@truc.com';
        $mail->Subject = 'Email test';

        $mail->msgHtml("<h1>Yeaar!</h1>\n\n<p>This is a test mail</p>");
        $mail->addAddress('titi@machin.local');
        $mail->addAddress('toto@machin1.local');
        $mail->addCC('robert@machin2.local');
        $mail->isFile();
        $mail->send();
        $config->mailer['debugModeEnabled'] = false;

        $this->assertEquals(jApp::varPath().'mails/', $mail->filePath);
        $this->assertEquals(jApp::varPath().'mails/mail.txt', $mail->getStorageFile2());

        $this->assertTrue(file_exists(jApp::varPath().'mails/mail.txt'));
        $content = file_get_contents(jApp::varPath().'mails/mail.txt');

        $this->assertTrue(strpos($content, 'To: debug1@machin.local, debug2@machin.local') !== false);
        $this->assertTrue(strpos($content, 'Cc: robert@machin2.local') === false);
        $this->assertTrue(strpos($content, 'From: Super Me <toto@truc.local>') !== false);
        $this->assertTrue(strpos($content, 'Subject: [MyDEBUG] Email test') !== false);
        $this->assertTrue(strpos($content, 'Content-Type: multipart/alternative;') !== false);
        $this->assertTrue(strpos($content, "This is an example of a message that could be send with following parameters, in the normal mode:

 - From: Super Me <toto@truc.local>
 - to: titi@machin.local
 - to: toto@machin1.local
 - cc: robert@machin2.local

-----------------------------------------------------------
Yeaar!

This is a test mail") !== false);

        $this->assertTrue(strpos($content, "<p>This is an example of a message that could be send with following parameters, in the normal mode:</p>
<ul>
<li>From: Super Me &lt;toto@truc.local&gt;</li>
<li>to: titi@machin.local</li>
<li>to: toto@machin1.local</li>
<li>cc: robert@machin2.local</li>
</ul>
<hr />
<h1>Yeaar!</h1>

<p>This is a test mail</p>") !== false);

    }

    public function testDebugHtmlMailWithWhiteListForCc() {

        if (file_exists(jApp::varPath().'mails/mail.txt')) {
            unlink(jApp::varPath() . 'mails/mail.txt');
        }

        $config = jApp::config();
        $config->mailer['debugModeEnabled'] = true;
        $config->mailer['debugReceivers'] = array('debug1@machin.local', 'debug2@machin.local') ;
        $config->mailer['debugSubjectPrefix'] = '[MyDEBUG] ' ;
        $config->mailer['debugReceiversWhiteList'] = array('robert@machin2.local');
        //$config->mailer['debugBodyIntroduction'] = 'Hello this is debug' ;

        $mail = new testJMailer();

        $mail->From = 'toto@truc.local';
        $mail->FromName = 'Super Me';
        $mail->Sender = 'toto@truc.com';
        $mail->Subject = 'Email test';

        $mail->msgHtml("<h1>Yeaar!</h1>\n\n<p>This is a test mail</p>");
        $mail->addAddress('titi@machin.local');
        $mail->addAddress('toto@machin1.local');
        $mail->addCC('robert@machin2.local');
        $mail->isFile();
        $mail->send();
        $config->mailer['debugModeEnabled'] = false;

        $this->assertEquals(jApp::varPath().'mails/', $mail->filePath);
        $this->assertEquals(jApp::varPath().'mails/mail.txt', $mail->getStorageFile2());

        $this->assertTrue(file_exists(jApp::varPath().'mails/mail.txt'));
        $content = file_get_contents(jApp::varPath().'mails/mail.txt');

        $this->assertTrue(strpos($content, 'To: debug1@machin.local, debug2@machin.local') !== false);
        $this->assertTrue(strpos($content, 'Cc: robert@machin2.local') !== false);
        $this->assertTrue(strpos($content, 'From: Super Me <toto@truc.local>') !== false);
        $this->assertTrue(strpos($content, 'Subject: [MyDEBUG] Email test') !== false);
        $this->assertTrue(strpos($content, 'Content-Type: multipart/alternative;') !== false);
        $this->assertTrue(strpos($content, "This is an example of a message that could be send with following parameters, in the normal mode:

 - From: Super Me <toto@truc.local>
 - to: titi@machin.local
 - to: toto@machin1.local
 - cc: robert@machin2.local

-----------------------------------------------------------
Yeaar!

This is a test mail") !== false);
        $this->assertTrue(strpos($content, "<p>This is an example of a message that could be send with following parameters, in the normal mode:</p>
<ul>
<li>From: Super Me &lt;toto@truc.local&gt;</li>
<li>to: titi@machin.local</li>
<li>to: toto@machin1.local</li>
<li>cc: robert@machin2.local</li>
</ul>
<hr />
<h1>Yeaar!</h1>

<p>This is a test mail</p>") !== false);

    }



    public function testDebugHtmlMailWithWhiteListForTo() {

        if (file_exists(jApp::varPath().'mails/mail.txt')) {
            unlink(jApp::varPath() . 'mails/mail.txt');
        }

        $config = jApp::config();
        $config->mailer['debugModeEnabled'] = true;
        $config->mailer['debugReceivers'] = array('debug1@machin.local', 'debug2@machin.local') ;
        $config->mailer['debugSubjectPrefix'] = '[MyDEBUG] ' ;
        $config->mailer['debugReceiversWhiteList'] = array('toto@machin1.local');
        //$config->mailer['debugBodyIntroduction'] = 'Hello this is debug' ;

        $mail = new testJMailer();

        $mail->From = 'toto@truc.local';
        $mail->FromName = 'Super Me';
        $mail->Sender = 'toto@truc.com';
        $mail->Subject = 'Email test';

        $mail->msgHtml("<h1>Yeaar!</h1>\n\n<p>This is a test mail</p>");
        $mail->addAddress('titi@machin.local');
        $mail->addAddress('toto@machin1.local');
        $mail->addCC('robert@machin2.local');
        $mail->isFile();
        $mail->send();
        $config->mailer['debugModeEnabled'] = false;

        $this->assertEquals(jApp::varPath().'mails/', $mail->filePath);
        $this->assertEquals(jApp::varPath().'mails/mail.txt', $mail->getStorageFile2());

        $this->assertTrue(file_exists(jApp::varPath().'mails/mail.txt'));
        $content = file_get_contents(jApp::varPath().'mails/mail.txt');

        $this->assertTrue(strpos($content, 'To: toto@machin1.local') !== false);
        $this->assertTrue(strpos($content, 'Cc: robert@machin2.local') === false);
        $this->assertTrue(strpos($content, 'From: Super Me <toto@truc.local>') !== false);
        $this->assertTrue(strpos($content, 'Subject: [MyDEBUG] Email test') !== false);
        $this->assertTrue(strpos($content, 'Content-Type: multipart/alternative;') !== false);
        $this->assertTrue(strpos($content, "This is an example of a message that could be send with following parameters, in the normal mode:

 - From: Super Me <toto@truc.local>
 - to: titi@machin.local
 - to: toto@machin1.local
 - cc: robert@machin2.local

-----------------------------------------------------------
Yeaar!

This is a test mail") !== false);
        $this->assertTrue(strpos($content, "<p>This is an example of a message that could be send with following parameters, in the normal mode:</p>
<ul>
<li>From: Super Me &lt;toto@truc.local&gt;</li>
<li>to: titi@machin.local</li>
<li>to: toto@machin1.local</li>
<li>cc: robert@machin2.local</li>
</ul>
<hr />
<h1>Yeaar!</h1>

<p>This is a test mail</p>") !== false);

    }



}


