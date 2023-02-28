<?php
/**
 * @package     jelix-scripts
 *
 * @author      Laurent Jouanneau
 * @contributor Julien Issler
 *
 * @copyright   2008-2023 Laurent Jouanneau
 * @copyright   2015 Julien Issler
 *
 * @see        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Jelix\DevHelper\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitAdmin extends \Jelix\DevHelper\AbstractCommandForApp
{
    protected function configure()
    {
        $this
            ->setName('app:init-admin')
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
                'no-jauthdb',
                null,
                InputOption::VALUE_NONE,
                'Do not use and do not configure the driver \'db\' of jAuth'
            )
            ->addOption(
                'no-jauth',
                null,
                InputOption::VALUE_NONE,
                'Do not configure the jauth module'
            )
            ->addOption(
                'no-jauthdb-admin',
                null,
                InputOption::VALUE_NONE,
                'Do not configure the jauthdb_admin module'
            )
            ->addOption(
                'install-jpref-admin',
                null,
                InputOption::VALUE_NONE,
                'Install the jpref_admin module'
            )
            ->addOption(
                'no-acl2db',
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
            $entrypoint = substr($entrypoint, 0, $p);
        }
        $this->selectedEntryPointId = $entrypoint;

        return $this->_execute($input, $output);
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $doNotInstallJauth = $input->getOption('no-jauth');
        $doNotInstallJauthdb = $input->getOption('no-jauthdb');
        $doNotInstallJacl2db = $input->getOption('no-acl2db');
        $doNotInstallJauthdbAdmin = $input->getOption('no-jauthdb-admin');
        $doInstallJprefAdmin = $input->getOption('install-jpref-admin');

        if ($doInstallJprefAdmin && $doNotInstallJacl2db) {
            throw new \Exception('module jpref-admin needs jAcl2db');
        }

        $entrypoint = $this->selectedEntryPointId;

        try {
            $ep = $this->getEntryPointInfo($entrypoint);
        } catch (\Exception $e) {
            try {
                $options = array(
                    'entrypoint' => $entrypoint,
                );
                $this->executeSubCommand('app:create-entrypoint', $options, $output);
                $ep = $this->getEntryPointInfo($entrypoint);
            } catch (\Exception $e) {
                throw new \Exception('The entrypoint has not been created because of this error: '.$e->getMessage().". No other files have been created.\n");
            }
        }

        \jApp::setConfig(\jConfigCompiler::read(
            $ep->getConfigFile(),
            true,
            true,
            $ep->getFile()
        ));
        \jFile::createDir(\jApp::tempPath(), \jApp::config()->chmodDir);

        $installConfig = new \Jelix\IniFile\IniModifier(\jApp::varConfigPath('installer.ini.php'));

        $mainIniFile = new \Jelix\IniFile\MultiIniModifier(
            \jConfig::getDefaultConfigFile(),
            \jApp::mainConfigFile()
        );
        $inifile = new \Jelix\IniFile\MultiIniModifier(
            $mainIniFile,
            \jApp::appSystemPath($ep->getConfigFile())
        );

        $params = array();
        $this->createFile(
            \jApp::appPath('app/responses/adminHtmlResponse.class.php'),
            'app/responses/adminHtmlResponse.class.php.tpl',
            $params,
            'Response for admin interface'
        );
        $this->createFile(
            \jApp::appPath('app/responses/adminLoginHtmlResponse.class.php'),
            'app/responses/adminLoginHtmlResponse.class.php.tpl',
            $params,
            'Response for login page'
        );
        $inifile->setValue('html', 'adminHtmlResponse', 'responses');
        $inifile->setValue('htmlauth', 'adminLoginHtmlResponse', 'responses');

        $repositoryPath = \jFile::parseJelixPath('lib:jelix-admin-modules');
        $this->registerModulesDir('lib:jelix-admin-modules', $repositoryPath);

        $inifile->save();

        $urlsFile = \jApp::appSystemPath($inifile->getValue('significantFile', 'urlengine'));
        $xmlMap = new \Jelix\Routing\UrlMapping\XmlMapModifier($urlsFile, true);
        $xmlEp = $xmlMap->getEntryPoint($entrypoint);
        $xmlEp->addUrlAction('/', 'master_admin', 'default:index', null, null, array('default' => true));
        $xmlEp->addUrlModule('', 'master_admin');

        $globalSetup = new \Jelix\Installer\GlobalSetup($this->getFrameworkInfos());
        $reporter = new \Jelix\Installer\Reporter\Console($output, ($output->isVerbose() ? 'notice' : 'warning'), 'Configuration');
        $configurator = new \Jelix\Installer\Configurator($reporter, $globalSetup, $this->getHelper('question'), $input, $output);

        $jcommunity = $globalSetup->getModuleComponent('jcommunity');
        if ($jcommunity && $jcommunity->isEnabled()) {
            $doNotInstallJauth = true;
            $doNotInstallJauthdb = true;
        }

        $modulesToConfigure = array();

        $profile = $input->getOption('profile');

        if (!$doNotInstallJauth) {
            $configurator->setModuleParameters('jauth', array('eps' => array($entrypoint)));

            $modulesToConfigure[] = 'jauth';

            $xmlEp->addUrlInclude('/auth', 'jauth', 'urls.xml');
        }

        if (!$doNotInstallJauthdb) {
            $configurator->setModuleParameters('jauthdb', array('defaultuser' => true));
            $modulesToConfigure[] = 'jauthdb';
        }

        if (!$doNotInstallJauthdbAdmin) {
            $modulesToConfigure[] = 'jauthdb_admin';
            $xmlEp->addUrlInclude('/admin/auth', 'jauthdb_admin', 'urls.xml');
        }

        $modulesToConfigure[] = 'master_admin';
        //$configurator->setModuleParameters('master_admin', array());

        if ($doInstallJprefAdmin) {
            $xmlEp->addUrlInclude('/admin/pref', 'jpref_admin', 'urls.xml');
            $modulesToConfigure[] = 'jpref_admin';
        }

        if (!$doNotInstallJacl2db) {
            if ($profile != '') {
                $dbini = new \Jelix\IniFile\IniModifier(\jApp::varConfigPath('profiles.ini.php'));
                $dbini->setValue('jacl2_profile', $profile, 'jdb');
                $dbini->save();
            }

            $xmlEp->addUrlInclude('/admin/acl', 'jacl2db_admin', 'urls.xml');
            $configurator->setModuleParameters('jacl2db', array('defaultuser' => true, 'defaultgroups' => true));
            $modulesToConfigure[] = 'jacl2db';
            $modulesToConfigure[] = 'jacl2db_admin';
        }

        $configurator->configureModules($modulesToConfigure, $entrypoint, false);

        $xmlMap->save();

        if (!$doNotInstallJauth) {
            $authini = new \Jelix\IniFile\IniModifier(\jApp::appSystemPath($entrypoint . '/auth.coord.ini.php'));
            $authini->setValue('after_login', 'master_admin~default:index');
            $authini->setValue('timeout', '30');
            if (!$doNotInstallJauthdb && $profile != '')  {
                $authini->setValue('profile', $profile, 'Db');
            }
            $authini->save();
        }

        // installation
        $globalSetup = new \Jelix\Installer\GlobalSetup($this->getFrameworkInfos());
        $reporter = new \Jelix\Installer\Reporter\Console($output, ($output->isVerbose() ? 'notice' : 'warning'));
        $installer = new \Jelix\Installer\Installer($reporter, $globalSetup);
        $installer->installApplication();
        return 0;
    }
}
