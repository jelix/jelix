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
use Jelix\DevHelper\AbstractCommand;



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

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        AbstractCommand::execute($input, $output);
        $entrypoint = $input->getArgument('entrypoint');
        if (($p = strpos($entrypoint, '.php')) !== false) {
            $entrypoint = substr($entrypoint,0,$p);
        }
        $this->selectedEntryPointId = $entrypoint;
        $this->loadAppConfig($this->selectedEntryPointId);
        return $this->_execute($input, $output);
    }

    protected function _execute(InputInterface $input, OutputInterface $output) {

        $entrypoint = $this->selectedEntryPointId;
        try {

            $ep = $this->getEntryPointInfo($entrypoint);
        }
        catch (\Exception $e) {
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

        $installConfig = new \Jelix\IniFile\IniModifier(\jApp::varConfigPath('installer.ini.php'));

        $mainIniFile = new \Jelix\IniFile\MultiIniModifier(\jConfig::getDefaultConfigFile(), \jApp::mainConfigFile());
        $inifile = new \Jelix\IniFile\MultiIniModifier($mainIniFile,
                                              \jApp::appConfigPath($ep['config']));

        $params = array();
        $this->createFile(\jApp::appPath('app/responses/adminHtmlResponse.class.php'),
                          'app/responses/adminHtmlResponse.class.php.tpl',
                          $params,
                          "Response for admin interface");
        $this->createFile(\jApp::appPath('app/responses/adminLoginHtmlResponse.class.php'),
                          'app/responses/adminLoginHtmlResponse.class.php.tpl',
                          $params,
                          "Response for login page");
        $inifile->setValue('html', 'adminHtmlResponse', 'responses');
        $inifile->setValue('htmlauth', 'adminLoginHtmlResponse', 'responses');

        $repositoryPath = \jFile::parseJelixPath( 'lib:jelix-admin-modules' );
        $this->registerModulesDir('lib:jelix-admin-modules', $repositoryPath);


        $installConfig->setValue('jacl.installed', '0', 'modules');
        $inifile->setValue('jacl.access', '0', 'modules');
        $installConfig->setValue('jacldb.installed', '0', 'modules');
        $inifile->setValue('jacldb.access', '0', 'modules');
        $inifile->save();

        $urlsFile = \jApp::appConfigPath($inifile->getValue('significantFile', 'urlengine'));
        $xmlMap = new \Jelix\Routing\UrlMapping\XmlMapModifier($urlsFile, true);
        $xmlEp = $xmlMap->getEntryPoint($entrypoint);
        $xmlEp->addUrlAction('/', 'master_admin', 'default:index', null, null, array('default'=>true));
        $xmlEp->addUrlModule('', 'master_admin');
        $xmlEp->addUrlInclude('/admin/acl', 'jacl2db_admin', 'urls.xml');
        $xmlEp->addUrlInclude('/admin/auth', 'jauthdb_admin', 'urls.xml');
        $xmlEp->addUrlInclude('/admin/pref', 'jpref_admin', 'urls.xml');
        $xmlEp->addUrlInclude('/auth', 'jauth', 'urls.xml');
        $xmlMap->save();

        require_once (JELIX_LIB_PATH.'installer/jInstaller.class.php');

        $reporter = new \textInstallReporter(($output->isVerbose()? 'notice':'warning'));
        $installer = new \jInstaller($reporter);
        $installer->installModules(array('jauth','master_admin'));

        $authini = new \Jelix\IniFile\IniModifier(\jApp::varConfigPath($entrypoint.'/auth.coord.ini.php'));
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
            $installer->installModules(array('jauthdb', 'jauthdb_admin'));
        }
        else {
            $installConfig->setValue('jauthdb_admin.installed', '0', 'modules');
            $installConfig->save();
            $inifile->setValue('jauthdb_admin.access', '0', 'modules');
            $inifile->save();
        }

        if (!$input->getOption('noacl2db')) {
            if ($profile != '') {
                $dbini = new \Jelix\IniFile\IniModifier(\jApp::varConfigPath('profiles.ini.php'));
                $dbini->setValue('jacl2_profile', $profile, 'jdb');
                $dbini->save();
            }
            $installer = new \jInstaller($reporter);
            $installer->setModuleParameters('jacl2db',array('defaultuser'=>true));
            $installer->installModules(array('jacl2db', 'jacl2db_admin'));
        }
        else {
            $installConfig->setValue('jacl2db_admin.installed', '0', 'modules');
            $installConfig->save();
            $inifile->setValue('jacl2db_admin.access', '0', 'modules');
            $inifile->save();
        }

        $installer->installModules(array('jpref_admin'));
    }
}
