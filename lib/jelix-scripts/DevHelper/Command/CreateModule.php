<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 * @contributor Bastien Jaillot
 *
 * @copyright   2005-2018 Laurent Jouanneau, 2007 Loic Mathaud, 2008 Bastien Jaillot
 *
 * @see        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateModule extends \Jelix\DevHelper\AbstractCommandForApp
{
    protected function configure()
    {
        $this
            ->setName('module:create')
            ->setDescription('Create a new module, with all necessary files and sub-directories')
            ->setHelp('')
            ->addArgument(
                'module',
                InputArgument::REQUIRED,
                'Name of the new module'
            )
            ->addArgument(
                'repository',
                InputArgument::OPTIONAL,
                'The path of the directory where to create the module. You can use shortcut like app:, lib: etc.',
                'app:modules/'
            )
            ->addOption(
                'no-subdir',
                null,
                InputOption::VALUE_NONE,
                'don\'t create sub-directories'
            )
            ->addOption(
                'no-controller',
                null,
                InputOption::VALUE_NONE,
                'don\'t create a default controller'
            )
            ->addOption(
                'add-install-zone',
                null,
                InputOption::VALUE_NONE,
                'Add the check_install zone for new application.'
            )
            ->addOption(
                'default-module',
                null,
                InputOption::VALUE_NONE,
                'the new module become the default module for the default entry point.'
            )
            ->addOption(
                'admin',
                null,
                InputOption::VALUE_NONE,
                'the new module will be used with master_admin. Install additionnal file and set additionnal configuration stuff'
            )
            ->addOption(
                'ver',
                null,
                InputOption::VALUE_REQUIRED,
                'indicates the initial version of the module',
                '0.1pre'
            )
            ->addOption(
                'no-registration',
                null,
                InputOption::VALUE_NONE,
                'Do not register the module in the application configuration'
            )
        ;

        $this->addEpOption();
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getArgument('module');
        $initialVersion = $input->getOption('ver');
        if (!$initialVersion) {
            $initialVersion = '0.1pre';
        }

        // note: since module name are used for name of generated name,
        // only this characters are allowed
        if ($module == null || preg_match('/([^a-zA-Z_0-9])/', $module)) {
            throw new \Exception("'".$module."' is not a valid name for a module");
        }

        // check if the module already exist or not
        $path = '';

        try {
            $path = $this->getModulePath($module);
        } catch (\Exception $e) {
        }

        if ($path != '') {
            throw new \Exception("module '".$module."' already exists");
        }

        // verify the given repository
        $repository = $input->getArgument('repository');
        if (substr($repository, -1) != '/') {
            $repository .= '/';
        }
        $repositoryPath = \jFile::parseJelixPath($repository);
        if (!$input->getOption('no-registration')) {
            $this->registerModulesDir($repository, $repositoryPath);
        }

        $path = $repositoryPath.$module.'/';
        $this->createDir($path);

        \jApp::setConfig(null);

        $noSubDir = $input->getOption('no-subdir');
        $addInstallZone = $input->getOption('add-install-zone');
        $isdefault = $input->getOption('default-module');

        if ($input->getOption('admin')) {
            $noSubDir = false;
            $addInstallZone = false;
        }

        $param = array();
        $param['module'] = $module;
        $param['default_id'] = $module.$this->config->infoIDSuffix;
        $param['version'] = $initialVersion;

        $this->createFile($path.'module.xml', 'module/module.xml.tpl', $param);

        // create all sub directories of a module
        if (!$noSubDir) {
            $this->createDir($path.'classes/');
            $this->createDir($path.'zones/');
            $this->createDir($path.'controllers/');
            $this->createDir($path.'templates/');
            $this->createDir($path.'classes/');
            $this->createDir($path.'daos/');
            $this->createDir($path.'forms/');
            $this->createDir($path.'locales/');
            $this->createDir($path.'locales/en_US/');
            $this->createDir($path.'locales/fr_FR/');
            $this->createDir($path.'install/');
            if ($this->verbose()) {
                $output->writeln("Sub directories have been created in the new module {$module}.");
            }
            $this->createFile($path.'install/install.php', 'module/install.tpl', $param);
            $this->createFile($path.'install/configure.php', 'module/configure.tpl', $param);
            $this->createFile($path.'urls.xml', 'module/urls.xml.tpl', array());
        }

        $iniDefault = new \Jelix\IniFile\MultiIniModifier(\jConfig::getDefaultConfigFile(), \jApp::mainConfigFile());
        $urlsFile = \jApp::appSystemPath($iniDefault->getValue('significantFile', 'urlengine'));
        $xmlMap = new \Jelix\Routing\UrlMapping\XmlMapModifier($urlsFile, true);

        // activate the module in the application
        if ($isdefault) {
            if ($this->allEntryPoint) {
                $xmlEp = $xmlMap->getDefaultEntryPoint('classic');
            } else {
                $xmlEp = $xmlMap->getEntryPoint($this->selectedEntryPointId);
            }
            if ($xmlEp) {
                $xmlEp->addUrlAction('/', $module, 'default:index', null, null, array('default' => true));
                $xmlEp->addUrlModule('', $module);
                if ($this->verbose()) {
                    $output->writeln("The new module {$module} becomes the default module");
                }
            } elseif ($this->verbose()) {
                $output->writeln('No default entry point found: the new module cannot be the default module');
            }
        }
        $xmlMap->save();

        // Configure the module. We don't launch the configurator,
        // as there is nothing to configure for the module.
        // just enabling it.
        \Jelix\Installer\Configurator::setModuleAsConfigured($module, $iniDefault);
        $iniDefault->save();

        // Install the module into the application instance
        // we don't have an installer, so just fill the installer.ini.php
        \Jelix\Installer\Installer::setModuleAsInstalled($module, $initialVersion, date('Y-m-d'));

        \jApp::declareModule($path);

        // create a default controller
        if (!$input->getOption('no-controller')) {
            $arguments = array(
                'module' => $module,
                'controller' => 'default',
                'method' => 'index',
            );

            if ($addInstallZone) {
                $arguments['--add-install-zone'] = true;
            }
            if ($output->isVerbose()) {
                $arguments['-v'] = true;
            }
            $this->executeSubCommand('module:create-ctrl', $arguments, $output);
        }

        if ($input->getOption('admin')) {
            $this->createFile($path.'classes/admin'.$module.'.listener.php', 'module/admin.listener.php.tpl', $param, 'Listener');
            $this->createFile($path.'events.xml', 'module/events.xml.tpl', $param);
            file_put_contents($path.'locales/en_US/interface.UTF-8.properties', 'menu.item='.$module);
            file_put_contents($path.'locales/fr_FR/interface.UTF-8.properties', 'menu.item='.$module);
        }
        return 0;
    }
}
