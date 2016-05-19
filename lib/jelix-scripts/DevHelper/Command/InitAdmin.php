<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2008-2011 Laurent Jouanneau
* @copyright   2015 Julien Issler
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
namespace Jelix\DevHelper\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitAdmin extends \Jelix\DevHelper\AbstractCommandForApp {

    protected function configure()
    {
        $this
            ->setName('app:initadmin')
            ->setDescription('Initialize the application with a web interface for administration')
            ->setHelp('It activates the module master_admin and configure jAuth and jAcl2')
            ->addArgument(
                'entrypoint',
                InputArgument::REQUIRED,
                'indicates the entry point to use for the administration'
            )
            ->addOption(
               'profile',
               null,
               InputOption::VALUE_REQUIRED,
               'indicate the name of the profile to use for the database connection',
               ''
            )
            ->addOption(
               'noauthdb',
               null,
               InputOption::VALUE_NONE,
               'Do not use and do not configure the driver \'db\' of jAuth'
            )
            ->addOption(
               'noacl2db',
               null,
               InputOption::VALUE_NONE,
               'Do not use and do not configure the driver \'db\' of jAcl2'
            )

        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadAppConfig();
        $entrypoint = $input->getArgument('entrypoint');
        if (($p = strpos($entrypoint, '.php')) !== false) {
            $entrypoint = substr($entrypoint,0,$p);
        }

        $ep = $this->getEntryPointInfo($entrypoint);

        if ($ep == null) {
            try {
                $options = array(
                    'entrypoint'=>$entrypoint,
                );
                $this->executeSubCommand('app:createentrypoint', $options, $output);
                $this->projectXml = null;
                $ep = $this->getEntryPointInfo($entrypoint);
            }
            catch (\Exception $e) {
                throw new \Exception("The entrypoint has not been created because of this error: ".$e->getMessage().". No other files have been created.\n");
            }
        }

        $installConfig = new \Jelix\IniFile\IniModifier(\jApp::configPath('installer.ini.php'));

        $inifile = new \Jelix\IniFile\MultiIniModifier(\jApp::mainConfigFile(),
                                              \jApp::configPath($ep['config']));

        $params = array();
        $this->createFile(\jApp::appPath('responses/adminHtmlResponse.class.php'),
                          'responses/adminHtmlResponse.class.php.tpl',
                          $params,
                          "Response for admin interface");
        $this->createFile(\jApp::appPath('responses/adminLoginHtmlResponse.class.php'),
                          'responses/adminLoginHtmlResponse.class.php.tpl',
                          $params,
                          "Response for login page");
        $inifile->setValue('html', 'adminHtmlResponse', 'responses');
        $inifile->setValue('htmlauth', 'adminLoginHtmlResponse', 'responses');


        $inifile->setValue('startModule', 'master_admin');
        $inifile->setValue('startAction', 'default:index');
        
        $repositoryPath = \jFile::parseJelixPath( 'lib:jelix-admin-modules' );
        $this->registerModulesDir('lib:jelix-admin-modules', $repositoryPath);

        $installConfig->setValue('jacl.installed', '0', $entrypoint);
        $inifile->setValue('jacl.access', '0', 'modules');
        $installConfig->setValue('jacldb.installed', '0', $entrypoint);
        $inifile->setValue('jacldb.access', '0', 'modules');

        $urlconf = $inifile->getValue($entrypoint, 'simple_urlengine_entrypoints', null, true);
        if ($urlconf === null || $urlconf == '') {
            // in defaultconfig
            $inifile->setValue($entrypoint, 'jacl2db_admin~*@classic, jauthdb_admin~*@classic, master_admin~*@classic, jpref_admin~*@classic', 'simple_urlengine_entrypoints', null, true);
            // in the config of the entry point
            $inifile->setValue($entrypoint, 'jacl2db~*@classic, jauth~*@classic, jacl2db_admin~*@classic, jauthdb_admin~*@classic, master_admin~*@classic, jpref_admin~*@classic', 'simple_urlengine_entrypoints');
        }
        else {
            $urlconf2 = $inifile->getValue($entrypoint, 'simple_urlengine_entrypoints');

            if(strpos($urlconf, 'jacl2db_admin~*@classic') === false)
                $urlconf .= ',jacl2db_admin~*@classic';
            if(strpos($urlconf, 'jauthdb_admin~*@classic') === false)
                $urlconf .= ',jauthdb_admin~*@classic';
            if(strpos($urlconf, 'master_admin~*@classic') === false)
                $urlconf .= ',master_admin~*@classic';
            if(strpos($urlconf2, 'jacl2db_admin~*@classic') === false)
                $urlconf2 .= ',jacl2db_admin~*@classic';
            if(strpos($urlconf2, 'jauthdb_admin~*@classic') === false)
                $urlconf2 .= ',jauthdb_admin~*@classic';
            if(strpos($urlconf2, 'master_admin~*@classic') === false)
                $urlconf2 .= ',master_admin~*@classic';
            if(strpos($urlconf2, 'jacl2db~*@classic') === false)
                $urlconf2 .= ',jacl2db~*@classic';
            if(strpos($urlconf2, 'jauth~*@classic') === false)
                $urlconf2 .= ',jauth~*@classic';
            if(strpos($urlconf2, 'jpref_admin~*@classic') === false)
                $urlconf2 .= ',jpref_admin~*@classic';

            $inifile->setValue($entrypoint, $urlconf, 'simple_urlengine_entrypoints', null, true);
            $inifile->setValue($entrypoint, $urlconf2, 'simple_urlengine_entrypoints');
        }

        if (null == $inifile->getValue($entrypoint, 'basic_significant_urlengine_entrypoints', null, true)) {
            $inifile->setValue($entrypoint, '1', 'basic_significant_urlengine_entrypoints',null,true);
        }

        $inifile->save();

        require_once (JELIX_LIB_PATH.'installer/jInstaller.class.php');

        $reporter = new \textInstallReporter(($output->isVerbose()? 'notice':'warning'));
        $installer = new \jInstaller($reporter);
        $installer->installModules(array('jauth','master_admin'), $entrypoint.'.php');

        $authini = new \Jelix\IniFile\IniModifier(\jApp::configPath($entrypoint.'/auth.coord.ini.php'));
        $authini->setValue('after_login','master_admin~default:index');
        $authini->setValue('timeout','30');
        $authini->save();

        $profile = $input->getOption('profile');

        if (!$input->getOption('noauthdb')) {
            if ($profile != '') {
                $authini->setValue('profile',$profile, 'Db');
            }
            $authini->save();
            $installer->setModuleParameters('jauthdb',array('defaultuser'=>true));
            $installer->installModules(array('jauthdb', 'jauthdb_admin'), $entrypoint.'.php');
        }
        else {
            $installConfig->setValue('jauthdb_admin.installed', '0', $entrypoint);
            $installConfig->save();
            $inifile->setValue('jauthdb_admin.access', '0', 'modules');
            $inifile->save();
        }

        if (!$input->getOption('noacl2db')) {
            if ($profile != '') {
                $dbini = new \Jelix\IniFile\IniModifier(\jApp::configPath('profiles.ini.php'));
                $dbini->setValue('jacl2_profile', $profile, 'jdb');
                $dbini->save();
            }
            $installer = new \jInstaller($reporter);
            $installer->setModuleParameters('jacl2db',array('defaultuser'=>true));
            $installer->installModules(array('jacl2db', 'jacl2db_admin'), $entrypoint.'.php');
        }
        else {
            $installConfig->setValue('jacl2db_admin.installed', '0', $entrypoint);
            $installConfig->save();
            $inifile->setValue('jacl2db_admin.access', '0', 'modules');
            $inifile->save();
        }

        $installer->installModules(array('jpref_admin'), $entrypoint.'.php');
    }
}
