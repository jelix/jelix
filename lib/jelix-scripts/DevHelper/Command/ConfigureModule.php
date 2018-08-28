<?php
/**
* @author      Laurent Jouanneau
* @copyright   2018 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
namespace Jelix\DevHelper\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigureModule extends \Jelix\DevHelper\AbstractCommandForApp {

    protected function configure()
    {
        $this
            ->setName('module:configure')
            ->setDescription('Configure the module for the application.')
            ->setHelp('Setup the framework for the given module, and enable the module')
            ->addArgument(
                'module',
                InputArgument::REQUIRED,
                'Name of the module to configure'
            )
            ->addOption(
               'parameters',
               'p',
               InputOption::VALUE_REQUIRED,
               'parameters for the installer of the module: -p "param1;param2=value;..."'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'force to launch configuration when already configured'
            )
            ->addOption(
                'local',
                'l',
                InputOption::VALUE_NONE,
                'configure the module only into the local configuration'
            )
            ->addOption(
                'no-local',
                '',
                InputOption::VALUE_NONE,
                'configure the module into the app configuration, when it was previously configured for the local configuration'
            )
        ;
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        \jAppManager::close();

        $module = $input->getArgument('module');
        $parameters = $input->getOption('parameters');

        if ($parameters) {
            $parameters = \Jelix\Installer\ModuleStatus::unserializeParameters($parameters);
        }

        $reporter = new \Jelix\Installer\Reporter\Console($output,
            ($this->verbose()?'notice':'error'), 'Configuration');

        $globalSetup = new \Jelix\Installer\GlobalSetup($this->projectInfos);
        $configurator = new \Jelix\Installer\Configurator($reporter, $globalSetup);
        if ($parameters) {
            $configurator->setModuleParameters($module, $parameters);
        }

        if ($input->isInteractive()) {
            $configurator->setInteractiveMode($this->getHelper('question'), $input, $output);
        }

        $localConfig = $input->getOption('local')?true:($input->getOption('no-local')?true:null);


        $configurator->configureModules(array($module), $this->selectedEntryPointId,
            $localConfig, $input->getOption('force'));

        \jAppManager::open();
    }
}
