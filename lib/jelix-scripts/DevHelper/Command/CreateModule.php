<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @contributor Bastien Jaillot
* @copyright   2005-2016 Laurent Jouanneau, 2007 Loic Mathaud, 2008 Bastien Jaillot
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
namespace Jelix\DevHelper\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Jelix\Core\App as App;

class CreateModule extends \Jelix\DevHelper\AbstractCommandForApp {

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
               'nosubdir',
               null,
               InputOption::VALUE_NONE,
               'don\'t create sub-directories'
            )
            ->addOption(
               'nocontroller',
               null,
               InputOption::VALUE_NONE,
               'don\'t create a default controller'
            )
            ->addOption(
               'cmdline',
               null,
               InputOption::VALUE_NONE,
               'To create a controller for a command line script'
            )
            ->addOption(
               'addinstallzone',
               null,
               InputOption::VALUE_NONE,
               'Add the check_install zone for new application.'
            )
            ->addOption(
               'defaultmodule',
               null,
               InputOption::VALUE_NONE,
               'the new module become the default module.'
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
               'noregistration',
               null,
               InputOption::VALUE_NONE,
               'Do not register the module in the application configuration'
            )
        ;
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
        }
        catch (\Exception $e) {
        }

        if ($path != '') {
            throw new \Exception("module '".$module."' already exists");
        }

        // verify the given repository
        $repository = $input->getArgument('repository');
        if (substr($repository,-1) != '/') {
            $repository .= '/';
        }
        $repositoryPath = \jFile::parseJelixPath( $repository );
        if (!$input->getOption('noregistration')) {
            $this->registerModulesDir($repository, $repositoryPath);
        }

        $path = $repositoryPath.$module.'/';
        $this->createDir($path);

        App::setConfig(null);

        $noSubDir = $input->getOption('nosubdir');
        $addInstallZone = $input->getOption('addinstallzone');
        $isdefault = $input->getOption('defaultmodule');

        if ($input->getOption('admin')) {
            $noSubDir = false;
            $addInstallZone = false;
        }

        $param = array();
        $param['module'] = $module;
        $param['version'] = $initialVersion;

        $this->createFile($path.'jelix-module.json', 'module/jelix-module.json.tpl', $param);

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
                $output->writeln("Sub directories have been created in the new module $module.");
            }
            $this->createFile($path.'install/install.php','module/install.tpl',$param);
            $this->createFile($path.'urls.xml', 'module/urls.xml.tpl', array());
        }


        $iniDefault = new \Jelix\IniFile\IniModifier(App::mainConfigFile());
        // activate the module in the application
        if ($isdefault) {
            $iniDefault->setValue('startModule', $module);
            $iniDefault->setValue('startAction', 'default:index');
            if ($this->verbose()) {
                $output->writeln("The new module $module becomes the default module");
            }
        }

        $iniDefault->setValue($module.'.access', ($this->allEntryPoint?2:1) , 'modules');
        $iniDefault->save();

        $list = $this->getEntryPointsList();
        $install = new \Jelix\IniFile\IniModifier(App::configPath('installer.ini.php'));

        // install the module for all needed entry points
        foreach ($list as $entryPoint) {

            $configFile = App::configPath($entryPoint['config']);
            $epconfig = new \Jelix\IniFile\IniModifier($configFile);

            if ($this->allEntryPoint) {
                $access = 2;
            }
            else {
                $access = ($entryPoint['file'] == $this->entryPointName?2:0);
            }

            $epconfig->setValue($module.'.access', $access, 'modules');
            $epconfig->save();

            if ($this->allEntryPoint || $entryPoint['file'] == $this->entryPointName) {
                $install->setValue($module.'.installed', 1, $entryPoint['id']);
                $install->setValue($module.'.version', $initialVersion, $entryPoint['id']);
            }

            if ($isdefault) {
                // we set the module as default module for one or all entry points.
                // we set the startModule option for all entry points except
                // if an entry point is indicated on the command line
                if ($this->allEntryPoint || $entryPoint['file'] == $this->entryPointName) {
                    if ($epconfig->getValue('startModule') != '') {
                        $epconfig->setValue('startModule', $module);
                        $epconfig->setValue('startAction', 'default:index');
                        $epconfig->save();
                    }
                }
            }
            if ($this->verbose()) {
                $output->writeln("The module is initialized for the entry point ".$entryPoint['file']);
            }
        }

        $install->save();
        App::declareModule($path);

        // create a default controller
        if(!$input->getOption('nocontroller')){
            $arguments = array(
                'module'=>$module,
                'controller'=>'default',
                'method'=>'index',
            );

            if ($input->getOption('entry-point')) {
                $arguments['--entry-point'] = $input->getOption('entry-point');
            }
            if ($input->getOption('cmdline')) {
                $arguments['--cmdline'] = true;
            }
            if ($addInstallZone) {
                $arguments['--addinstallzone'] =true;
            }
            if ($output->isVerbose()) {
                $arguments['-v'] = true;
            }
            $this->executeSubCommand('module:create-ctrl', $arguments, $output);
        }

        if ($input->getOption('admin')) {
            $this->createFile($path.'classes/admin'.$module.'.listener.php', 'module/admin.listener.php.tpl', $param, "Listener");
            $this->createFile($path.'events.xml', 'module/events.xml.tpl', $param);
            file_put_contents($path.'locales/en_US/interface.UTF-8.properties', 'menu.item='.$module);
            file_put_contents($path.'locales/fr_FR/interface.UTF-8.properties', 'menu.item='.$module);
        }
    }
}
