<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2008 Laurent Jouanneau
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


class UTjmailer extends jUnitTestCase {

    public function setUp() {
    }
    
    public function testFileMail() {
        
        if (file_exists(JELIX_APP_VAR_PATH.'mails/mail.txt'));
            unlink(JELIX_APP_VAR_PATH.'mails/mail.txt');
        
        $mail = new testJMailer();
        $mail->From = 'toto@truc.local';
        $mail->FromName = 'Super Me';
        $mail->Sender = 'toto@truc.com';
        $mail->Subject = 'Email test';

        $mail->Body = 'This is a test mail';
        $mail->AddAddress('titi@machin.local');
        $mail->IsFile();
        $mail->Send();
        
        $this->assertEqual(JELIX_APP_VAR_PATH.'mails/', $mail->filePath);
        $this->assertEqual(JELIX_APP_VAR_PATH.'mails/mail.txt', $mail->getStorageFile2());
        
        
        if ($this->assertTrue(file_exists(JELIX_APP_VAR_PATH.'mails/mail.txt'))) {
            $content = file_get_contents(JELIX_APP_VAR_PATH.'mails/mail.txt');
            
            $this->assertTrue(strpos($content, 'Return-Path: toto@truc.com') !== false);
            $this->assertTrue(strpos($content, 'To: titi@machin.local') !== false);
            $this->assertTrue(strpos($content, 'From: Super Me <toto@truc.local>') !== false);
            $this->assertTrue(strpos($content, 'Subject: Email test') !== false);
            $this->assertTrue(strpos($content, 'Content-Transfer-Encoding: 8bit') !== false);
            $this->assertTrue(strpos($content, 'Content-Type: text/plain; charset="UTF-8"') !== false);
            $this->assertTrue(strpos($content, 'Email testThis is a test mail') !== false);

        }
    }
}

?>
