<?php

/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor
* @copyright   2008-2016 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

namespace Jelix\DevHelper\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jelix\FileUtilities\Path;
use Jelix\Core\App as App;

class CreateEntryPoint extends \Jelix\DevHelper\AbstractCommandForApp {

    protected function configure()
    {
        $this
            ->setName('app:createentrypoint')
            ->setDescription('Create a new entry point in the www directory of the application')
            ->setHelp('')
            ->addArgument(
                'entrypoint',
                InputArgument::REQUIRED,
                'Name of the new entrypoint. It can contain a sub-directory'
            )
            ->addArgument(
                'config',
                InputArgument::OPTIONAL,
                'The name of the configuration file to use. If it does not exists, it will be created with default content or with the content of the configuration file indicated with --copy-config'
            )
            ->addOption(
               'type',
               null,
               InputOption::VALUE_REQUIRED,
               'indicates the type of the entry point: classic, jsonrpc, xmlrpc, soap, cmdline',
               'classic'
            )
            ->addOption(
               'copy-config',
               null,
               InputOption::VALUE_REQUIRED,
               'The name of the configuration file to copy as new configuration file'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // retrieve the type of entry point we want to create
        $type = $input->getOption('type');
        if(!in_array($type, array('classic','jsonrpc','xmlrpc','soap','cmdline'))) {
            throw new \Exception("invalid type");
        }

        // retrieve the name of the entry point
        $name = $input->getArgument('entrypoint');
        if (preg_match('/(.*)\.php$/', $name, $m)) {
            $name = $m[1];
        }

        // the full path of the entry point
        if ($type == 'cmdline') {
            $entryPointFullPath = App::scriptsPath($name.'.php');
            $entryPointTemplate = 'scripts/cmdline.php.tpl';
        }
        else {
            $entryPointFullPath = App::wwwPath($name.'.php');
            $entryPointTemplate = 'www/'.($type=='classic'?'index':$type).'.php.tpl';
        }

        if (file_exists($entryPointFullPath)) {
            throw new \Exception("the entry point already exists");
        }

        $entryPointDir = dirname($entryPointFullPath).'/';

        $this->loadAppInfos();

        // retrieve the config file name
        $configFile = $input->getArgument('config');

        if ($configFile == null) {
            if ($type == 'cmdline') {
                $configFile = 'cmdline/'.$name.'.ini.php';
            }
            else {
                $configFile = $name.'/config.ini.php';
            }
        }

        // let's create the config file if needed
        $configFilePath = App::appConfigPath($configFile);
        if (!file_exists($configFilePath)) {
            $this->createDir(dirname($configFilePath));
            // the file doesn't exists
            // if there is a -copy-config parameter, we copy this file
            $originalConfig = $input->getOption('copy-config');
            if ($originalConfig) {
                if (! file_exists(App::appConfigPath($originalConfig))) {
                    throw new \Exception ("unknown original configuration file");
                }
                file_put_contents($configFilePath,
                                  file_get_contents(App::appConfigPath($originalConfig)));
                if ($this->verbose()) {
                    $output->writeln("Configuration file $configFile has been created from the config file $originalConfig.");
                }
            }
            else {
                // else we create a new config file
                $param = array();
                $this->createFile($configFilePath,
                                  'app/config/index/config.ini.php.tpl',
                                  $param, "Configuration file");
            }
        }

        $inifile = new \Jelix\IniFile\MultiIniModifier(App::mainConfigFile(), $configFilePath);
        $urlsFile = App::appConfigPath($inifile->getValue('significantFile', 'urlengine'));
        $xmlMap = new \Jelix\Routing\UrlMapping\XmlMapModifier($urlsFile, true);

        $param = array();
        // creation of the entry point
        $this->createDir($entryPointDir);
        $param['rp_app']   = Path::shortestPath($entryPointDir, App::appPath());
        $param['config_file'] = $configFile;

        $this->createFile($entryPointFullPath, $entryPointTemplate, $param, "Entry point");

        if ($type != 'cmdline') {
            $xmlEp = $xmlMap->addEntryPoint($name, $type);
            /*if ($type == 'classic') {
                $xmlEp->addUrlAction('/', $module, $action);
            }*/
            $xmlMap->save();
        }

        $this->appInfos->addEntryPointInfo($name.".php", $configFile , $type);
        if ($this->verbose()) {
            $output->writeln($this->appInfos->getFile()." has been updated.");
        }

        $installer = new \Jelix\Installer\Installer(new \Jelix\Installer\Reporter\Console('warning'));
        $installer->installEntryPoint($name.".php");
        if ($this->verbose()) {
            $output->writeln("All modules have been initialized for the new entry point.");
        }
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
    }
}
