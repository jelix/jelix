<?php

/**
* page for Installation wizard
*
* @package     InstallWizard
* @subpackage  pages
* @author      Laurent Jouanneau
* @copyright   2010 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class confmailWizPage extends installWizardPage {
    
    /**
     * action to display the page
     * @param \Jelix\Castor\Castor $tpl the template container
     */
    function show (\Jelix\Castor\Castor $tpl) {
        if (!isset($_SESSION['confmail'])) {
            $_SESSION['confmail'] = $this->loadconf();
        }

        $tpl->assign($_SESSION['confmail']);

        return true;
    }
    
    /**
     * action to process the page after the submit
     */
    function process() {
        $ini = new  \Jelix\IniFile\IniModifier(jApp::varConfigPath('localconfig.ini.php'));

        $errors = array();
        $_SESSION['confmail']['webmasterEmail'] = trim($_POST['webmasterEmail']);
        if ($_SESSION['confmail']['webmasterEmail'] == '') {
            $errors[] = $this->locales['error.missing.webmasterEmail'];
        }
        else {
            $ini->setValue('webmasterEmail',$_SESSION['confmail']['webmasterEmail'], 'mailer');
        }
        $_SESSION['confmail']['webmasterName'] = trim($_POST['webmasterName']);

         $mailerType = $_SESSION['confmail']['mailerType'] = $_POST['mailerType'];
         $ini->setValue('mailerType',$mailerType, 'mailer');

        if ($mailerType == 'sendmail') {
            $_SESSION['confmail']['sendmailPath'] = trim($_POST['sendmailPath']);
            if ($_SESSION['confmail']['sendmailPath'] == '') {
                $errors[] = $this->locales['error.missing.sendmailPath'];
            }
            else {
                $ini->setValue('sendmailPath',$_SESSION['confmail']['sendmailPath'], 'mailer');
            }
        }
        elseif ($mailerType == 'smtp') {
            $mailerProfile = $ini->getValue('smtpProfile','mailer');
            if (!$mailerProfile) {
                $mailerProfile = 'mailer';
                $ini->setValue('smtpProfile', $mailerProfile, 'mailer');
            }
            $mailerProfile = 'smtp:'.$mailerProfile;
            $profilesIni = $this->getProfilesIni();

            $_SESSION['confmail']['smtpHost'] = trim($_POST['smtpHost']);
            if ($_SESSION['confmail']['smtpHost'] == '') {
                $errors[] = $this->locales['error.missing.smtpHost'];
            }
            else {
                $profilesIni->setValue('host', $_SESSION['confmail']['smtpHost'] , $mailerProfile);
            }
            $smtpPort = $_SESSION['confmail']['smtpPort'] = trim($_POST['smtpPort']);
            if ($smtpPort != '' && intval($smtpPort) == 0) {
                $errors[] = $this->locales['error.smtpPort'];
            }
            else {
                $profilesIni->setValue('port',$smtpPort , $mailerProfile);
            }
            $_SESSION['confmail']['smtpSecure'] = trim($_POST['smtpSecure']);
            $profilesIni->setValue('secure_protocol', $_SESSION['confmail']['smtpSecure'], $mailerProfile);

            if (isset($_POST['smtpAuth'])) {
                $smtpAuth = $_SESSION['confmail']['smtpAuth'] = trim($_POST['smtpAuth']);
                $smtpAuth = ($smtpAuth != '');
            }
            else $smtpAuth= false;

            $profilesIni->setValue('auth_enabled',$smtpAuth , $mailerProfile);
            if ($smtpAuth) {
                $_SESSION['confmail']['smtpUsername'] = trim($_POST['smtpUsername']);
                if ($_SESSION['confmail']['smtpUsername'] == '') {
                    $errors[] = $this->locales['error.missing.smtpUsername'];
                }
                else {
                    $profilesIni->setValue('username', $_SESSION['confmail']['smtpUsername'] , $mailerProfile);
                }
                $_SESSION['confmail']['smtpPassword'] = trim($_POST['smtpPassword']);
                if ($_SESSION['confmail']['smtpPassword'] == '') {
                    $errors[] = $this->locales['error.missing.smtpPassword'];
                }
                else {
                    $profilesIni->setValue('password', $_SESSION['confmail']['smtpPassword'], $mailerProfile);
                }
            }
            $profilesIni->setValue('helo', $_SESSION['confmail']['smtpHelo'], $mailerProfile);
            $profilesIni->setValue('timeout', $_SESSION['confmail']['smtpTimeout'], $mailerProfile);

            if (!count($errors)) {
                $profilesIni->save();
            }
            $ini->removeValue('smtpHost', 'mailer');
            $ini->removeValue('smtpPort', 'mailer');
            $ini->removeValue('smtpHelo', 'mailer');
            $ini->removeValue('smtpSecure', 'mailer');
            $ini->removeValue('smtpAuth', 'mailer');
            $ini->removeValue('smtpUsername', 'mailer');
            $ini->removeValue('smtpPassword', 'mailer');
            $ini->removeValue('smtpTimeout', 'mailer');

        }
        if (count($errors)) {
            $_SESSION['confmail']['errors'] = $errors;
            return false;
        }
        $ini->save();
        unset($_SESSION['confmail']);
        return 0;
    }


    protected function loadconf() {
        $ini = new  \Jelix\IniFile\IniModifierArray( array(
                'default' => jConfig::getDefaultConfigFile(),
                'main' => jApp::mainConfigFile(),
                'local' => jApp::varConfigPath('localconfig.ini.php')
            )
        );

        $emailConfig = array(
            'webmasterEmail'=>$ini->getValue('webmasterEmail','mailer'),
            'webmasterName'=>$ini->getValue('webmasterName','mailer'),
            'mailerType'=>$ini->getValue('mailerType','mailer'),
            'hostname'=>$ini->getValue('hostname','mailer'),
            'sendmailPath'=>$ini->getValue('sendmailPath','mailer'),
            'smtpHost'=>$ini->getValue('smtpHost','mailer'),
            'smtpPort'=>$ini->getValue('smtpPort','mailer'),
            'smtpSecure'=>$ini->getValue('smtpSecure','mailer'),
            'smtpHelo'=>$ini->getValue('smtpHelo','mailer'),
            'smtpAuth'=>$ini->getValue('smtpAuth','mailer'),
            'smtpUsername'=>$ini->getValue('smtpUsername','mailer'),
            'smtpPassword'=>$ini->getValue('smtpPassword','mailer'),
            'smtpTimeout'=>$ini->getValue('smtpTimeout','mailer'),
            'errors'=>array()
        );

        if (!in_array($emailConfig['mailerType'], array('mail','sendmail','smtp'))) {
            $emailConfig['mailerType'] = 'mail';
        }

        if ($emailConfig['mailerType'] == 'smtp' && $ini->getValue('smtpProfile','mailer')) {
            $mailerProfile = 'smtp:'.$ini->getValue('smtpProfile','mailer');
            $profilesIni = $this->getProfilesIni();
            $smtp = $profilesIni->getValues($mailerProfile);
            $smtp = array_merge(array(
                'host' => 'localhost',
                'port' => 25,
                'secure_protocol' => '', // or "ssl", "tls"
                'helo' => '',
                'auth_enabled' => false,
                'username' => '',
                'password' => '',
                'timeout' => 10
            ), $smtp);

            $emailConfig['smtpHost'] = $smtp['host'];
            $emailConfig['smtpPort'] = $smtp['port'];
            $emailConfig['smtpSecure'] = $smtp['secure_protocol'];
            $emailConfig['smtpHelo'] = $smtp['helo'];
            $emailConfig['smtpAuth'] = $smtp['auth_enabled'];
            $emailConfig['smtpUsername'] = $smtp['username'];
            $emailConfig['smtpPassword'] = $smtp['password'];
            $emailConfig['smtpTimeout'] = $smtp['timeout'];
        }

        return $emailConfig;
    }


    protected function getProfilesIni()  {
        $file = jApp::varConfigPath('profiles.ini.php');

        if (!file_exists($file) && file_exists(jApp::varConfigPath('profiles.ini.php.dist'))) {
            copy(jApp::varConfigPath('profiles.ini.php.dist'), $file);
        }

        return new \Jelix\IniFile\IniModifier($file, ";<?php die(''); ?>
;for security reasons, don't remove or modify the first line

");
    }

}