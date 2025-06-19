<?php

/**
 * @author      Laurent Jouanneau
 * @contributor
 *
 * @copyright   2008-2023 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Jelix\Core\App as App;
use Jelix\FileUtilities\Path;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateEntryPoint extends \Jelix\DevHelper\AbstractCommandForApp
{
    protected function configure()
    {
        $this
            ->setName('app:create-entrypoint')
            ->setDescription('Create a new entry point in the www directory of the application')
            ->setHelp('')
            ->addArgument(
                'entrypoint',
                InputArgument::REQUIRED,
                'Name of the new entrypoint. It can contain a directory path related to the config dir'
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
                'indicates the type of the entry point: classic, jsonrpc, xmlrpc, soap',
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
        if (!in_array($type, array('classic', 'jsonrpc', 'xmlrpc', 'soap'))) {
            throw new \Exception('invalid type');
        }

        // retrieve the name of the entry point
        $name = $input->getArgument('entrypoint');
        if (preg_match('/(.*)\.php$/', $name, $m)) {
            $name = $m[1];
        }

        // the full path of the entry point
        $entryPointFullPath = App::wwwPath($name.'.php');
        $entryPointTemplate = 'www/'.($type == 'classic' ? 'index' : $type).'.php.tpl';

        if (file_exists($entryPointFullPath)) {
            throw new \Exception('the entry point already exists');
        }

        $entryPointDir = dirname($entryPointFullPath).'/';

        // retrieve the config file name
        $configFile = $input->getArgument('config');

        if ($configFile == null) {
            $configFile = $name.'/config.ini.php';
        }

        // let's create the config file if needed
        $configFilePath = App::appSystemPath($configFile);
        if (!file_exists($configFilePath)) {
            $this->createDir(dirname($configFilePath));
            // the file doesn't exists
            // if there is a -copy-config parameter, we copy this file
            $originalConfig = $input->getOption('copy-config');
            if ($originalConfig) {
                if (!file_exists(App::appSystemPath($originalConfig))) {
                    throw new \Exception('unknown original configuration file');
                }
                file_put_contents(
                    $configFilePath,
                    file_get_contents(App::appSystemPath($originalConfig))
                );
                if ($this->verbose()) {
                    $output->writeln("Configuration file {$configFile} has been created from the config file {$originalConfig}.");
                }
            } else {
                // else we create a new config file
                $param = array();
                $this->createFile(
                    $configFilePath,
                    'app/system/index/config.ini.php.tpl',
                    $param,
                    'Configuration file'
                );
            }
        }

        $mainIniFile = new \Jelix\IniFile\MultiIniModifier(\Jelix\Core\Config\AppConfig::getDefaultConfigFile(), App::mainConfigFile());
        $inifile = new \Jelix\IniFile\MultiIniModifier($mainIniFile, $configFilePath);
        $urlsFile = App::appSystemPath($inifile->getValue('significantFile', 'urlengine'));
        $xmlMap = new \Jelix\Routing\UrlMapping\XmlMapModifier($urlsFile, true);

        $param = array();
        // creation of the entry point
        $this->createDir($entryPointDir);
        $param['rp_app'] = Path::shortestPath($entryPointDir, App::appPath()).'/';
        $param['config_file'] = $configFile;

        $this->createFile($entryPointFullPath, $entryPointTemplate, $param, 'Entry point');

        $xmlEp = $xmlMap->addEntryPoint($name, $type);
        /*if ($type == 'classic') {
            $xmlEp->addUrlAction('/', $module, $action);
        }*/
        $xmlMap->save();

        $fmk = $this->getFrameworkInfos();
        $fmk->addEntryPointInfo($name.'.php', $configFile, $type);
        $fmk->save();

        if ($this->verbose()) {
            $output->writeln('Project.xml has been updated');
        }
        return 0;
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        return 0;
    }
}
