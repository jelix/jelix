<?php
/**
 * @package     jelix-scripts
 *
 * @author Laurent Jouanneau
 * @copyright   2018-2022 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\Scripts;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for a user to configure an application before installing it.
 */
class ConfigureCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('configure')
            ->setDescription('Launch configuration of the application')
            ->addArgument(
                'module',
                InputArgument::OPTIONAL,
                'name of a module to configure specifically'
            )
            ->addOption(
                'parameters',
                'p',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'parameters for the installer of the module:  -p param1 -p param2=value etc'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'force to launch configuration of a module when already configured'
            )
            ->addOption(
                'entry-points',
                'e',
                InputOption::VALUE_REQUIRED,
                'indicate the list of entry points (names separated by a coma) for which the module will be configured'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUpOutput($output);
        \jAppManager::close();

        $reporter = new \Jelix\Installer\Reporter\Console(
            $output,
            ($output->isVerbose() ? 'notice' : 'error'),
            'Configuration migration'
        );

        // launch the low-level migration
        $migrator = new \Jelix\Installer\Migration($reporter);
        $migrator->migrateLocal();

        $reporter = new \Jelix\Installer\Reporter\Console(
            $output,
            ($output->isVerbose() ? 'notice' : 'error'),
            'Configuration'
        );

        $globalSetup = new \Jelix\Installer\GlobalSetup();
        $configurator = new \Jelix\Installer\Configurator(
            $reporter,
            $globalSetup,
            $this->getHelper('question'),
            $input,
            $output
        );

        $module = $input->getArgument('module');
        if ($module) {
            $parameters = $input->getOption('parameters');
            $parsedParameters = array();
            if ($parameters) {
                foreach($parameters as $param) {
                    $result = \Jelix\Installer\ModuleStatus::unserializeParameters($param);
                    if ($result) {
                        $parsedParameters = array_merge($parsedParameters, $result);
                    }
                }
            }
            $selectedEntryPointId = $globalSetup->getMainEntryPoint()->getEpId();
            $selectedEntryPointsIdList = $this->getSelectedEntryPoint($input->getOption('entry-points'), true);
            if (count($selectedEntryPointsIdList)) {
                $selectedEntryPointId = $selectedEntryPointsIdList[0];
            }
            $configurator->setModuleParameters($module, $parsedParameters);
            $configurator->configureModules(
                array($module),
                $selectedEntryPointId,
                true,
                $input->getOption('force')
            );
        } else {
            $configurator->localConfigureEnabledModules();
        }

        \jAppManager::open();

        return 0;
    }

    protected function getSelectedEntryPoint($ep, $allowList = false)
    {
        // check entry point

        if ($ep) {

            if ($allowList) {
                $list = preg_split('/\s*,\s*/', $ep);

                return array_map(array($this, 'normalizeEp'), $list);
            }

            return $this->normalizeEp($ep);
        }
        if ($allowList) {
            return array();
        }

        return '';
    }

    private function normalizeEp($ep)
    {
        if (($p = strpos($ep, '.php')) === false) {
            return $ep;
        }

        return substr($ep, 0, $p);
    }

    protected function setUpOutput(OutputInterface $output)
    {
        $outputStyle = new OutputFormatterStyle('cyan', 'default');
        $output->getFormatter()->setStyle('question', $outputStyle);

        $outputStyle2 = new OutputFormatterStyle('yellow', 'default', array('bold'));
        $output->getFormatter()->setStyle('inputstart', $outputStyle2);
    }
}
