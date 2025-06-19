<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnconfigureModule extends \Jelix\DevHelper\AbstractCommandForApp
{
    protected function configure()
    {
        $this
            ->setName('module:unconfigure')
            ->setDescription('Unconfigure and disable given module from the application.')
            ->setHelp('Launch configurators of modules and disable modules')
            ->addArgument(
                'modules',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Names of modules to unconfigure'
            )
        ;
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        \jAppManager::close();

        $modules = $input->getArgument('modules');

        $reporter = new \Jelix\Installer\Reporter\Console(
            $output,
            ($this->verbose() ? 'notice' : 'error'),
            'Unconfiguration'
        );

        $globalSetup = new \Jelix\Installer\GlobalSetup($this->getFrameworkInfos());
        $configurator = new \Jelix\Installer\Configurator(
            $reporter,
            $globalSetup,
            $this->getHelper('question'),
            $input,
            $output
        );

        $configurator->unconfigureModule($modules, $this->selectedEntryPointId);

        \jAppManager::open();
        return 0;
    }
}
