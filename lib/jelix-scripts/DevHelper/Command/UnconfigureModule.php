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

class UnconfigureModule extends \Jelix\DevHelper\AbstractCommandForApp {

    protected function configure()
    {
        $this
            ->setName('module:unconfigure')
            ->setDescription('Unconfigure and disable the module from the application.')
            ->setHelp('Launch the module configurator and disable the module')
            ->addArgument(
                'module',
                InputArgument::REQUIRED,
                'Name of the module to unconfigure'
            )
        ;
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        \jAppManager::close();

        $module = $input->getArgument('module');

        $reporter = new \Jelix\Installer\Reporter\Console($output,
            ($this->verbose()?'notice':'error'), 'Unconfiguration');
        $configurator = new \Jelix\Installer\Configurator($reporter);

        $configurator->unconfigureModule($module, $this->selectedEntryPointId);

        \jAppManager::open();
    }
}
